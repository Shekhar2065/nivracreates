<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

function admin_header(string $title, string $active = ''): void
{
    $user = admin_require_auth();
    $flash = admin_take_flash();
    $unread = (int) admin_db()->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
    $items = [
        'dashboard' => ['index.php', 'Dashboard'], 'settings' => ['settings.php', 'Site content'],
        'projects' => ['projects.php', 'Projects'],
        'messages' => ['messages.php', 'Inbox' . ($unread ? ' · ' . $unread : '')], 'profile' => ['profile.php', 'Account'],
    ];
    ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= admin_e($title) ?> · Nivra Admin</title><link rel="stylesheet" href="assets/admin.css"></head>
<body><div class="admin-shell"><aside class="sidebar"><a class="brand" href="index.php"><span class="brand-mark">N</span><span>Nivra</span></a>
<nav aria-label="Admin navigation"><?php foreach ($items as $key => [$href, $label]): ?><a href="<?= $href ?>" class="<?= $active === $key ? 'active' : '' ?>"><?= admin_e($label) ?></a><?php endforeach; ?></nav>
<div class="sidebar-foot"><a href="../index.php" target="_blank">View website ↗</a><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= admin_csrf() ?>"><button type="submit">Sign out</button></form></div></aside>
<main class="admin-main"><header class="topbar"><div><p class="eyebrow">Nivra control room</p><h1><?= admin_e($title) ?></h1></div><div class="user-chip"><span><?= strtoupper(admin_e(substr((string) $user['username'], 0, 1))) ?></span><?= admin_e((string) $user['username']) ?></div></header>
<?php if ($flash): ?><div class="notice <?= admin_e($flash['type']) ?>" role="status"><?= admin_e($flash['message']) ?></div><?php endif; ?>
<?php
}

function admin_footer(): void
{
    $scriptVersion = (int) filemtime(__DIR__ . '/assets/admin.js');
    echo '</main></div><script src="assets/admin.js?v=' . $scriptVersion . '"></script></body></html>';
}
