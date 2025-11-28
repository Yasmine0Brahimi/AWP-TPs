<?php
require_once __DIR__ . '/../scripts/db_connect.php';
require_once __DIR__ . '/../scripts/auth.php';
require_role('teacher');

$course_id = (int)($_GET['course_id'] ?? 0);
$course = $pdo->prepare("SELECT * FROM courses WHERE id=?");
$course->execute([$course_id]);
$c = $course->fetch();
if (!$c) die("Course not found.");

$summary = $pdo->prepare("
    SELECT s.session_date,
           SUM(ar.status='present') AS present_count,
           SUM(ar.status='absent')  AS absent_count,
           SUM(ar.status='late')    AS late_count
    FROM attendance_sessions s
    LEFT JOIN attendance_records ar ON ar.session_id=s.id
    WHERE s.course_id=?
    GROUP BY s.id
    ORDER BY s.session_date DESC
");
$summary->execute([$course_id]);
$data = $summary->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Summary</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include '../nav.php'; ?>
<div class="container">
    <h2>Summary — <?= htmlspecialchars($c['title']) ?></h2>
    <table class="table">
        <tr><th>Date</th><th>Present</th><th>Absent</th><th>Late</th></tr>
        <?php if (!empty($data)): foreach ($data as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['session_date']) ?></td>
            <td><?= (int)$row['present_count'] ?></td>
            <td><?= (int)$row['absent_count'] ?></td>
            <td><?= (int)$row['late_count'] ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="4">No sessions yet.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
