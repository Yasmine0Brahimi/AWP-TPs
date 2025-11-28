<?php
require_once __DIR__ . '/../scripts/db_connect.php';
require_once __DIR__ . '/../scripts/auth.php';
require_role('teacher');

// Only show courses assigned to this teacher
$courses = $pdo->prepare("SELECT * FROM courses WHERE teacher_id=? ORDER BY title");
$courses->execute([$_SESSION['user_id']]);
$courses = $courses->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teacher Home</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include '../nav.php'; ?>
<div class="container">
    <h2>My Courses & Sessions</h2>
    <?php if (!empty($courses)): ?>
    <ul class="list">
        <?php foreach ($courses as $c): ?>
            <li class="list-item">
                <strong><?= htmlspecialchars($c['title'] ?? '') ?></strong>
                <span class="muted">(<?= htmlspecialchars($c['code'] ?? '') ?>)</span>
                <div class="row">
                    <a class="btn btn-outline" href="create_session.php?course_id=<?= $c['id'] ?>">➕ Create Session</a>
                    <a class="btn btn-outline" href="summary.php?course_id=<?= $c['id'] ?>">📊 Summary</a>
                </div>
                <div class="small muted">Sessions:</div>
                <?php
                $s = $pdo->prepare("SELECT id, session_date, is_open FROM attendance_sessions WHERE course_id=? ORDER BY session_date DESC");
                $s->execute([$c['id']]);
                $sessions = $s->fetchAll();
                ?>
                <?php if (!empty($sessions)): ?>
                    <div class="stack">
                        <?php foreach ($sessions as $row): ?>
                            <div class="chip">
                                <?= htmlspecialchars($row['session_date'] ?? '') ?> — <?= $row['is_open'] ? 'Open' : 'Closed' ?>
                                <a class="btn btn-small" href="take_attendance.php?session_id=<?= $row['id'] ?>">✏️ Mark</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="muted">No sessions yet.</div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
        <p class="info">No courses assigned to you.</p>
    <?php endif; ?>
</div>
</body>
</html>
