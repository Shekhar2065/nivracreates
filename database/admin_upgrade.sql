USE `nivra_portfolio`;

ALTER TABLE `projects`
    ADD COLUMN IF NOT EXISTS `logo_image` VARCHAR(255) NULL AFTER `image`,
    ADD COLUMN IF NOT EXISTS `project_url` VARCHAR(500) NULL AFTER `verified_result`,
    ADD COLUMN IF NOT EXISTS `project_cta` VARCHAR(80) NULL AFTER `project_url`,
    ADD COLUMN IF NOT EXISTS `is_published` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER `project_cta`;

ALTER TABLE `testimonials`
    ADD COLUMN IF NOT EXISTS `is_published` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER `project_id`;

CREATE TABLE IF NOT EXISTS `project_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) NOT NULL DEFAULT '',
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `project_images_project_order_index` (`project_id`, `display_order`, `id`),
    CONSTRAINT `project_images_project_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `project_images` (`project_id`, `image_path`, `alt_text`, `display_order`)
SELECT projects.id, seeded.image_path, seeded.alt_text, seeded.display_order
FROM `projects`
JOIN (
    SELECT 'cafe-campaign' AS project_slug, 'assets/images/cafe-project.png' AS image_path, 'Coffee and pastry campaign photograph for Salt & Pepper Cafe' AS alt_text, 1 AS display_order
    UNION ALL SELECT 'cafe-campaign', 'assets/images/salt-pepper-food-platter.png', 'Salt & Pepper Cafe platter photographed for its social campaign', 2
    UNION ALL SELECT 'cafe-campaign', 'assets/images/salt-pepper-savoury-sweet.png', 'Savoury ribs and dessert from the Salt & Pepper Cafe menu', 3
    UNION ALL SELECT 'cafe-campaign', 'assets/images/salt-pepper-cafe-exterior.png', 'Outdoor seating and greenery at Salt & Pepper Cafe', 4
    UNION ALL SELECT 'cafe-campaign', 'assets/images/salt-pepper-table.png', 'Wooden table and chairs at Salt & Pepper Cafe', 5
    UNION ALL SELECT 'himalayan-arts', 'assets/images/himalayan-arts-temple.png', 'Framed Himalayan Arts painting of a golden Kathmandu square', 1
    UNION ALL SELECT 'himalayan-arts', 'assets/images/himalayan-arts-yaks.png', 'Framed Himalayan Arts painting of a mountain caravan', 2
    UNION ALL SELECT 'himalayan-arts', 'assets/images/himalayan-arts-gallery-wall.png', 'Curated wall of framed Himalayan mountain paintings', 3
) AS seeded ON seeded.project_slug = projects.slug
WHERE NOT EXISTS (
    SELECT 1 FROM `project_images` existing
    WHERE existing.project_id = projects.id AND existing.image_path = seeded.image_path
);

CREATE TABLE IF NOT EXISTS `site_settings` (
    `setting_key` VARCHAR(100) NOT NULL PRIMARY KEY,
    `setting_value` TEXT NOT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(80) NOT NULL UNIQUE,
    `email` VARCHAR(190) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `recovery_phone` VARCHAR(20) NOT NULL DEFAULT '+9779845895222',
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `last_login_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `password_reset_requests` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `request_key_hash` CHAR(64) NOT NULL,
    `reset_token_hash` CHAR(64) NULL,
    `ip_hash` CHAR(64) NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `verified_at` DATETIME NULL,
    `used_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `password_resets_user_created_index` (`user_id`, `created_at`),
    KEY `password_resets_expiry_index` (`expires_at`),
    CONSTRAINT `password_resets_user_fk` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
    ('site_name', 'Nivra'), ('seo_title', 'Nivra'),
    ('seo_description', 'Nivra combines strategy, content and performance marketing to help ambitious businesses become impossible to ignore.'),
    ('hero_kicker', 'Independent digital marketing agency'),
    ('hero_headline_lead', 'Ideas'),
    ('hero_headline_tail', 'that move.'),
    ('hero_support', 'A creative growth studio building brands through strategy, content and performance.'),
    ('hero_final_lead', 'We'),
    ('hero_final_text', 'shape bold ideas into brands people notice, trust, and remember.'),
    ('hero_cta_label', 'Work with us'),
    ('hero_image_1', 'assets/images/himalayan-arts-bts-camera.png'),
    ('hero_image_1_alt', 'Behind-the-scenes camera photographing artwork for Himalayan Arts'),
    ('hero_image_2', 'assets/images/himalayan-arts-temple.png'),
    ('hero_image_2_alt', 'Himalayan Arts campaign featuring a framed Kathmandu painting'),
    ('hero_image_3', 'assets/images/work-03.jpg'),
    ('hero_image_3_alt', 'Service brand strategy campaign created by Nivra'),
    ('founder_image', ''),
    ('founder_image_alt', 'Portrait of the founder of Nivra'),
    ('work_principles_heading', 'How we work'),
    ('work_principle_1', 'Strategy before execution'),
    ('work_principle_2', 'Creative with a clear purpose'),
    ('work_principle_3', 'Honest communication'),
    ('work_principle_4', 'Measurable improvement'),
    ('work_principle_5', 'No unnecessary agency layers'),
    ('contact_section_label', '05 — Contact'),
    ('contact_heading_lead', 'Let’s begin'),
    ('contact_heading_accent', 'the project.'),
    ('contact_intro', 'Tell us where the business is now, and where you want it to go. We’ll respond with the most useful next step.'),
    ('contact_email_label', 'Email'),
    ('contact_phone_label', 'Phone'),
    ('contact_location_label', 'Location'),
    ('contact_name_label', 'Name'),
    ('contact_form_email_label', 'Email'),
    ('contact_form_phone_label', 'Phone'),
    ('contact_company_label', 'Business or company'),
    ('contact_service_label', 'Required service'),
    ('contact_service_placeholder', 'Choose a service'),
    ('contact_service_extra_options', 'Integrated campaign\nNot sure yet'),
    ('contact_message_label', 'Project description'),
    ('contact_message_note', 'A few lines about your goal, timing and what you need help with.'),
    ('contact_submit_label', 'Send inquiry'),
    ('contact_email', ''), ('contact_phone', '+977 9845895222'), ('location', 'Nepal'),
    ('instagram_url', 'https://www.instagram.com/nivra.creates/'), ('tiktok_url', 'https://www.tiktok.com/@nivra.creates'),
    ('whatsapp_number', '9779845895222')
ON DUPLICATE KEY UPDATE `setting_key` = VALUES(`setting_key`);
