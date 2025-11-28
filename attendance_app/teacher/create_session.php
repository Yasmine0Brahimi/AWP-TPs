<?php
require_once __DIR__ . '/../scripts/db_connect.php';
require_once __DIR__ . '/../scripts/auth.php';
require_role('teacher');

$course_id = (int)($_GET['course_id'] ?? 0);
$course = $pdo->prepare("SELECT * FROM courses WHERE id=? AND teacher_id=?");
$course->execute([$course_id, $_SESSION['user_id']]);
$c = $course->fetch();
if (!$c) die("Course not found or not assigned to you.");

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $date = $_POST['session_date'] ?? date('Y-m-d');
        $stmt = $pdo->prepare("INSERT INTO attendance_sessions (course_id, session_date, is_open) VALUES (?,?,1)");
        $stmt->execute([$course_id, $date]);

        // Create records for enrolled students (default absent)
        $sid = $pdo->lastInsertId();
        $en = $pdo->prepare("SELECT student_id FROM enrollments WHERE course_id=?");
        $en->execute([$course_id]);
        $ins = $pdo->prepare("INSERT IGNORE INTO attendance_records (session_id, student_id, status) VALUES (?,?, 'absent')");
        foreach ($en as $row) {
            $ins->execute([$sid, $row['student_id']]);
        }

        $success = "Session created successfully for " . htmlspecialchars($date);
        header("Location: take_attendance.php?session_id=" . $sid);
        exit;
    } catch (PDOException $e) {
        $error = "Error: " . htmlspecialchars($e->getMessage() ?? '');
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create Session</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include '../nav.php'; ?>
<div class="container">
    <h2>Create Session — <?= htmlspecialchars($c['title'] ?? '') ?></h2>
    <?php if (!empty($success)): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="error"><?= $error ?></div><?php endif; ?>

    <form method="post" class="card">
        <label>Session Date</label>
        <input type="date" name="session_date" value="<?= date('Y-m-d') ?>" required>
        <p class="muted small">All enrolled students will be marked absent by default until attendance is taken.</p>
        <button class="btn" type="submit">➕ Create Session</button>
    </form>
</div>
</body>
</html>
