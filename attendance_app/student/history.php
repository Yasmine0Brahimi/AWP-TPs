<?php
require_once __DIR__ . '/../scripts/db_connect.php';
require_once __DIR__ . '/../scripts/auth.php';
require_role('student');

// Get current student ID
$student_id = $_SESSION['user']['id'] ?? 0;

// Fetch enrolled courses
$courses = $pdo->prepare("
    SELECT c.id, c.title, c.code 
    FROM courses c
    JOIN enrollments e ON e.course_id = c.id
    WHERE e.student_id = ?
    ORDER BY c.title
");
$courses->execute([$student_id]);
$courses = $courses->fetchAll();

// Fetch attendance records
$records = $pdo->prepare("
    SELECT ar.session_id, ar.status, s.session_date, c.title
    FROM attendance_records ar
    JOIN attendance_sessions s ON s.id = ar.session_id
    JOIN courses c ON c.id = s.course_id
    WHERE ar.student_id = ?
    ORDER BY s.session_date DESC
");
$records->execute([$student_id]);
$records = $records->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Attendance History</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include '../nav.php'; ?>
<div class="container">
    <h2>My Attendance History</h2>

    <?php if (!empty($courses)): ?>
        <h3>Enrolled Courses</h3>
        <ul class="list">
            <?php foreach ($courses as $c): ?>
                <li class="list-item">
                    <strong><?= htmlspecialchars($c['title'] ?? '') ?></strong>
                    <span class="muted">(<?= htmlspecialchars($c['code'] ?? '') ?>)</span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="info">You are not enrolled in any courses.</p>
    <?php endif; ?>

    <h3>Attendance Records</h3>
    <?php if (!empty($records)): ?>
        <table class="table">
            <tr><th>Date</th><th>Course</th><th>Status</th></tr>
            <?php foreach ($records as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['session_date'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['title'] ?? '') ?></td>
                    <td>
                        <?php if ($r['status'] === 'present'): ?>
                            <span style="color:#10b981;">Present</span>
                        <?php elseif ($r['status'] === 'late'): ?>
                            <span style="color:#6366f1;">Late</span>
                        <?php else: ?>
                            <span style="color:#ef4444;">Absent</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Summary Chart</h3>
        <canvas id="studentChart"></canvas>
        <script>
        const ctx = document.getElementById('studentChart').getContext('2d');
        const chartData = {
            labels: ['Present','Absent','Late'],
            datasets: [{
                label: 'My Attendance',
                data: [
                    <?= count(array_filter($records, fn($r) => $r['status']==='present')) ?>,
                    <?= count(array_filter($records, fn($r) => $r['status']==='absent')) ?>,
                    <?= count(array_filter($records, fn($r) => $r['status']==='late')) ?>
                ],
                backgroundColor: ['#10b981','#ef4444','#6366f1']
            }]
        };
        new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'Overall Attendance',
                        color: '#f3f4f6',
                        font: { size: 18 }
                    }
                },
                scales: {
                    x: { ticks: { color: '#f3f4f6' }, grid: { color: '#374151' } },
                    y: { ticks: { color: '#f3f4f6' }, grid: { color: '#374151' } }
                }
            }
        });
        </script>
    <?php else: ?>
        <p class="info">No attendance records yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
