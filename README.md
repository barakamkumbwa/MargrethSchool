# 🎓 Margreth Pre & Primary School Website

A modern, responsive website for **Margreth Pre & Primary School**, built with HTML, CSS, vanilla JavaScript, and PHP for form submissions.

Tovuti ya kisasa na inayojipanga kwenye vifaa mbalimbali kwa **Margreth Pre & Primary School**. Imetengenezwa kwa HTML, CSS, JavaScript bila framework, na PHP kwa ajili ya kutuma fomu.

---

## 📖 Table of Contents / Yaliyomo

- [✨ Features / Vipengele](#-features--vipengele)
- [🛠️ Tech Stack / Teknolojia](#️-tech-stack--teknolojia-iliyotumika)
- [📸 Pages / Kurasa](#-pages--kurasa)
- [📁 Project Structure / Muundo wa Project](#-project-structure--muundo-wa-project)
- [🚀 Getting Started / Jinsi ya Kuanza](#-getting-started--jinsi-ya-kuanza)
- [📧 Email Configuration / Mipangilio ya Barua Pepe](#-email-configuration--mipangilio-ya-barua-pepe)
- [🎨 Design / Muonekano](#-design--muonekano)
- [🖼️ Images / Picha](#️-images--picha)
- [🤝 Contributing / Kuchangia](#-contributing--kuchangia)
- [📄 License / Leseni](#-license--leseni)
- [👨‍💻 Author / Mwandishi](#-author--mwandishi)

---

## ✨ Features / Vipengele

- Responsive layout for phones, tablets, and desktop screens.
- Main navigation with About Us and Academics dropdown menus.
- Home page sections for the school, programs, and core values.
- School staff page with an organizational tree and staff photo cards.
- Admissions information, joining instructions, printable application form, and a separate online application page.
- Online admission form with child, guardian, and document-upload fields.
- Results page with Standard Four (SFNA) and Standard Seven (PSLE) cards and year links.
- Image gallery with categories, masonry-style layout, and rotating banner slides.
- News and events page.
- Contact form and embedded Google Map.
- PHP form handlers that send contact messages and admission applications using SMTP.
- Hover effects, transitions, and Font Awesome icons.

> Note: the four year links under each results card currently point to the same NECTA results URL. Update them in `results/index.html` when separate year or school-specific URLs are available.

---

## 🛠️ Tech Stack / Teknolojia Iliyotumika

| Technology | Usage |
|---|---|
| HTML5 | Page structure and semantic content |
| CSS3 | Responsive layouts, styling, and animations |
| Vanilla JavaScript | Navigation, gallery interactions, and form behavior |
| PHP 8.1+ | Contact and admission form processing |
| Font Awesome 6 | Icons, loaded from CDN |
| Google Maps Embed | School location map on the contact page |

No frontend framework such as React, Vue, or Bootstrap is required.

---

## 📸 Pages / Kurasa

| Page | File |
|---|---|
| Home and school programs | `index.html` |
| About Us | `about.html` and the About section on the home page |
| School staff organizational tree | `school-staff.html` |
| Academic programs landing page | `academics/index.html` (the Programs menu currently links to the home page's Academics section) |
| Admissions information | `admissions/index.html` |
| Online admission application | `admissions/apply.html` |
| Joining instructions | `admissions/joining-instructions.html` and `admissions/joining-instructions.pdf` |
| Examination results | `results/index.html` |
| News and events | `news/index.html` |
| Photo gallery | `gallery/index.html` |
| Contact and map | `contact.html` |
| Portal sign-in page | `portal-login.html` |

---

## 📁 Project Structure / Muundo wa Project

```text
margreth-school/
├── index.html
├── about.html
├── contact.html
├── school-staff.html
├── portal-login.html
├── send-message.php
├── send-admission.php
├── mail-config.example.php
├── README.md
├── start-php-server.sh
├── academics/
│   └── index.html
├── admissions/
│   ├── index.html
│   ├── apply.html
│   ├── joining-instructions.html
│   └── joining-instructions.pdf
├── gallery/
│   └── index.html
├── news/
│   └── index.html
├── results/
│   └── index.html
├── css/
│   └── style.css
├── js/
│   └── main.js
└── images/
    └── Site photos, banners, gallery images, and staff portraits
```

---

## 🚀 Getting Started / Jinsi ya Kuanza

### Requirements

- PHP 8.1 or later for the form handlers.
- A web browser.
- SMTP settings to send contact and admission form submissions.

### Run locally

From the project root, run:

```bash
./start-php-server.sh
```

Then open [http://127.0.0.1:8000](http://127.0.0.1:8000).

Alternatively, use PHP's built-in web server:

```bash
php -S 127.0.0.1:8000 -t .
```

Opening the HTML files directly with a `file://` URL will not run the PHP form handlers.

---

## 📧 Email Configuration / Mipangilio ya Barua Pepe

1. Copy `mail-config.example.php` to `mail-config.php`.
2. Add the SMTP host, port, security mode, username, and password for the school's sending account.
3. Keep `mail-config.php` private and do not commit real credentials to the repository.
4. Ensure the hosting provider allows outbound SMTP connections.

The contact form is processed by `send-message.php`. Online admission applications are processed by `send-admission.php`, including the uploaded supporting documents.

---

## 🎨 Design / Muonekano

- Responsive CSS Grid and Flexbox layouts.
- Blue and teal accents with light backgrounds.
- Shared header, dropdown navigation, and footer across pages.
- Staff tree layout with circular portraits and hover details.
- Cards for admissions, school values, and examination results.

---

## 🖼️ Images / Picha

Images are stored in `images/`. The website currently uses `.jpg`, `.jpeg`, and `.png` assets for the school, gallery, banners, and staff portraits. Keep image paths and filenames in the HTML in sync when replacing or adding images. Use descriptive `alt` text for images that convey information.

---

## 🤝 Contributing / Kuchangia

1. Make changes in a separate branch when using Git.
2. Keep layouts responsive and preserve the shared navigation and styles.
3. Do not commit private SMTP credentials or student and staff personal data.
4. Describe the changes and affected pages in your pull request.

---

## 📄 License / Leseni

No license has been specified for this project. Contact the project owner before reusing or distributing its content and assets.

---

## 👨‍💻 Author / Mwandishi

Developed for **Margreth Pre & Primary School**. For website content and school inquiries, use the contact details listed on the website.
