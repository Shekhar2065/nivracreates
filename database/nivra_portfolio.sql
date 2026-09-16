-- Nivra portfolio database
-- The project and testimonial rows below are DEMO CONTENT only.

CREATE DATABASE IF NOT EXISTS `nivra_portfolio`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `nivra_portfolio`;

CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(170) NOT NULL,
    `client_name` VARCHAR(150) NOT NULL,
    `industry` VARCHAR(120) NOT NULL,
    `services` VARCHAR(255) NOT NULL,
    `short_description` TEXT NOT NULL,
    `image` VARCHAR(255) NOT NULL,
    `logo_image` VARCHAR(255) NULL,
    `project_year` VARCHAR(10) NOT NULL,
    `verified_result` TEXT NULL,
    `project_url` VARCHAR(500) NULL,
    `project_cta` VARCHAR(80) NULL,
    `is_published` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `projects_slug_unique` (`slug`),
    KEY `projects_display_order_index` (`display_order`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `project_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) NOT NULL DEFAULT '',
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `project_images_project_order_index` (`project_id`, `display_order`, `id`),
    CONSTRAINT `project_images_project_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `testimonials` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_name` VARCHAR(150) NOT NULL,
    `company_name` VARCHAR(150) NOT NULL,
    `client_role` VARCHAR(120) NOT NULL,
    `testimonial` TEXT NOT NULL,
    `client_image` VARCHAR(255) NULL,
    `project_id` INT UNSIGNED NULL,
    `is_published` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `testimonials_project_index` (`project_id`),
    KEY `testimonials_display_order_index` (`display_order`),
    CONSTRAINT `testimonials_project_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `site_settings` (
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT NOT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(80) NOT NULL,
    `email` VARCHAR(190) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `recovery_phone` VARCHAR(20) NOT NULL DEFAULT '+9779845895222',
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `last_login_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `admin_users_username_unique` (`username`),
    UNIQUE KEY `admin_users_email_unique` (`email`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `password_reset_requests` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `request_key_hash` CHAR(64) NOT NULL,
    `reset_token_hash` CHAR(64) NULL,
    `ip_hash` CHAR(64) NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `verified_at` DATETIME NULL,
    `used_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `password_resets_user_created_index` (`user_id`, `created_at`),
    KEY `password_resets_expiry_index` (`expires_at`),
    CONSTRAINT `password_resets_user_fk` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
    ('site_name', 'Nivra'),
    ('seo_title', 'Nivra'),
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
    ('contact_email', ''),
    ('contact_phone', '+977 9845895222'),
    ('location', 'Nepal'),
    ('instagram_url', 'https://www.instagram.com/nivra.creates/'),
    ('tiktok_url', 'https://www.tiktok.com/@nivra.creates'),
    ('whatsapp_number', '9779845895222')
ON DUPLICATE KEY UPDATE `setting_key` = VALUES(`setting_key`);

CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(190) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `company` VARCHAR(150) NULL,
    `service` VARCHAR(80) NOT NULL,
    `message` TEXT NOT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'new',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `contact_messages_status_created_index` (`status`, `created_at`),
    KEY `contact_messages_email_index` (`email`)
) ENGINE=InnoDB;

INSERT INTO `projects`
    (`title`, `slug`, `client_name`, `industry`, `services`, `short_description`, `image`, `logo_image`, `project_year`, `verified_result`, `project_url`, `project_cta`, `display_order`)
VALUES
    ('Salt & Pepper Cafe', 'cafe-campaign', 'Salt & Pepper Cafe', 'Hospitality', 'Campaign Strategy · Photography · Social Content', 'A focused digital campaign designed to make the cafe’s food, atmosphere and personality instantly recognisable across social media.', 'assets/images/cafe-project.png', 'assets/images/salt-and-pepper-cafe-logo-640.png', '', NULL, NULL, NULL, 1),
    ('Himalayan Arts', 'himalayan-arts', 'Himalayan Arts', 'Art Gallery', 'Digital showcase · Social content', 'A digital presentation of Himalayan artistic heritage, bringing traditional landscapes and gallery collections to an online audience.', 'assets/images/himalayan-arts-temple.png', 'assets/images/himalayan-arts-mark.svg', 'Project', NULL, NULL, NULL, 2)
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `client_name` = VALUES(`client_name`),
    `industry` = VALUES(`industry`),
    `services` = VALUES(`services`),
    `short_description` = VALUES(`short_description`),
    `image` = VALUES(`image`),
    `logo_image` = VALUES(`logo_image`),
    `project_year` = VALUES(`project_year`),
    `verified_result` = VALUES(`verified_result`),
    `project_url` = VALUES(`project_url`),
    `project_cta` = VALUES(`project_cta`),
    `is_published` = 1,
    `display_order` = VALUES(`display_order`);

-- Retire the superseded demo card when this seed is applied to an older database.
UPDATE `projects`
SET `is_published` = 0
WHERE `slug` = 'local-fashion-brand';

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

INSERT INTO `testimonials`
    (`client_name`, `company_name`, `client_role`, `testimonial`, `client_image`, `project_id`, `display_order`)
SELECT
    seeded.client_name, seeded.company_name, seeded.client_role, seeded.testimonial,
    NULL, projects.id, seeded.display_order
FROM (
    SELECT 'Salt & Pepper Cafe' AS client_name, 'Salt & Pepper Cafe' AS company_name, 'Campaign client' AS client_role,
        'Nivra understood the personality of our cafe and translated it into content that felt consistent, natural and recognisable.' AS testimonial,
        'cafe-campaign' AS project_slug, 1 AS display_order
    UNION ALL
    SELECT 'Himalayan Arts', 'Himalayan Arts', 'Gallery client',
        'Nivra gave our collection a digital presence that feels considered, accessible and true to the spirit of Himalayan art.',
        'himalayan-arts', 2
) AS seeded
JOIN `projects` ON projects.slug = seeded.project_slug
WHERE NOT EXISTS (
    SELECT 1 FROM `testimonials` existing
    WHERE existing.project_id = projects.id AND existing.client_name = seeded.client_name
);
