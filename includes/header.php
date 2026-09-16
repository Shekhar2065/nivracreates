<?php
declare(strict_types=1);
$pageTitle = $pageTitle ?? 'Nivra';
$pageDescription = $pageDescription ?? 'Nivra is an independent digital marketing agency for strategy, content, social media and performance marketing.';
?>
<!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="theme-color" content="#F4F5EF">
    <link rel="canonical" href="https://example.com/">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="assets/images/cafe-project.webp">
    <link rel="icon" href="assets/images/nivra-symbol-favicon-white.png?v=<?= (int) filemtime(__DIR__ . '/../assets/images/nivra-symbol-favicon-white.png') ?>" type="image/png">
    <link rel="preload" href="assets/images/work-01.webp" as="image" type="image/webp">
    <link rel="preload" href="assets/images/work-02.webp" as="image" type="image/webp">
    <link rel="preload" href="assets/images/work-03.webp" as="image" type="image/webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { navy: '#0A3557', ink: '#052742', ivory: '#E9EDE2', paper: '#F4F5EF', acid: '#DFFF43' },
            fontFamily: { sans: ['Arial', 'Helvetica Neue', 'sans-serif'], serif: ['Georgia', 'Times New Roman', 'serif'] },
            screens: { '3xl': '1800px' }
        }}};
    </script>
    <link rel="stylesheet" href="assets/css/app.css?v=<?= (int) filemtime(__DIR__ . '/../assets/css/app.css') ?>">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ProfessionalService",
      "name": "Nivra",
      "url": "https://example.com/",
      "description": "Independent digital marketing agency",
      "email": "hello@example.com",
      "address": {"@type": "PostalAddress", "addressLocality": "Add business location"}
    }
    </script>
</head>
<body class="bg-paper text-ink antialiased selection:bg-acid selection:text-ink">
<a class="skip-link" href="#main-content">Skip to content</a>
<div id="scroll-progress" aria-hidden="true"></div>
<header class="site-header fixed inset-x-0 top-0 z-50 border-b border-ink/15 bg-paper/95 backdrop-blur-sm">
    <nav class="mx-auto flex h-[76px] max-w-[1600px] items-center px-5 md:h-[86px] md:px-10" aria-label="Primary navigation">
        <a href="#top" class="logo-link flex shrink-0 items-center" aria-label="Nivra, home">
            <img class="nivra-nav-logo" src="assets/images/nivra-logo-navbar-clean.png" width="420" height="210" alt="Nivra">
        </a>
        <button id="menu-toggle" class="ml-auto flex min-h-11 min-w-11 items-center justify-center" type="button" aria-expanded="false" aria-controls="mobile-menu" aria-label="Open menu">
            <span class="hamburger" aria-hidden="true"><i></i><i></i><i></i></span>
        </button>
    </nav>
</header>
<div id="mobile-menu" class="mobile-menu" aria-hidden="true">
    <div class="mx-auto flex h-full w-full max-w-[1600px] flex-col justify-between px-6 pb-8 pt-28 md:px-10 md:pb-12 md:pt-32">
        <div class="flex flex-col">
            <a class="mobile-link" href="#about">About</a>
            <a class="mobile-link" href="#services">Services</a>
            <a class="mobile-link" href="#work">Work</a>
            <a class="mobile-link" href="#social">Social</a>
            <a class="mobile-link" href="#contact">Contact</a>
        </div>
        <p class="text-sm text-ivory/60">Strategy · Creative · Growth</p>
    </div>
</div>
