<?php
require_once __DIR__ . '/../scripts/db_connect.php';
require_once __DIR__ . '/../scripts/auth.php';
require_role('admin');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $full_name  = trim($_POST['full_name'] ?? '');
    $password   = $_POST['password'] ?? '';
    $role       = $_POST['role'] ?? '';
    $course_id  = (int)($_POST['course_id'] ?? 0);

    if ($username === '' || $full_name === '' || $password === '' || !in_array($role, ['student','teacher'])) {
        $error = "Please fill all fields correctly.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, full_name, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $full_name, password_hash($password, PASSWORD_DEFAULT), $role]);
            $newUserId = $pdo->lastInsertId();

            // If it's a student and a course was selected, enroll them
            if ($role === 'student' && $course_id > 0) {
                $enroll = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
                $enroll->execute([$newUserId, $course_id]);
            }

            $success = "User '" . htmlspecialchars($full_name ?? '') . "' added successfully.";
        } catch (PDOException $e) {
            $error = "Error: " . htmlspecialchars($e->getMessage() ?? '');
        }
    }
}

$users   = $pdo->query("SELECT id, username, full_name, role FROM users ORDER BY role, full_name")->fetchAll();
$courses = $pdo->query("SELECT id, title FROM courses ORDER BY title")->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include '../nav.php'; ?>
<div class="container">
    <h2>Manage Users</h2>
    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

    <form method="post" class="card">
        <label>Username</label>
        <input type="text" name="username" required>
        <label>Full Name</label>
        <input type="text" name="full_name" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <label>Role</label>
        <select name="role" required>
            <option value="">Select role</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
        </select>
        <label>Assign to Course (students only)</label>
        <select name="course_id">
            <option value="0">None</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title'] ?? '') ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Add User</button>
    </form>

    <h3>Existing Users</h3>
    <table class="table">
        <tr><th>Username</th><th>Full Name</th><th>Role</th><th>Action</th></tr>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['username'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['full_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['role'] ?? '') ?></td>
            <td><a class="btn-small" href="delete_user.php?id=<?= $u['id'] ?>">Delete</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
