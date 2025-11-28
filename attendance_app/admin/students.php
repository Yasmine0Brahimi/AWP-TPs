<?php
require_once __DIR__ . '/../scripts/db_connect.php';
require_once __DIR__ . '/../scripts/auth.php';
require_role('admin');

$msg = '';
// Add student
if (isset($_POST['add'])) {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if ($u && $p) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?,?, 'student')");
        $stmt->execute([$u, password_hash($p, PASSWORD_DEFAULT)]);
        $msg = "Student added.";
    } else { $msg = "Provide username and password."; }
}

// Delete student
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM users WHERE id=? AND role='student'")->execute([$id]);
    header("Location: students.php"); exit;
}

// Import CSV (Progres-like: first column username)
if (isset($_POST['import']) && isset($_FILES['csv']) && is_uploaded_file($_FILES['csv']['tmp_name'])) {
    $fh = fopen($_FILES['csv']['tmp_name'], 'r');
    $count = 0;
    while (($row = fgetcsv($fh)) !== false) {
        $username = trim($row[0] ?? '');
        if ($username !== '') {
            $pdo->prepare("INSERT IGNORE INTO users (username, password, role) VALUES (?,?, 'student')")
                ->execute([$username, password_hash("1234", PASSWORD_DEFAULT)]);
            $count++;
        }
    }
    fclose($fh);
    $msg = "Imported $count students.";
}

// Export CSV
if (isset($_POST['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=students.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['username']);
    foreach ($pdo->query("SELECT username FROM users WHERE role='student' ORDER BY username") as $row) {
        fputcsv($out, [$row['username']]);
    }
    fclose($out);
    exit;
}

$students = $pdo->query("SELECT id, username FROM users WHERE role='student' ORDER BY username")->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Students</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<?php include '../nav.php'; ?>
<div class="container">
    <h2>Student management</h2>
    <?php if ($msg): ?><div class="info"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <h3>Add</h3>
    <form method="post" class="inline">
        <input name="username" placeholder="username" required>
        <input name="password" type="password" placeholder="password" required>
        <button name="add" value="1">Add</button>
    </form>

    <h3>Import/Export</h3>
    <form method="post" enctype="multipart/form-data" class="inline">
        <input type="file" name="csv" accept=".csv">
        <button name="import" value="1">Import CSV</button>
    </form>
    <form method="post" class="inline">
        <button name="export" value="1">Export CSV</button>
    </form>

    <h3>List</h3>
    <table class="table">
        <tr><th>Username</th><th>Action</th></tr>
        <?php foreach ($students as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['username']) ?></td>
            <td><a href="students.php?delete=<?= $s['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
