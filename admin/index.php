<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';
$db = admin_db();
$counts = [
    'projects' => (int) $db->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
    'images' => (int) $db->query('SELECT COUNT(*) FROM project_images')->fetchColumn(),
    'new' => (int) $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn(),
    'messages' => (int) $db->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn(),
];
$recent = $db->query('SELECT id, name, email, service, status, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 6')->fetchAll();
admin_header('Dashboard', 'dashboard');
?><section class="cards"><article class="card"><div class="number"><?= $counts['new'] ?></div><p>New inquiries</p></article><article class="card"><div class="number"><?= $counts['messages'] ?></div><p>Total messages</p></article><article class="card"><div class="number"><?= $counts['projects'] ?></div><p>Projects</p></article><article class="card"><div class="number"><?= $counts['images'] ?></div><p>Project images</p></article></section>
<section class="panel"><div class="panel-head"><h2>Latest inquiries</h2><a class="button secondary small" href="messages.php">Open inbox</a></div><?php if (!$recent): ?><div class="empty">Messages from the website will appear here.</div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Contact</th><th>Service</th><th>Received</th><th>Status</th></tr></thead><tbody><?php foreach ($recent as $message): ?><tr><td><a href="messages.php?id=<?= (int) $message['id'] ?>"><strong><?= admin_e((string) $message['name']) ?></strong></a><br><span class="muted"><?= admin_e((string) $message['email']) ?></span></td><td><?= admin_e((string) $message['service']) ?></td><td><?= admin_e(date('M j, Y · g:i a', strtotime((string) $message['created_at']))) ?></td><td><span class="status <?= admin_e((string) $message['status']) ?>"><?= admin_e((string) $message['status']) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php admin_footer(); ?>
