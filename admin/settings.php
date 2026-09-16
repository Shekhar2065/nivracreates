<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$db = admin_db();
admin_require_auth();

$fields = [
    'Brand & search' => [
        'site_name' => ['Site name', 'text'],
        'seo_title' => ['Browser / search title', 'text'],
        'seo_description' => ['Search description', 'textarea'],
    ],
    'Hero content' => [
        'hero_kicker' => ['Hero eyebrow', 'text'],
        'hero_headline_lead' => ['Opening headline — first part', 'text'],
        'hero_headline_tail' => ['Opening headline — second part', 'text'],
        'hero_support' => ['Hero supporting line', 'textarea'],
        'hero_final_lead' => ['Main statement — first word', 'text'],
        'hero_final_text' => ['Main statement — remaining text', 'textarea'],
        'hero_cta_label' => ['Contact button label', 'text'],
        'hero_image_1_alt' => ['Hero image 1 description', 'text'],
        'hero_image_2_alt' => ['Hero image 2 description', 'text'],
        'hero_image_3_alt' => ['Hero image 3 description', 'text'],
    ],
    'Homepage copy' => [
        'about_intro' => ['About introduction', 'textarea'],
        'founder_name' => ['Founder name', 'text'],
        'founder_role' => ['Founder role', 'text'],
        'founder_image_alt' => ['Founder photo description', 'text'],
        'founder_quote' => ['Founder quote', 'textarea'],
        'work_principles_heading' => ['How We Work heading', 'text'],
        'work_principle_1' => ['How We Work item 1', 'text'],
        'work_principle_2' => ['How We Work item 2', 'text'],
        'work_principle_3' => ['How We Work item 3', 'text'],
        'work_principle_4' => ['How We Work item 4', 'text'],
        'work_principle_5' => ['How We Work item 5', 'text'],
    ],
    'Services' => [
        'services_heading' => ['Section heading', 'text'],
        'service_1_title' => ['Service 1 title', 'text'],
        'service_1_description' => ['Service 1 description', 'textarea'],
        'service_1_items' => ['Service 1 items — one per line', 'textarea'],
        'service_2_title' => ['Service 2 title', 'text'],
        'service_2_description' => ['Service 2 description', 'textarea'],
        'service_2_items' => ['Service 2 items — one per line', 'textarea'],
        'service_3_title' => ['Service 3 title', 'text'],
        'service_3_description' => ['Service 3 description', 'textarea'],
        'service_3_items' => ['Service 3 items — one per line', 'textarea'],
    ],
    'Contact section' => [
        'contact_section_label' => ['Section label', 'text'],
        'contact_heading_lead' => ['Heading — first line', 'text'],
        'contact_heading_accent' => ['Heading — accent line', 'text'],
        'contact_intro' => ['Introduction', 'textarea'],
    ],
    'Contact & social' => [
        'contact_email' => ['Contact email', 'email'],
        'contact_phone' => ['Contact phone', 'text'],
        'location' => ['Location', 'text'],
        'instagram_url' => ['Instagram URL', 'url'],
        'tiktok_url' => ['TikTok URL', 'url'],
        'whatsapp_number' => ['WhatsApp number', 'text'],
    ],
];

$heroImages = [
    'hero_image_1' => 'Hero image 1 — left',
    'hero_image_2' => 'Hero image 2 — centre',
    'hero_image_3' => 'Hero image 3 — right',
];

$founderImages = [
    'founder_image' => 'Founder photo',
];

$editableImages = $heroImages + $founderImages;

$fieldTabs = [
    'Brand & search' => 'brand',
    'Hero content' => 'hero',
    'Homepage copy' => 'about',
    'Services' => 'services',
    'Contact section' => 'contact',
    'Contact & social' => 'contact',
];

$tabLabels = [
    'brand' => 'Brand & SEO',
    'hero' => 'Hero',
    'about' => 'About',
    'services' => 'Services',
    'contact' => 'Contact',
];

$fieldDescriptions = [
    'Brand & search' => 'Control the website name and how the site appears in search results.',
    'Hero content' => 'Edit the first words visitors see on the homepage.',
    'Homepage copy' => 'Manage the About section, founder details, and How We Work list.',
    'Services' => 'Edit the service names, descriptions, and line-by-line capabilities.',
    'Contact section' => 'Control the section label, main heading, and introductory message.',
    'Contact & social' => 'Update the contact details and social links shown across the website.',
];

$defaults = [
    'site_name' => 'Nivra',
    'seo_title' => 'Nivra',
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
    'founder_name' => '[Founder Name]',
    'founder_role' => 'Founder & Creative Director',
    'founder_image' => '',
    'founder_image_alt' => 'Portrait of the founder of Nivra',
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
    'contact_email' => '',
    'contact_phone' => '+977 9845895222',
    'location' => 'Nepal',
    'instagram_url' => 'https://www.instagram.com/nivra.creates/',
    'tiktok_url' => 'https://www.tiktok.com/@nivra.creates',
    'whatsapp_number' => '9779845895222',
    'services_heading' => 'One connected system, from the first question to the next move.',
    'service_1_title' => 'Strategy',
    'service_1_description' => 'Find the sharpest position before spending time, attention or budget.',
    'service_1_items' => "Brand positioning\nMarketing audits\nCampaign planning\nAudience research",
    'service_2_title' => 'Creative',
    'service_2_description' => 'Turn clear thinking into work people recognise, remember and share.',
    'service_2_items' => "Content creation\nGraphic design\nCampaign concepts\nPhotography & video direction",
    'service_3_title' => 'Growth',
    'service_3_description' => 'Build useful feedback loops and make each campaign smarter than the last.',
    'service_3_items' => "Social media management\nPaid advertising\nCampaign optimisation\nPerformance reporting",
];

$values = $db->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll(PDO::FETCH_KEY_PAIR);
$allKeys = [];
foreach ($fields as $group) {
    $allKeys = array_merge($allKeys, array_keys($group));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();
    $submitted = [];
    $returnTab = (string) ($_POST['settings_return_tab'] ?? 'brand');
    if (!array_key_exists($returnTab, $tabLabels)) {
        $returnTab = 'brand';
    }

    try {
        foreach ($allKeys as $key) {
            $value = trim((string) ($_POST[$key] ?? ''));
            if (in_array($key, ['instagram_url', 'tiktok_url'], true) && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                throw new RuntimeException('Social links must be complete URLs beginning with https://.');
            }
            $submitted[$key] = $value;
        }

        foreach ($editableImages as $key => $label) {
            $existing = (string) ($values[$key] ?? $defaults[$key]);
            $submitted[$key] = (string) admin_upload_image($key . '_upload', $existing);
        }

        $db->beginTransaction();
        $stmt = $db->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($submitted as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        $db->commit();
    } catch (Throwable $exception) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        admin_flash('error', $exception->getMessage());
        admin_redirect('settings.php#settings-' . $returnTab);
    }

    admin_flash('success', 'Website content saved.');
    admin_redirect('settings.php#settings-' . $returnTab);
}

admin_header('Site content', 'settings');
?>
<style>
.settings-intro{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:18px}.settings-intro p{max-width:680px;margin:0;color:var(--muted)}
.settings-tabs{position:sticky;top:0;z-index:20;display:flex;gap:7px;overflow-x:auto;margin:0 -8px 18px;padding:10px 8px;background:rgba(243,245,237,.96);backdrop-filter:blur(10px)}
.settings-tab{flex:0 0 auto;border:1px solid var(--line);border-radius:999px;background:white;color:var(--ink);padding:10px 16px;font:700 13px/1 inherit;cursor:pointer}.settings-tab[aria-selected="true"]{border-color:var(--ink);background:var(--ink);color:white;box-shadow:inset 0 -3px var(--acid)}
.settings-panel[hidden]{display:none}.settings-panel{margin-top:14px}.settings-panel .panel-head{align-items:flex-start}.settings-panel .panel-head p{max-width:620px;margin:4px 0 0;color:var(--muted);font-size:13px}
.settings-image-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.settings-image-card{border:1px solid var(--line);border-radius:12px;background:var(--paper);padding:12px}.settings-image-preview{display:block;width:100%;height:170px;margin:0 0 12px;border-radius:8px;background:#dfe6e1;object-fit:cover}.settings-image-preview.is-round{width:150px;height:150px;border-radius:50%}.settings-image-placeholder{display:grid;width:150px;height:150px;place-items:center;margin-bottom:12px;border:1px dashed #aebdb8;border-radius:50%;color:var(--muted);background:#eef1ec;font-size:12px;text-align:center}
.settings-savebar{position:sticky;bottom:14px;z-index:25;display:flex;align-items:center;justify-content:space-between;gap:15px;margin-top:22px;border:1px solid var(--line);border-radius:12px;background:rgba(255,255,255,.96);box-shadow:0 14px 40px rgba(6,47,75,.14);padding:12px 14px;backdrop-filter:blur(12px)}.settings-savebar .actions{margin:0}.settings-save-status{color:var(--muted);font-size:13px}.settings-save-status.is-dirty{color:#735900;font-weight:700}
@media(max-width:850px){.settings-image-grid{grid-template-columns:1fr 1fr}}@media(max-width:600px){.settings-intro{display:block}.settings-intro .button{margin-top:12px}.settings-image-grid{grid-template-columns:1fr}.settings-savebar{bottom:8px;align-items:flex-start;flex-direction:column}.settings-savebar .actions{width:100%}.settings-savebar .button{flex:1}.settings-tabs{top:0}}
</style>

<div class="settings-intro">
    <p>Choose a section, make your changes, then save. Existing website content stays in place until you press the save button.</p>
    <a class="button secondary" href="../index.php" target="_blank">Open website preview ↗</a>
</div>

<nav class="settings-tabs" aria-label="Site content sections" data-settings-tabs>
    <?php foreach ($tabLabels as $tabKey => $tabLabel): ?>
        <button class="settings-tab" type="button" data-settings-tab="<?= admin_e($tabKey) ?>" aria-selected="<?= $tabKey === 'brand' ? 'true' : 'false' ?>"><?= admin_e($tabLabel) ?></button>
    <?php endforeach; ?>
</nav>

<form method="post" enctype="multipart/form-data" data-settings-form>
    <input type="hidden" name="csrf_token" value="<?= admin_csrf() ?>">
    <input type="hidden" name="settings_return_tab" value="brand" data-settings-return-tab>

    <?php foreach ($fields as $title => $group): ?>
        <?php $tabKey = $fieldTabs[$title]; ?>
        <section class="panel settings-panel" data-settings-panel="<?= admin_e($tabKey) ?>" <?= $tabKey === 'brand' ? '' : 'hidden' ?>>
            <div class="panel-head"><div><h2><?= admin_e($title) ?></h2><p><?= admin_e($fieldDescriptions[$title] ?? '') ?></p></div></div>
            <div class="grid-2">
                <?php foreach ($group as $key => [$label, $type]): ?>
                    <?php $value = (string) ($values[$key] ?? $defaults[$key] ?? ''); ?>
                    <div class="field <?= $type === 'textarea' ? 'full' : '' ?>">
                        <label for="<?= admin_e($key) ?>"><?= admin_e($label) ?></label>
                        <?php if ($type === 'textarea'): ?>
                            <textarea id="<?= admin_e($key) ?>" name="<?= admin_e($key) ?>"><?= admin_e($value) ?></textarea>
                        <?php else: ?>
                            <input id="<?= admin_e($key) ?>" name="<?= admin_e($key) ?>" type="<?= admin_e($type) ?>" value="<?= admin_e($value) ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <section class="panel settings-panel" data-settings-panel="hero" hidden>
        <div class="panel-head"><div><h2>Hero images</h2><p>Replace any homepage hero image. The layout and animation stay unchanged.</p></div></div>
        <div class="settings-image-grid">
            <?php foreach ($heroImages as $key => $label): ?>
                <?php $imagePath = (string) ($values[$key] ?? $defaults[$key]); ?>
                <div class="field settings-image-card">
                    <label for="<?= admin_e($key) ?>_upload"><?= admin_e($label) ?></label>
                    <img class="settings-image-preview" id="preview_<?= admin_e($key) ?>" src="../<?= admin_e($imagePath) ?>" alt="">
                    <input id="<?= admin_e($key) ?>_upload" name="<?= admin_e($key) ?>_upload" type="file" accept="image/jpeg,image/png,image/webp" data-settings-image-input data-preview-id="preview_<?= admin_e($key) ?>">
                    <small>JPG, PNG or WebP · max 5 MB. Leave empty to keep the current image.</small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel settings-panel" data-settings-panel="about" hidden>
        <div class="panel-head"><div><h2>Founder photo</h2><p>Upload a portrait; the website automatically crops it into the circular frame.</p></div></div>
        <div class="settings-image-grid">
            <?php foreach ($founderImages as $key => $label): ?>
                <?php $imagePath = (string) ($values[$key] ?? $defaults[$key]); ?>
                <div class="field settings-image-card">
                    <label for="<?= admin_e($key) ?>_upload"><?= admin_e($label) ?></label>
                    <img class="settings-image-preview is-round" id="preview_<?= admin_e($key) ?>" <?= $imagePath !== '' ? 'src="../' . admin_e($imagePath) . '"' : 'hidden' ?> alt="">
                    <div class="settings-image-placeholder" data-placeholder-for="preview_<?= admin_e($key) ?>" <?= $imagePath !== '' ? 'hidden' : '' ?>>No photo uploaded</div>
                    <input id="<?= admin_e($key) ?>_upload" name="<?= admin_e($key) ?>_upload" type="file" accept="image/jpeg,image/png,image/webp" data-settings-image-input data-preview-id="preview_<?= admin_e($key) ?>">
                    <small>JPG, PNG or WebP · max 5 MB. The website crops it to a circle.</small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="settings-savebar">
        <span class="settings-save-status" data-settings-save-status aria-live="polite">All changes saved</span>
        <div class="actions">
            <button class="button acid" type="submit">Save changes</button>
            <a class="button secondary" href="../index.php" target="_blank">Preview ↗</a>
        </div>
    </div>
</form>
<?php admin_footer(); ?>
