<?php
declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

require_once __DIR__ . '/config/database.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$flash = $_SESSION['form_flash'] ?? null;
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_flash'], $_SESSION['form_old']);

// Clearly labeled demo content. Replace from MySQL or update in this file.
$projects = [
    [
        'title' => 'Salt & Pepper Cafe', 'slug' => 'cafe-campaign', 'client_name' => 'Salt & Pepper Cafe',
        'industry' => 'Hospitality', 'services' => 'Campaign Strategy · Photography · Social Content',
        'short_description' => 'A focused digital campaign designed to make the cafe’s food, atmosphere and personality instantly recognisable across social media.',
        'image' => 'assets/images/cafe-project.png', 'logo_image' => 'assets/images/salt-and-pepper-cafe-logo-640.png', 'project_year' => '', 'verified_result' => null,
    ],
    [
        'title' => 'Himalayan Arts', 'slug' => 'himalayan-arts', 'client_name' => 'Himalayan Arts',
        'industry' => 'Art Gallery', 'services' => 'Digital showcase · Social content',
        'short_description' => 'A digital presentation of Himalayan artistic heritage, bringing traditional landscapes and gallery collections to an online audience.',
        'image' => 'assets/images/himalayan-arts-temple.png', 'logo_image' => 'assets/images/himalayan-arts-mark.svg', 'project_year' => 'Project', 'verified_result' => null,
    ],
];

$projectGalleries = [
    'cafe-campaign' => [
        ['image_path' => 'assets/images/cafe-project.png', 'alt_text' => 'Coffee and pastry campaign photograph for Salt & Pepper Cafe'],
        ['image_path' => 'assets/images/salt-pepper-food-platter.png', 'alt_text' => 'Salt & Pepper Cafe platter photographed for its social campaign'],
        ['image_path' => 'assets/images/salt-pepper-savoury-sweet.png', 'alt_text' => 'Savoury ribs and dessert from the Salt & Pepper Cafe menu'],
        ['image_path' => 'assets/images/salt-pepper-cafe-exterior.png', 'alt_text' => 'Outdoor seating and greenery at Salt & Pepper Cafe'],
        ['image_path' => 'assets/images/salt-pepper-table.png', 'alt_text' => 'Wooden table and chairs at Salt & Pepper Cafe'],
    ],
    'himalayan-arts' => [
        ['image_path' => 'assets/images/himalayan-arts-temple.png', 'alt_text' => 'Framed Himalayan Arts painting of a golden Kathmandu square'],
        ['image_path' => 'assets/images/himalayan-arts-yaks.png', 'alt_text' => 'Framed Himalayan Arts painting of a mountain caravan'],
        ['image_path' => 'assets/images/himalayan-arts-gallery-wall.png', 'alt_text' => 'Curated wall of framed Himalayan mountain paintings'],
    ],
];

$testimonials = [
    [
        'testimonial' => 'Nivra understood the personality of our cafe and translated it into content that felt consistent, natural and recognisable.',
        'client_name' => 'Salt & Pepper Cafe', 'company_name' => 'Salt & Pepper Cafe', 'client_role' => 'Campaign client',
        'project_slug' => 'cafe-campaign', 'label' => 'Client testimonial',
    ],
    [
        'testimonial' => 'Nivra gave our collection a digital presence that feels considered, accessible and true to the spirit of Himalayan art.',
        'client_name' => 'Himalayan Arts', 'company_name' => 'Himalayan Arts', 'client_role' => 'Gallery client',
        'project_slug' => 'himalayan-arts', 'label' => 'Client testimonial',
    ],
];

$siteSettings = [
    'site_name' => 'Nivra', 'seo_title' => 'Nivra',
    'seo_description' => 'Nivra combines strategy, content and performance marketing to help ambitious businesses become impossible to ignore.',
    'hero_kicker' => 'Independent digital marketing agency',
    'hero_headline_lead' => 'Ideas',
    'hero_headline_tail' => 'that move.',
    'hero_support' => 'A creative growth studio building brands through strategy, content and performance.',
    'hero_final_lead' => 'We',
    'hero_final_text' => 'shape bold ideas into brands people notice, trust, and remember.',
    'hero_cta_label' => 'Work with us',
    'hero_image_1' => 'assets/images/himalayan-arts-bts-camera.png',
    'hero_image_1_alt' => 'Behind-the-scenes camera photographing artwork for Himalayan Arts',
    'hero_image_2' => 'assets/images/himalayan-arts-temple.png',
    'hero_image_2_alt' => 'Himalayan Arts campaign featuring a framed Kathmandu painting',
    'hero_image_3' => 'assets/images/work-03.jpg',
    'hero_image_3_alt' => 'Service brand strategy campaign created by Nivra',
    'about_intro' => 'Nivra is an independent digital marketing agency helping businesses build stronger brands through strategy, creative content, social media and performance-focused campaigns.',
    'founder_name' => '[Founder Name]', 'founder_role' => 'Founder & Creative Director',
    'founder_image' => '', 'founder_image_alt' => 'Portrait of the founder of Nivra',
    'founder_quote' => 'I started Nivra to give ambitious businesses clear strategy, purposeful creative work and the attention they rarely receive from traditional agencies.',
    'work_principles_heading' => 'How we work',
    'work_principle_1' => 'Strategy before execution',
    'work_principle_2' => 'Creative with a clear purpose',
    'work_principle_3' => 'Honest communication',
    'work_principle_4' => 'Measurable improvement',
    'work_principle_5' => 'No unnecessary agency layers',
    'contact_section_label' => '05 — Contact',
    'contact_heading_lead' => 'Let’s begin',
    'contact_heading_accent' => 'the project.',
    'contact_intro' => 'Tell us where the business is now, and where you want it to go. We’ll respond with the most useful next step.',
    'contact_email_label' => 'Email',
    'contact_phone_label' => 'Phone',
    'contact_location_label' => 'Location',
    'contact_name_label' => 'Name',
    'contact_form_email_label' => 'Email',
    'contact_form_phone_label' => 'Phone',
    'contact_company_label' => 'Business or company',
    'contact_service_label' => 'Required service',
    'contact_service_placeholder' => 'Choose a service',
    'contact_service_extra_options' => "Integrated campaign\nNot sure yet",
    'contact_message_label' => 'Project description',
    'contact_message_note' => 'A few lines about your goal, timing and what you need help with.',
    'contact_submit_label' => 'Send inquiry',
    'contact_email' => '', 'contact_phone' => '+977 9845895222', 'location' => 'Nepal',
    'instagram_url' => 'https://www.instagram.com/nivra.creates/', 'tiktok_url' => 'https://www.tiktok.com/@nivra.creates',
    'whatsapp_number' => '9779845895222',
    'services_heading' => 'One connected system, from the first question to the next move.',
    'service_1_title' => 'Strategy', 'service_1_description' => 'Find the sharpest position before spending time, attention or budget.', 'service_1_items' => "Brand positioning\nMarketing audits\nCampaign planning\nAudience research",
    'service_2_title' => 'Creative', 'service_2_description' => 'Turn clear thinking into work people recognise, remember and share.', 'service_2_items' => "Content creation\nGraphic design\nCampaign concepts\nPhotography & video direction",
    'service_3_title' => 'Growth', 'service_3_description' => 'Build useful feedback loops and make each campaign smarter than the last.', 'service_3_items' => "Social media management\nPaid advertising\nCampaign optimisation\nPerformance reporting",
];

// Published database content replaces demo entries when a configured database has records.
$db = nivra_db();
if ($db) {
    try {
        $dbSettings = $db->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll(PDO::FETCH_KEY_PAIR);
        $siteSettings = array_replace($siteSettings, $dbSettings);
        $dbProjects = $db->query('SELECT title, slug, client_name, industry, services, short_description, image, logo_image, project_year, verified_result, project_url, project_cta FROM projects WHERE is_published = 1 ORDER BY display_order, id')->fetchAll();
        $dbTestimonials = $db->query('SELECT t.client_name, t.company_name, t.client_role, t.testimonial, p.slug AS project_slug FROM testimonials t LEFT JOIN projects p ON p.id = t.project_id WHERE t.is_published = 1 ORDER BY t.display_order, t.id')->fetchAll();
        if ($dbProjects) {
            $projects = $dbProjects;
            $dbProjectImages = $db->query('SELECT p.slug AS project_slug, pi.image_path, pi.alt_text FROM project_images pi JOIN projects p ON p.id = pi.project_id WHERE p.is_published = 1 ORDER BY p.display_order, p.id, pi.display_order, pi.id')->fetchAll();
            $projectGalleries = [];
            foreach ($dbProjectImages as $projectImage) {
                $projectGalleries[(string) $projectImage['project_slug']][] = $projectImage;
            }
        }
        if ($dbTestimonials) {
            $testimonials = array_map(static fn(array $item): array => $item + ['label' => 'Client testimonial'], $dbTestimonials);
        }
    } catch (PDOException $exception) {
        error_log('Nivra content query failed: ' . $exception->getMessage());
    }
}

// Keep the removed demo out of the public slider even when an older database
// installation still contains its original seed record.
$projects = array_values(array_filter(
    $projects,
    static fn(array $project): bool => ($project['slug'] ?? '') !== 'service-business'
));

// Internal placeholders are never rendered in the public portfolio UI.
$isPublicProjectValue = static function (?string $value): bool {
    $value = trim((string) $value);
    return $value !== '' && !preg_match('/\b(sample|demo|placeholder|replace|to confirm|pending|services to confirm|not a real client)\b/i', $value);
};

foreach ($projects as &$project) {
    if (($project['slug'] ?? '') !== 'cafe-campaign') {
        continue;
    }
    if (!$isPublicProjectValue((string) ($project['client_name'] ?? ''))) {
        $project['client_name'] = 'Salt & Pepper Cafe';
    }
    if (!$isPublicProjectValue((string) ($project['services'] ?? ''))) {
        $project['services'] = 'Campaign Strategy · Photography · Social Content';
    }
    if (!$isPublicProjectValue((string) ($project['short_description'] ?? ''))) {
        $project['short_description'] = 'A focused digital campaign designed to make the cafe’s food, atmosphere and personality instantly recognisable across social media.';
    }
    if (!$isPublicProjectValue((string) ($project['project_year'] ?? ''))) {
        $project['project_year'] = '';
    }
}
unset($project);

$projectTestimonials = [];
foreach ($testimonials as $testimonial) {
    $projectSlug = trim((string) ($testimonial['project_slug'] ?? ''));
    $genericIdentityValues = ['client name', 'client position', 'business name', 'cafe name', 'role'];
    $identityValues = array_map(
        static fn(string $value): string => strtolower(trim($value)),
        [(string) ($testimonial['client_name'] ?? ''), (string) ($testimonial['client_role'] ?? ''), (string) ($testimonial['company_name'] ?? '')]
    );
    $isPublished = $isPublicProjectValue((string) ($testimonial['testimonial'] ?? ''))
        && $isPublicProjectValue((string) ($testimonial['client_name'] ?? ''))
        && $isPublicProjectValue((string) ($testimonial['client_role'] ?? ''))
        && $isPublicProjectValue((string) ($testimonial['company_name'] ?? ''))
        && count(array_intersect($genericIdentityValues, $identityValues)) === 0;

    if ($isPublished && $projectSlug !== '') {
        $projectTestimonials[$projectSlug] = $testimonial;
    }
}

$contactExtraServices = preg_split('/\r\n|\r|\n/', (string) $siteSettings['contact_service_extra_options']) ?: [];
$contactServiceOptions = array_values(array_unique(array_filter([
    (string) $siteSettings['service_1_title'],
    (string) $siteSettings['service_2_title'],
    (string) $siteSettings['service_3_title'],
    ...array_map('trim', $contactExtraServices),
], static fn(string $option): bool => $option !== '')));

$pageTitle = $siteSettings['seo_title'];
$pageDescription = $siteSettings['seo_description'];
require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section id="top" class="hero hero-scroll-section" aria-labelledby="hero-heading">
        <div class="hero-stage">
            <p class="hero-kicker"><?= e($siteSettings['hero_kicker']) ?></p>

            <div class="hero-opening">
                <h1 id="hero-heading" class="hero-opening-line" aria-label="<?= e(trim($siteSettings['hero_headline_lead'] . ' ' . $siteSettings['hero_headline_tail'])) ?>">
                    <span class="hero-opening-track">
                        <span><?= e($siteSettings['hero_headline_lead']) ?></span>
                        <span class="hero-star" aria-hidden="true">✦</span>
                        <span class="hero-headline-muted"><?= e($siteSettings['hero_headline_tail']) ?></span>
                    </span>
                </h1>
            </div>

            <p class="hero-support"><?= e($siteSettings['hero_support']) ?></p>

            <div class="hero-final">
                <p class="hero-final-statement">
                    <span><?= e($siteSettings['hero_final_lead']) ?></span>
                    <span class="hero-final-media-row" aria-hidden="true">
                        <span class="hero-image-anchor" data-hero-anchor="0"></span>
                        <span class="hero-image-anchor" data-hero-anchor="1"></span>
                        <span class="hero-image-anchor" data-hero-anchor="2"></span>
                    </span>
                    <span><?= e($siteSettings['hero_final_text']) ?></span>
                </p>
                <a class="hero-cta" href="#contact"><?= e($siteSettings['hero_cta_label']) ?> <span aria-hidden="true">↗</span></a>
            </div>

            <div class="hero-card-layer" aria-label="Selected Nivra project images">
                <picture class="hero-project-card hero-project-card-1">
                    <img src="<?= e($siteSettings['hero_image_1']) ?>" width="477" height="423" alt="<?= e($siteSettings['hero_image_1_alt']) ?>" fetchpriority="high">
                </picture>
                <picture class="hero-project-card hero-project-card-2">
                    <img src="<?= e($siteSettings['hero_image_2']) ?>" width="743" height="616" alt="<?= e($siteSettings['hero_image_2_alt']) ?>" fetchpriority="high">
                </picture>
                <picture class="hero-project-card hero-project-card-3">
                    <img src="<?= e($siteSettings['hero_image_3']) ?>" width="900" height="700" alt="<?= e($siteSettings['hero_image_3_alt']) ?>" fetchpriority="high">
                </picture>
            </div>

            <div class="hero-scroll-cue" aria-hidden="true"><span>Scroll to explore</span><i></i></div>
        </div>
    </section>

    <section id="about" class="about-section section-space bg-ivory px-5 md:px-10">
        <div class="mx-auto max-w-[1600px]">
            <p class="about-label eyebrow">01 — About</p>

            <div class="about-layout mt-10 grid gap-12 md:mt-14 lg:grid-cols-12 lg:gap-6">
                <div class="lg:col-span-5 lg:col-start-2">
                    <div class="about-copy">
                        <p class="text-2xl leading-snug tracking-[-.035em] md:text-4xl"><?= e($siteSettings['about_intro']) ?></p>
                    </div>

                    <div class="founder-block mt-10 max-w-[500px] border-t border-ink/20 pt-6 md:mt-12">
                        <div class="flex items-center gap-4 md:gap-5">
                            <?php $founderImage = trim((string) $siteSettings['founder_image']); ?>
                            <?php if ($founderImage !== '' && is_file(__DIR__ . '/' . $founderImage)): ?>
                                <img class="founder-photo h-14 w-14 shrink-0 rounded-full object-cover md:h-[68px] md:w-[68px]" src="<?= e($founderImage) ?>" width="68" height="68" alt="<?= e($siteSettings['founder_image_alt']) ?>" loading="lazy">
                            <?php else: ?>
                                <span class="founder-photo grid h-14 w-14 shrink-0 place-items-center rounded-full border border-ink/20 bg-paper text-ink/55 md:h-[68px] md:w-[68px]" role="img" aria-label="Founder photograph placeholder">
                                    <svg class="h-7 w-7 md:h-8 md:w-8" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                                        <circle cx="16" cy="11" r="5" stroke="currentColor" stroke-width="1.5"></circle>
                                        <path d="M6.5 27c.8-6 4.1-9 9.5-9s8.7 3 9.5 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    </svg>
                                </span>
                            <?php endif; ?>
                            <div class="founder-meta min-w-0">
                                <p class="text-base font-medium text-ink">Founded by <span><?= e($siteSettings['founder_name']) ?></span></p>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-[.16em] text-ink/50"><?= e($siteSettings['founder_role']) ?></p>
                            </div>
                        </div>
                        <blockquote class="founder-message mt-5 text-base leading-relaxed text-ink/75 md:text-lg">
                            “<?= e($siteSettings['founder_quote']) ?>”
                        </blockquote>
                        <p class="founder-signature mt-3 font-serif text-xl italic text-ink/65"><?= e($siteSettings['founder_name']) ?></p>
                        <span class="founder-connector mt-6 flex items-center gap-3 text-ink/35" aria-hidden="true"><i></i><b>↓</b></span>
                    </div>
                </div>

                <aside class="about-principles self-start bg-ink p-6 text-ivory md:p-8 lg:col-span-5 lg:col-start-8 lg:p-10">
                    <p class="eyebrow !text-acid"><?= e($siteSettings['work_principles_heading']) ?></p>
                    <ol class="mt-6 divide-y divide-ivory/20">
                        <?php foreach (range(1, 5) as $index): ?>
                            <?php $principle = (string) $siteSettings['work_principle_' . $index]; ?>
                            <li class="about-principle-row flex items-center gap-5 py-3.5">
                                <span class="font-mono text-xs text-ivory/45"><?= str_pad((string) $index, 2, '0', STR_PAD_LEFT) ?></span>
                                <span class="about-principle-text min-w-0 flex-1 text-sm md:text-base"><?= e($principle) ?></span>
                                <span class="about-principle-arrow text-acid" aria-hidden="true">→</span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </aside>
            </div>

            <div class="about-why mt-16 border-t border-ink/20 pt-8 md:mt-24 md:pt-10 lg:grid lg:grid-cols-12 lg:items-start lg:gap-6">
                <div class="about-why-intro lg:col-span-3">
                    <div class="about-why-kicker">
                        <span aria-hidden="true"></span>
                        <h2 class="about-why-title eyebrow">Why choose Nivra</h2>
                    </div>
                    <p class="mt-4 max-w-[230px] text-sm leading-relaxed text-ink/45">Clear thinking, close collaboration and creative work built around your business.</p>
                </div>
                <div class="about-why-content mt-7 lg:col-span-9 lg:col-start-4 lg:mt-0">
                    <p class="about-why-statement">Choose Nivra when you need <em>clear direction,</em> purposeful creative work and a team that stays close from the first question to the next move.</p>

                    <ol class="about-why-principles mt-10 md:mt-12">
                        <li class="about-why-principle">
                            <span class="about-why-number font-mono text-xs text-ink/45">01</span>
                            <h3>Clarity before activity</h3>
                            <p>We turn scattered ideas into a focused position, useful priorities and a practical plan your team can act on.</p>
                        </li>
                        <li class="about-why-principle">
                            <span class="about-why-number font-mono text-xs text-ink/45">02</span>
                            <h3>One connected system</h3>
                            <p>Strategy, content, creative direction and performance work together, so your brand feels consistent everywhere it appears.</p>
                        </li>
                        <li class="about-why-principle">
                            <span class="about-why-number font-mono text-xs text-ink/45">03</span>
                            <h3>Close attention, real momentum</h3>
                            <p>You work close to the people shaping the work, with honest communication, fewer layers and decisions that keep progress moving.</p>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="section-space overflow-hidden px-5 md:px-10">
        <div class="services-stage mx-auto max-w-[1600px]">
            <div class="grid gap-8 md:grid-cols-12">
                <p class="eyebrow md:col-span-3">02 — Services</p>
                <div class="md:col-span-8">
                    <h2 class="section-title reveal-text"><?= e($siteSettings['services_heading']) ?></h2>
                </div>
            </div>
            <div class="services-stack mt-16 md:mt-24">
                <?php
                $services = [];
                for ($serviceIndex = 1; $serviceIndex <= 3; $serviceIndex++) {
                    $items = array_values(array_filter(array_map('trim', preg_split('/\R/', $siteSettings['service_' . $serviceIndex . '_items']) ?: [])));
                    $services[] = [str_pad((string) $serviceIndex, 2, '0', STR_PAD_LEFT), $siteSettings['service_' . $serviceIndex . '_title'], $siteSettings['service_' . $serviceIndex . '_description'], $items];
                }
                foreach ($services as $service): ?>
                    <article class="service-card grid gap-8 border border-ink/20 bg-paper p-6 md:grid-cols-12 md:p-10 lg:p-14">
                        <span class="font-mono text-sm md:col-span-1"><?= e($service[0]) ?></span>
                        <h3 class="text-5xl font-semibold tracking-[-.06em] md:col-span-4 md:text-7xl"><?= e($service[1]) ?></h3>
                        <p class="max-w-md text-lg leading-relaxed text-ink/70 md:col-span-3"><?= e($service[2]) ?></p>
                        <ul class="space-y-2 text-sm md:col-span-4 md:justify-self-end">
                            <?php foreach ($service[3] as $item): ?><li class="flex items-center gap-3"><span class="h-1.5 w-1.5 bg-acid" aria-hidden="true"></span><?= e($item) ?></li><?php endforeach; ?>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="work" class="work-section px-5 text-ivory md:px-10" aria-labelledby="work-heading">
        <div class="work-sticky mx-auto max-w-[1600px]">
            <header class="work-header">
                <div class="work-header-meta">
                    <p class="work-section-label eyebrow">03 — Selected work</p>
                    <p class="work-scroll-note">Scroll to explore <span aria-hidden="true">↓</span></p>
                    <p class="work-header-counter" aria-live="polite"><span id="work-current">01</span> / <?= str_pad((string) count($projects), 2, '0', STR_PAD_LEFT) ?></p>
                </div>
                <h2 id="work-heading" class="work-heading">
                    <span class="work-heading-mask"><span>Selected work,</span></span>
                    <span class="work-heading-mask"><span><em>built to be remembered.</em></span></span>
                </h2>
            </header>

            <div class="work-stage" data-work-slider>
                <div class="work-track" id="work-track" aria-live="polite">
                    <?php foreach ($projects as $index => $project): ?>
                        <?php
                        $slug = (string) ($project['slug'] ?? $index);
                        $galleryImages = $projectGalleries[$slug] ?? [];
                        if ($galleryImages === [] && !empty($project['image'])) {
                            $galleryImages[] = [
                                'image_path' => (string) $project['image'],
                                'alt_text' => (string) $project['title'] . ' project image',
                            ];
                        }
                        $galleryCount = count($galleryImages);
                        $projectTestimonial = $projectTestimonials[$slug] ?? null;
                        $btsModalId = $slug === 'cafe-campaign' ? 'bts-cafe-modal' : ($slug === 'himalayan-arts' ? 'bts-himalayan-modal' : '');
                        $btsDialogId = $slug === 'cafe-campaign' ? 'bts-cafe-dialog' : ($slug === 'himalayan-arts' ? 'bts-himalayan-dialog' : '');
                        $projectLogoPath = trim((string) ($project['logo_image'] ?? ''));
                        $projectLogo = $projectLogoPath !== ''
                            ? [
                                'src' => $projectLogoPath,
                                'class' => $slug === 'himalayan-arts' ? 'work-project-logo--himalayan' : '',
                            ]
                            : null;
                        ?>
                        <article id="project-<?= e($slug) ?>" class="work-panel <?= $index === 0 ? 'is-current' : '' ?>" aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>" data-project-title="<?= e((string) $project['title']) ?>">
                            <div class="work-panel-grid">
                                <div class="work-media-column">
                                    <div class="work-panel-image">
                                        <div class="project-gallery" data-project-gallery aria-live="polite">
                                            <?php foreach ($galleryImages as $imageIndex => $galleryImage): ?>
                                                <figure class="project-gallery-slide <?= $imageIndex === 0 ? 'is-active' : '' ?>" aria-hidden="<?= $imageIndex === 0 ? 'false' : 'true' ?>">
                                                    <img src="<?= e((string) $galleryImage['image_path']) ?>" alt="<?= e((string) ($galleryImage['alt_text'] ?: $project['title'] . ' project image')) ?>" loading="<?= $index === 0 && $imageIndex === 0 ? 'eager' : 'lazy' ?>">
                                                </figure>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if ($galleryCount > 1): ?>
                                            <div class="project-gallery-controls">
                                                <button type="button" data-gallery-prev aria-label="Previous <?= e((string) $project['title']) ?> image">←</button>
                                                <span><strong data-gallery-current>01</strong> / <?= str_pad((string) $galleryCount, 2, '0', STR_PAD_LEFT) ?></span>
                                                <button type="button" data-gallery-next aria-label="Next <?= e((string) $project['title']) ?> image">→</button>
                                                <button class="gallery-fullscreen-button" type="button" data-gallery-fullscreen aria-label="View gallery full screen" aria-pressed="false">
                                                    <svg class="gallery-fullscreen-enter" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M8 3H3v5M16 3h5v5M8 21H3v-5M16 21h5v-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="square"></path>
                                                    </svg>
                                                    <svg class="gallery-fullscreen-exit" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M3 8h5V3M21 8h-5V3M3 16h5v5M21 16h-5v5" stroke="currentColor" stroke-width="1.7" stroke-linecap="square"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                        <span class="work-panel-number">0<?= $index + 1 ?></span>
                                    </div>

                                    <?php if ($btsModalId !== ''): ?>
                                        <button class="work-bts-link work-bts-desktop" type="button" data-bts-open="<?= e($btsModalId) ?>" aria-haspopup="dialog" aria-controls="<?= e($btsDialogId) ?>">
                                            <span>BTS</span> Behind the scenes <b aria-hidden="true">→</b>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div class="work-panel-copy">
                                    <p class="work-category"><?= e((string) $project['industry']) ?></p>
                                    <div class="work-title-row">
                                        <?php if ($projectLogo): ?>
                                            <img class="work-project-logo <?= e($projectLogo['class']) ?>" src="<?= e($projectLogo['src']) ?>" alt="" aria-hidden="true">
                                        <?php endif; ?>
                                        <h3><?= e((string) $project['title']) ?></h3>
                                    </div>
                                    <p class="work-services"><?= e((string) $project['services']) ?></p>
                                    <p class="work-description"><?= e((string) $project['short_description']) ?></p>

                                    <?php if ($projectTestimonial): ?>
                                        <blockquote class="work-panel-quote">
                                            <p class="work-panel-quote-label"><?= e((string) ($projectTestimonial['label'] ?? 'Client testimonial')) ?></p>
                                            <span aria-hidden="true">“</span>
                                            <p><?= e((string) $projectTestimonial['testimonial']) ?></p>
                                            <footer><strong><?= e((string) $projectTestimonial['client_name']) ?></strong><em><?= e((string) $projectTestimonial['client_role']) ?></em></footer>
                                        </blockquote>
                                    <?php endif; ?>

                                    <?php if ($btsModalId !== ''): ?>
                                        <button class="work-bts-link work-bts-mobile" type="button" data-bts-open="<?= e($btsModalId) ?>" aria-haspopup="dialog" aria-controls="<?= e($btsDialogId) ?>">
                                            <span>BTS</span> Behind the scenes <b aria-hidden="true">→</b>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <nav class="work-project-nav" aria-label="Project navigation">
                    <button type="button" id="work-prev" aria-label="Previous project"><span aria-hidden="true">←</span> Previous project</button>
                    <div class="work-progress" aria-hidden="true"><span id="work-progress-fill"></span></div>
                    <p><span id="work-nav-current">01</span> / <?= str_pad((string) count($projects), 2, '0', STR_PAD_LEFT) ?></p>
                    <button type="button" id="work-next" aria-label="Next project">Next project <span aria-hidden="true">→</span></button>
                </nav>
            </div>
        </div>
    </section>

    <div id="bts-cafe-modal" class="bts-modal" aria-hidden="true">
        <button class="bts-backdrop" type="button" data-bts-close aria-label="Close behind-the-scenes gallery"></button>
        <section id="bts-cafe-dialog" class="bts-dialog" role="dialog" aria-modal="true" aria-labelledby="bts-cafe-title" tabindex="-1">
            <div class="flex items-start justify-between gap-6 border-b border-ivory/20 pb-5">
                <div>
                    <p class="eyebrow !text-acid">Salt & Pepper Cafe · BTS</p>
                    <h2 id="bts-cafe-title" class="mt-3 text-4xl font-semibold tracking-[-.05em] md:text-6xl">Behind the scenes.</h2>
                </div>
                <button class="bts-close" type="button" data-bts-close aria-label="Close behind-the-scenes gallery">×</button>
            </div>
            <p class="mt-6 max-w-2xl text-sm leading-relaxed text-ivory/60">Add approved photographs or video from the cafe campaign here. These placeholders intentionally avoid presenting staged imagery as real behind-the-scenes material.</p>
            <div class="bts-grid mt-8">
                <div class="bts-placeholder"><span>01</span><strong>Add BTS photograph</strong><small>Portrait or detail shot</small></div>
                <div class="bts-placeholder"><span>02</span><strong>Add BTS photograph</strong><small>Production or styling shot</small></div>
                <div class="bts-placeholder"><span>03</span><strong>Add BTS video</strong><small>Short campaign clip</small></div>
            </div>
        </section>
    </div>

    <div id="bts-himalayan-modal" class="bts-modal" aria-hidden="true">
        <button class="bts-backdrop" type="button" data-bts-close aria-label="Close Himalayan Arts behind-the-scenes gallery"></button>
        <section id="bts-himalayan-dialog" class="bts-dialog" role="dialog" aria-modal="true" aria-labelledby="bts-himalayan-title" tabindex="-1">
            <div class="flex items-start justify-between gap-6 border-b border-ivory/20 pb-5">
                <div>
                    <p class="eyebrow !text-acid">Himalayan Arts · BTS</p>
                    <h2 id="bts-himalayan-title" class="mt-3 text-4xl font-semibold tracking-[-.05em] md:text-6xl">Behind the artwork.</h2>
                </div>
                <button class="bts-close" type="button" data-bts-close aria-label="Close Himalayan Arts behind-the-scenes gallery">×</button>
            </div>
            <p class="mt-6 max-w-2xl text-sm leading-relaxed text-ivory/60">A look at the photography setup used to capture and present the Himalayan Arts collection.</p>
            <div class="bts-grid mt-8">
                <figure class="bts-media">
                    <img src="assets/images/himalayan-arts-bts-camera.png" width="477" height="423" alt="Camera positioned above Himalayan Arts paintings during a photography session" loading="lazy">
                    <figcaption><span>01</span><strong>Artwork photography setup</strong><small>Behind the scenes · Himalayan Arts</small></figcaption>
                </figure>
            </div>
        </section>
    </div>

    <section id="social" class="social-section px-5 md:px-10" aria-labelledby="social-heading">
        <div class="social-shell mx-auto max-w-[1600px]">
            <header class="social-intro">
                <p class="eyebrow">04 — Follow along</p>
                <h2 id="social-heading" class="social-heading">Find us in<br><em>the feed.</em></h2>
                <p class="social-lede">Fresh work, behind-the-scenes moments and the ideas shaping what we make next.</p>
            </header>

            <div class="social-links" aria-label="Nivra social profiles">
                <a class="social-link social-link--instagram" href="<?= e($siteSettings['instagram_url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Nivra on Instagram">
                    <span class="social-link-index">01</span>
                    <span class="social-link-icon" aria-hidden="true"><img src="https://cdn.simpleicons.org/instagram/E4405F" width="24" height="24" alt=""></span>
                    <span class="social-link-name"><strong>Instagram</strong><small>@nivra.creates</small></span>
                    <span class="social-link-note">Campaigns, process and studio notes.</span>
                    <span class="social-link-action">Follow <i aria-hidden="true">↗</i></span>
                </a>

                <a class="social-link social-link--tiktok" href="<?= e($siteSettings['tiktok_url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Nivra on TikTok">
                    <span class="social-link-index">02</span>
                    <span class="social-link-icon" aria-hidden="true"><img src="https://cdn.simpleicons.org/tiktok/052742" width="24" height="24" alt=""></span>
                    <span class="social-link-name"><strong>TikTok</strong><small>@nivra.creates</small></span>
                    <span class="social-link-note">Short ideas, motion and creative experiments.</span>
                    <span class="social-link-action">Watch <i aria-hidden="true">↗</i></span>
                </a>
            </div>
        </div>
    </section>

    <section id="contact" class="section-space bg-ivory px-5 md:px-10">
        <div class="mx-auto max-w-[1600px]">
            <p class="eyebrow"><?= e($siteSettings['contact_section_label']) ?></p>
            <h2 class="contact-title mt-8 max-w-[1400px] font-semibold leading-[.84] tracking-[-.075em]" aria-label="<?= e(trim($siteSettings['contact_heading_lead'] . ' ' . $siteSettings['contact_heading_accent'])) ?>">
                <span class="contact-title-line"><span><?= e($siteSettings['contact_heading_lead']) ?></span></span>
                <span class="contact-title-line contact-title-accent">
                    <em class="font-serif font-normal"><?= e($siteSettings['contact_heading_accent']) ?></em>
                    <i aria-hidden="true"></i>
                </span>
            </h2>

            <div class="mt-16 grid gap-14 md:mt-24 md:grid-cols-12">
                <div class="md:col-span-4">
                    <p class="max-w-sm text-lg leading-relaxed text-ink/70"><?= e($siteSettings['contact_intro']) ?></p>
                    <dl class="mt-10 space-y-5 text-sm">
                        <?php if ($siteSettings['contact_email'] !== ''): ?><div><dt class="contact-label"><?= e($siteSettings['contact_email_label']) ?></dt><dd><a class="contact-link" href="mailto:<?= e($siteSettings['contact_email']) ?>"><?= e($siteSettings['contact_email']) ?></a></dd></div><?php endif; ?>
                        <?php if ($siteSettings['contact_phone'] !== ''): ?><div><dt class="contact-label"><?= e($siteSettings['contact_phone_label']) ?></dt><dd><a class="contact-link" href="tel:<?= e(preg_replace('/[^+\d]/', '', $siteSettings['contact_phone'])) ?>"><?= e($siteSettings['contact_phone']) ?></a></dd></div><?php endif; ?>
                        <?php if ($siteSettings['location'] !== ''): ?><div><dt class="contact-label"><?= e($siteSettings['contact_location_label']) ?></dt><dd><?= e($siteSettings['location']) ?></dd></div><?php endif; ?>
                    </dl>
                </div>

                <div class="md:col-span-7 md:col-start-6">
                    <?php if ($flash): ?>
                        <div class="mb-8 border-l-4 <?= $flash['type'] === 'success' ? 'border-navy bg-paper' : 'border-red-700 bg-red-50' ?> p-5" role="alert" tabindex="-1" id="form-message">
                            <strong class="block"><?= $flash['type'] === 'success' ? 'Message received.' : 'Please check the form.' ?></strong>
                            <span class="mt-1 block text-sm"><?= e($flash['message']) ?></span>
                            <?php if (!empty($flash['errors'])): ?><ul class="mt-3 list-disc pl-5 text-sm"><?php foreach ($flash['errors'] as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <form id="inquiry-form" action="contact-submit.php" method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                        <div class="honeypot" aria-hidden="true"><label for="website">Website</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                        <div class="grid gap-x-6 md:grid-cols-2">
                            <div class="form-field"><label for="name"><?= e($siteSettings['contact_name_label']) ?> <span aria-hidden="true">*</span></label><input id="name" name="name" type="text" required maxlength="100" autocomplete="name" value="<?= e($old['name'] ?? '') ?>"></div>
                            <div class="form-field"><label for="email"><?= e($siteSettings['contact_form_email_label']) ?> <span aria-hidden="true">*</span></label><input id="email" name="email" type="email" required maxlength="190" autocomplete="email" value="<?= e($old['email'] ?? '') ?>"></div>
                            <div class="form-field"><label for="phone"><?= e($siteSettings['contact_form_phone_label']) ?></label><input id="phone" name="phone" type="tel" maxlength="30" autocomplete="tel" value="<?= e($old['phone'] ?? '') ?>"></div>
                            <div class="form-field"><label for="company"><?= e($siteSettings['contact_company_label']) ?></label><input id="company" name="company" type="text" maxlength="150" autocomplete="organization" value="<?= e($old['company'] ?? '') ?>"></div>
                            <div class="form-field md:col-span-2"><label for="service"><?= e($siteSettings['contact_service_label']) ?> <span aria-hidden="true">*</span></label><select id="service" name="service" required><option value=""><?= e($siteSettings['contact_service_placeholder']) ?></option><?php foreach ($contactServiceOptions as $option): ?><option value="<?= e($option) ?>" <?= ($old['service'] ?? '') === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
                            <div class="form-field md:col-span-2"><label for="message"><?= e($siteSettings['contact_message_label']) ?> <span aria-hidden="true">*</span></label><textarea id="message" name="message" rows="5" required minlength="20" maxlength="3000"><?= e($old['message'] ?? '') ?></textarea><span class="field-note"><?= e($siteSettings['contact_message_note']) ?></span></div>
                        </div>
                        <button class="button button-dark mt-4" type="submit"><?= e($siteSettings['contact_submit_label']) ?> <span aria-hidden="true">↗</span></button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
