<?php
require_once __DIR__ . '/../scripts/db_connect.php';
require_once __DIR__ . '/../scripts/auth.php';
require_role('teacher');

$session_id = (int)($_GET['session_id'] ?? 0);
$ses = $pdo->prepare("SELECT s.*, c.title FROM attendance_sessions s JOIN courses c ON c.id=s.course_id WHERE s.id=?");
$ses->execute([$session_id]);
$session = $ses->fetch();
if (!$session) die("Session not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['status'] ?? [] as $student_id => $status) {
        $pdo->prepare("UPDATE attendance_records SET status=? WHERE session_id=? AND student_id=?")
            ->execute([$status, $session_id, (int)$student_id]);
    }
    $pdo->prepare("UPDATE attendance_sessions SET is_open=0 WHERE id=?")->execute([$session_id]);
    $saved = true;
}

$rows = $pdo->prepare("
    SELECT u.id, u.username, ar.status
    FROM attendance_records ar JOIN users u ON u.id=ar.student_id
    WHERE ar.session_id=? ORDER BY u.username
");
$rows->execute([$session_id]);
$data = $rows->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mark attendance</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include '../nav.php'; ?>
<div class="container">
    <h2><?= htmlspecialchars($session['title']) ?> — <?= htmlspecialchars($session['session_date']) ?></h2>
    <?php if (!empty($saved)): ?><div class="success">Saved. Session closed.</div><?php endif; ?>
    <form method="post">
        <table class="table">
            <tr><th>Student</th><th>Status</th></tr>
            <?php if (!empty($data)): foreach ($data as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['username']) ?></td>
                <td>
                    <select name="status[<?= $r['id'] ?>]">
                        <option value="present" <?= $r['status']=='present'?'selected':'' ?>>present</option>
                        <option value="absent"  <?= $r['status']=='absent'?'selected':''  ?>>absent</option>
                        <option value="late"    <?= $r['status']=='late'?'selected':''    ?>>late</option>
                    </select>
                </td>
            </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="2">No students enrolled for this course.</td></tr>
            <?php endif; ?>
        </table>
        <button type="submit" class="btn">Save & close</button>
    </form>
</div>
</body>
</html>
