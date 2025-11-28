<?php
require_once __DIR__ . '/../scripts/auth.php';
require_role('admin');
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin home</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include '../nav.php'; ?>
<div class="container">
    <h2>Admin Dashboard</h2>
    <div class="grid">
        <a class="card link-card" href="statistics.php">📊 Statistics</a>
        <a class="card link-card" href="add_user.php">👥 Manage Users</a>
       
    </div>
</div>
</body>
</html>
