<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';
admin_require_auth();
admin_flash('success', 'Testimonials are now managed inside each project.');
admin_redirect('projects.php');
