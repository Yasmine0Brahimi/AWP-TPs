<?php
require_once __DIR__ . '/../scripts/db_connect.php';
require_once __DIR__ . '/../scripts/auth.php';
require_role('admin');

$stats = $pdo->query("
    SELECT c.title,
           SUM(ar.status='present') AS present_count,
           SUM(ar.status='absent')  AS absent_count,
           SUM(ar.status='late')    AS late_count
    FROM attendance_records ar
    JOIN attendance_sessions s ON s.id=ar.session_id
    JOIN courses c ON c.id=s.course_id
    GROUP BY c.id
    ORDER BY c.title
")->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statistics</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include '../nav.php'; ?>
<div class="container">
    <h2>Statistics</h2>
    <table class="table">
        <tr><th>Course</th><th>Present</th><th>Absent</th><th>Late</th></tr>
        <?php if (!empty($stats)): foreach ($stats as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= (int)$r['present_count'] ?></td>
            <td><?= (int)$r['absent_count'] ?></td>
            <td><?= (int)$r['late_count'] ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="4">No statistics available.</td></tr>
        <?php endif; ?>
    </table>

    <div class="card" style="margin-top:20px;">
        <h3>Attendance Chart</h3>
        <canvas id="statsChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('statsChart').getContext('2d');
const chartData = {
  labels: [<?php foreach ($stats as $r) echo "'".addslashes($r['title'])."',"; ?>],
  datasets: [
    {
      label: 'Present',
      data: [<?php foreach ($stats as $r) echo (int)$r['present_count'].","; ?>],
      backgroundColor: '#10b981' // emerald
    },
    {
      label: 'Absent',
      data: [<?php foreach ($stats as $r) echo (int)$r['absent_count'].","; ?>],
      backgroundColor: '#ef4444' // red
    },
    {
      label: 'Late',
      data: [<?php foreach ($stats as $r) echo (int)$r['late_count'].","; ?>],
      backgroundColor: '#6366f1' // indigo
    }
  ]
};
new Chart(ctx, {
  type: 'bar',
  data: chartData,
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'top',
        labels: {
          color: '#f3f4f6', // light text
          font: { weight: '600' }
        }
      },
      title: {
        display: true,
        text: 'Attendance per Course',
        color: '#f3f4f6',
        font: { size: 18 }
      }
    },
    scales: {
      x: {
        ticks: { color: '#f3f4f6' },
        grid: { color: '#374151' }
      },
      y: {
        ticks: { color: '#f3f4f6' },
        grid: { color: '#374151' }
      }
    }
  }
});
</script>

</body>
</html>
