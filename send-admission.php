<?php
declare(strict_types=1);

$configFile = __DIR__ . '/mail-config.php';
$config = is_file($configFile) ? require $configFile : [];

function redirect_admission(string $status): never
{
    header('Location: admissions/apply.html?application=' . rawurlencode($status) . '#online-admission-form', true, 303);
    exit;
}

function admission_smtp_read_response($connection): array
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

function admission_smtp_command($connection, string $command, array $expectedCodes): void
{
    if (fwrite($connection, $command . "\r\n") === false) {
        throw new RuntimeException('Could not write SMTP command');
    }
    [$code] = admission_smtp_read_response($connection);
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException('SMTP server rejected a command with code ' . $code);
    }
}

function send_admission_email(array $config, string $recipient, string $replyTo, string $body, array $attachments): void
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
    $connection = @stream_socket_client($scheme . $host . ':' . $port, $errorNumber, $errorMessage, 15, STREAM_CLIENT_CONNECT);
    if ($connection === false) {
        throw new RuntimeException('Could not connect to configured SMTP server');
    }

    try {
        stream_set_timeout($connection, 15);
        [$greetingCode] = admission_smtp_read_response($connection);
        if ($greetingCode !== 220) {
            throw new RuntimeException('SMTP server did not greet the connection');
        }

        $localName = preg_replace('/[^A-Za-z0-9.-]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost')) ?: 'localhost';
        admission_smtp_command($connection, 'EHLO ' . $localName, [250]);
        if ($security === 'tls') {
            admission_smtp_command($connection, 'STARTTLS', [220]);
            if (stream_socket_enable_crypto($connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
                throw new RuntimeException('Could not establish SMTP TLS encryption');
            }
            admission_smtp_command($connection, 'EHLO ' . $localName, [250]);
        }

        admission_smtp_command($connection, 'AUTH LOGIN', [334]);
        admission_smtp_command($connection, base64_encode($username), [334]);
        admission_smtp_command($connection, base64_encode($password), [235]);
        admission_smtp_command($connection, 'MAIL FROM:<' . $fromEmail . '>', [250]);
        admission_smtp_command($connection, 'RCPT TO:<' . $recipient . '>', [250, 251]);
        admission_smtp_command($connection, 'DATA', [354]);

        $boundary = '=_MargrethAdmission_' . bin2hex(random_bytes(16));
        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
        $encodedSubject = '=?UTF-8?B?' . base64_encode('New school admission application') . '?=';
        $headers = [
            'From: ' . $encodedFromName . ' <' . $fromEmail . '>',
            'To: <' . $recipient . '>',
            'Reply-To: <' . $replyTo . '>',
            'Subject: ' . $encodedSubject,
            'Date: ' . date(DATE_RFC2822),
            'MIME-Version: 1.0',
            'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
        ];
        $message = '--' . $boundary . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($body));

        foreach ($attachments as $attachment) {
            $message .= '--' . $boundary . "\r\n"
                . 'Content-Type: ' . $attachment['mime'] . '; name="' . $attachment['name'] . "\"\r\n"
                . "Content-Transfer-Encoding: base64\r\n"
                . 'Content-Disposition: attachment; filename="' . $attachment['name'] . "\"\r\n\r\n"
                . chunk_split(base64_encode($attachment['content']));
        }
        $message .= '--' . $boundary . "--\r\n";
        $messageData = implode("\r\n", $headers) . "\r\n\r\n" . $message;
        $messageData = preg_replace('/(?m)^\./', '..', $messageData) ?? $messageData;
        if (fwrite($connection, $messageData . "\r\n.\r\n") === false) {
            throw new RuntimeException('Could not send application email');
        }
        [$messageCode] = admission_smtp_read_response($connection);
        if ($messageCode !== 250) {
            throw new RuntimeException('SMTP server did not accept the application email');
        }
        admission_smtp_command($connection, 'QUIT', [221]);
    } finally {
        fclose($connection);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed');
}

if (!empty($_POST['website'])) {
    redirect_admission('sent');
}

$requiredFields = [
    'child_name' => "Child's full name",
    'gender' => 'Gender',
    'date_of_birth' => 'Date of birth',
    'citizenship' => 'Citizenship',
    'residential_address' => 'Residential address',
    'phone' => 'Phone number',
    'grade' => 'Grade for admission',
    'medical_conditions' => 'Medical conditions',
    'father_name' => "Father's name",
    'mother_name' => "Mother's name",
    'father_occupation' => "Father's occupation",
    'mother_occupation' => "Mother's occupation",
];
$fields = [];
foreach ($requiredFields as $key => $label) {
    $value = trim((string) ($_POST[$key] ?? ''));
    if ($value === '' || strlen($value) > 2000) {
        redirect_admission('invalid');
    }
    $fields[$key] = trim(strip_tags(str_replace(["\r\n", "\r", "\0"], ["\n", "\n", ''], $value)));
}

if (!in_array($fields['gender'], ['Male', 'Female'], true)) {
    redirect_admission('invalid');
}
$allowedGrades = ['Daycare', 'Pre-Unit', 'Standard One', 'Standard Two', 'Standard Three', 'Standard Four', 'Standard Five', 'Standard Six', 'Standard Seven'];
if (!in_array($fields['grade'], $allowedGrades, true)) {
    redirect_admission('invalid');
}
$date = DateTime::createFromFormat('Y-m-d', $fields['date_of_birth']);
if (!$date || $date->format('Y-m-d') !== $fields['date_of_birth'] || $date > new DateTime('today')) {
    redirect_admission('invalid');
}

$optionalFields = [
    'email' => 'Email address',
    'previous_school' => 'Previous school',
];
foreach ($optionalFields as $key => $label) {
    $fields[$key] = trim(strip_tags((string) ($_POST[$key] ?? '')));
}
if ($fields['email'] !== '' && filter_var($fields['email'], FILTER_VALIDATE_EMAIL) === false) {
    redirect_admission('invalid');
}

$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
$requiredUploads = ['passport_photo_1', 'passport_photo_2', 'birth_certificate'];
$uploadFields = array_merge($requiredUploads, ['other_document']);
$attachments = [];
$fileInfo = new finfo(FILEINFO_MIME_TYPE);
foreach ($uploadFields as $key) {
    $upload = $_FILES[$key] ?? null;
    if (!is_array($upload)) {
        if (in_array($key, $requiredUploads, true)) {
            redirect_admission('invalid');
        }
        continue;
    }
    $error = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE && !in_array($key, $requiredUploads, true)) {
        continue;
    }
    if ($error !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($upload['tmp_name'] ?? '')) || (int) ($upload['size'] ?? 0) > 5 * 1024 * 1024) {
        redirect_admission('invalid');
    }
    $mime = $fileInfo->file((string) $upload['tmp_name']);
    if (!in_array($mime, $allowedMimeTypes, true)) {
        redirect_admission('invalid');
    }
    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string) ($upload['name'] ?? 'document'))) ?: 'document';
    $attachments[] = [
        'name' => substr($safeName, 0, 120),
        'mime' => $mime,
        'content' => file_get_contents((string) $upload['tmp_name']),
    ];
}

$labels = [
    'child_name' => "Child's Full Name", 'gender' => 'Gender', 'date_of_birth' => 'Date of Birth',
    'citizenship' => 'Citizenship', 'residential_address' => 'Residential Address', 'phone' => 'Phone Number',
    'email' => 'Email Address', 'grade' => 'Grade for Admission', 'previous_school' => 'Previous School',
    'medical_conditions' => 'Problems / Medical Conditions', 'father_name' => "Father's Name",
    'mother_name' => "Mother's Name",
    'father_occupation' => "Father's Occupation", 'mother_occupation' => "Mother's Occupation",
];
$mailBody = "A new admission application was submitted through the Margreth School website.\n\n";
foreach ($labels as $key => $label) {
    $mailBody .= $label . ': ' . ($fields[$key] !== '' ? $fields[$key] : 'Not provided') . "\n";
}
$mailBody .= "\nAttached supporting documents: " . count($attachments) . " file(s).\n";
$recipient = (string) ($config['admissions_email'] ?? 'barakamkumbwa106@gmail.com');
$replyTo = filter_var($fields['email'], FILTER_VALIDATE_EMAIL) ? $fields['email'] : $recipient;

try {
    send_admission_email($config, $recipient, $replyTo, $mailBody, $attachments);
    redirect_admission('sent');
} catch (Throwable $error) {
    error_log('Admission application email failed: ' . $error->getMessage());
    redirect_admission('error');
}
