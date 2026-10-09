<?php
declare(strict_types=1);

$configFile = __DIR__ . '/mail-config.php';
$config = is_file($configFile) ? require $configFile : [];

function redirect_with_status(string $status): never
{
    header('Location: contact.html?form=' . rawurlencode($status) . '#contact-form-status', true, 303);
    exit;
}

function smtp_read_response($connection): array
{
    $response = '';
    while (($line = fgets($connection, 515)) !== false) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] !== '-') {
            break;
        }
    }

    if ($response === '' || !preg_match('/^(\d{3})/', $response, $matches)) {
        throw new RuntimeException('Invalid SMTP response');
    }

    return [(int) $matches[1], trim($response)];
}

function smtp_command($connection, string $command, array $expectedCodes): string
{
    if (fwrite($connection, $command . "\r\n") === false) {
        throw new RuntimeException('Could not write SMTP command');
    }

    [$code, $response] = smtp_read_response($connection);
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException('SMTP server rejected a command with code ' . $code);
    }

    return $response;
}

function send_smtp_message(array $config, string $recipient, string $replyTo, string $subject, string $body): void
{
    $host = (string) ($config['smtp_host'] ?? '');
    $port = (int) ($config['smtp_port'] ?? 0);
    $security = (string) ($config['smtp_security'] ?? 'ssl');
    $username = (string) ($config['smtp_username'] ?? '');
    $password = preg_replace('/\s+/', '', (string) ($config['smtp_password'] ?? '')) ?? '';
    $fromEmail = (string) ($config['from_email'] ?? $username);
    $fromName = (string) ($config['from_name'] ?? 'Margreth School Website');

    if ($host === '' || $port < 1 || $username === '' || $password === '' || $fromEmail === '') {
        throw new RuntimeException('SMTP settings are incomplete');
    }
    if (!in_array($security, ['ssl', 'tls'], true)) {
        throw new RuntimeException('Unsupported SMTP security setting');
    }

    $scheme = $security === 'ssl' ? 'ssl://' : 'tcp://';
    $connection = @stream_socket_client(
        $scheme . $host . ':' . $port,
        $errorNumber,
        $errorMessage,
        15,
        STREAM_CLIENT_CONNECT
    );
    if ($connection === false) {
        throw new RuntimeException('Could not connect to configured SMTP server');
    }

    try {
        stream_set_timeout($connection, 15);
        [$greetingCode] = smtp_read_response($connection);
        if ($greetingCode !== 220) {
            throw new RuntimeException('SMTP server did not greet the connection');
        }

        $localName = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $localName = preg_replace('/[^A-Za-z0-9.-]/', '', $localName) ?: 'localhost';
        smtp_command($connection, 'EHLO ' . $localName, [250]);

        if ($security === 'tls') {
            smtp_command($connection, 'STARTTLS', [220]);
            if (stream_socket_enable_crypto($connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
                throw new RuntimeException('Could not establish SMTP TLS encryption');
            }
            smtp_command($connection, 'EHLO ' . $localName, [250]);
        }

        smtp_command($connection, 'AUTH LOGIN', [334]);
        smtp_command($connection, base64_encode($username), [334]);
        smtp_command($connection, base64_encode($password), [235]);
        smtp_command($connection, 'MAIL FROM:<' . $fromEmail . '>', [250]);
        smtp_command($connection, 'RCPT TO:<' . $recipient . '>', [250, 251]);
        smtp_command($connection, 'DATA', [354]);

        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers = [
            'From: ' . $encodedFromName . ' <' . $fromEmail . '>',
            'To: <' . $recipient . '>',
            'Reply-To: <' . $replyTo . '>',
            'Subject: ' . $encodedSubject,
            'Date: ' . date(DATE_RFC2822),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];
        $messageData = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($body));
        if (fwrite($connection, $messageData . "\r\n.\r\n") === false) {
            throw new RuntimeException('Could not send message data');
        }
        [$messageCode] = smtp_read_response($connection);
        if ($messageCode !== 250) {
            throw new RuntimeException('SMTP server did not accept the email');
        }
        smtp_command($connection, 'QUIT', [221]);
    } finally {
        fclose($connection);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed');
}

// Silently accept honeypot submissions to discourage automated spam.
if (!empty($_POST['website'])) {
    redirect_with_status('sent');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$subjectChoice = (string) ($_POST['subject'] ?? '');
$message = trim((string) ($_POST['message'] ?? ''));

$allowedSubjects = [
    'general' => 'General Inquiry',
    'admissions' => 'Admissions',
    'fees' => 'Fees & Payments',
    'academics' => 'Academics',
    'other' => 'Other',
];

if (
    $name === '' || strlen($name) > 300 ||
    filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 254 ||
    strlen($phone) > 90 ||
    !isset($allowedSubjects[$subjectChoice]) ||
    $message === '' || strlen($message) > 15000
) {
    redirect_with_status('invalid');
}

$name = trim(strip_tags($name));
$phone = trim(strip_tags($phone));
$message = trim(strip_tags(str_replace(["\r\n", "\r", "\0"], ["\n", "\n", ''], $message)));

$recipient = 'barakamkumbwa106@gmail.com';
$mailSubject = 'Website contact form: ' . $allowedSubjects[$subjectChoice];
$mailBody = "A new message was sent through the Margreth School website.\n\n"
    . "Name: {$name}\n"
    . "Email: {$email}\n"
    . "Phone: " . ($phone !== '' ? $phone : 'Not provided') . "\n"
    . "Subject: {$allowedSubjects[$subjectChoice]}\n\n"
    . "Message:\n{$message}\n";

try {
    send_smtp_message($config, $recipient, $email, $mailSubject, $mailBody);
    redirect_with_status('sent');
} catch (Throwable $error) {
    error_log('Contact form email failed: ' . $error->getMessage());
    redirect_with_status('error');
}
