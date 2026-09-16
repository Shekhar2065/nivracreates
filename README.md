# Nivra portfolio

A responsive, one-page portfolio demo for Nivra, built with PHP 8+, MySQL, Tailwind CSS, vanilla JavaScript, GSAP and ScrollTrigger. All projects and testimonials shipped with the demo are explicitly labeled placeholders; no results, clients, awards or reviews are presented as factual.

## Admin panel

Open `http://localhost/Nivra/admin/` and complete the one-time owner setup. The panel includes editable homepage/contact/social/SEO settings, project and testimonial publishing, safe image uploads, an inquiry inbox, and account management.

Password recovery is assigned initially to `+9779845895222`. To deliver real OTP messages, create a Twilio Verify service and add its Account SID, Auth Token and Verify Service SID under `sms` in the ignored `config/config.php`. OTP values and credentials are never stored in the repository. Existing databases can be upgraded with `database/admin_upgrade.sql`; new installations use `database/nivra_portfolio.sql`.

## Run locally with XAMPP

1. Put the project at `C:\xampp\htdocs\Nivra` (this workspace is already in that location).
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open `http://localhost/Nivra/`.

The public site works without a database by using the labeled fallback content in `index.php`. The contact form requires the database setup below.

## Create the database

1. Open `http://localhost/phpmyadmin/`.
2. Choose **Import**.
3. Select `database/nivra_portfolio.sql` and run the import.
4. Copy `config/config.example.php` to `config/config.php`.
5. Update the local file with the MySQL host, port, database name and user used by XAMPP.

`config/config.php` is ignored by Git. Never commit a password. Production credentials should be injected by the host or stored in a server-only configuration file.

Example XAMPP development configuration:

```php
return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'nivra_portfolio',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];
```

The form validates in the browser and again in PHP, uses a session CSRF token, honeypot, 30-second session rate limit and PDO prepared statement, and preserves safe entered values after an error. It stores inquiries only; it does not claim to send email.

## Replace brand and project content

### Original logo

The supplied Nivra artwork is stored as `assets/images/nivra-logo.png`. A tightly trimmed, transparency-cleaned navbar export is stored as `assets/images/nivra-logo-navbar-clean.png` and used by `includes/header.php`. Replace both files together if a higher-resolution master logo becomes available.

### Project images

The three PNG files in `assets/images/` are AI-generated demo photography with no embedded logos or claims. Optimized WebP versions are served first through `<picture>`, with the PNG originals as fallbacks. Replace both formats with approved exports, keep meaningful `alt` text, and update the image paths in both `index.php` and the `projects` database table. A practical source size is 1800–2400 pixels wide.

### Café case study

The supplied Instagram screenshot was used to isolate the Salt & Pepper Cafe mark as `assets/images/salt-and-pepper-cafe-logo.png`. The website serves the optimized `assets/images/salt-and-pepper-cafe-logo-640.png` in the hero and café case study. Replace both with the standalone original logo file when available for maximum fidelity. Replace `assets/images/cafe-project.png` with approved campaign photography, then edit the café section in `index.php`. Add the real services, duration, objective, approved before/after assets and verified result. Keep “Add verified result” until the result has been checked and approved.

### Testimonials

Update the `testimonials` table through phpMyAdmin or replace the fallback array near the top of `index.php`. Remove the placeholder label only after the client has approved the exact wording, name, company and role.

### Contact details

Search `index.php` and `includes/header.php` for `example.com`, `Add phone number`, `Add business location`, Instagram and Facebook placeholders. Update the structured data and visible contact links together. Add a WhatsApp link only after a real business number is supplied.

## Styling and production build

The demo uses the Tailwind CDN for quick XAMPP setup and `assets/css/app.css` for motion/layout effects that would be awkward as utilities. `assets/css/input.css` is the starting point for a compiled Tailwind build. Before production, install Tailwind locally, define the PHP/JS content paths, compile/minify the CSS, and remove the CDN script.

GSAP and ScrollTrigger are loaded from CDN. The site remains readable without JavaScript, and advanced motion is disabled for reduced-motion users and simplified on mobile/low-powered devices.

## Deploy

1. Use PHP 8.0+ with PDO MySQL and `mbstring` enabled.
2. Create a UTF-8 MySQL database and import `database/nivra_portfolio.sql`.
3. Create a server-only `config/config.php` with production credentials.
4. Replace the canonical URL, Open Graph image, structured-data placeholders and all contact details.
5. Serve over HTTPS so the session cookie receives the `Secure` flag.
6. Compile Tailwind, optimize images to WebP/AVIF, enable server compression and long-lived caching for versioned assets.
7. Keep PHP error display disabled in production and log errors outside the public web root.
8. Back up `contact_messages` and protect database/phpMyAdmin access at the hosting layer.

## Project map

```text
Nivra/
├── index.php
├── contact-submit.php
├── config/
│   ├── database.php
│   └── config.example.php
├── includes/
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/input.css
│   ├── css/app.css
│   ├── js/main.js
│   └── images/
├── database/nivra_portfolio.sql
├── .gitignore
└── README.md
```
