<?php
require_once __DIR__ . '/../scripts/db_connect.php';
require_once __DIR__ . '/../scripts/auth.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
}
header("Location: add_user.php");
exit;
