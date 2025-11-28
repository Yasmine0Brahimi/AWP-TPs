<?php
require_once __DIR__ . '/scripts/auth.php';
?>
<nav class="nav">
    <div class="nav-left">
        <span class="brand">Attendance</span>
    </div>
    <div class="nav-right">
        <?php if (!empty($_SESSION['user'])): ?>
            <span class="muted"><?= htmlspecialchars($_SESSION['user']['username']) ?> (<?= htmlspecialchars($_SESSION['user']['role']) ?>)</span>
            <a href="/attendance_app/home.php">Home</a>
            <?php if (role()==='teacher'): ?><a href="/attendance_app/teacher/home.php">Teacher</a><?php endif; ?>
            <?php if (role()==='student'): ?><a href="/attendance_app/student/home.php">Student</a><?php endif; ?>
            <?php if (role()==='admin'): ?><a href="/attendance_app/admin/home.php">Admin</a><?php endif; ?>
            <a class="btn btn-outline" href="/attendance_app/logout.php">Logout</a>
        <?php endif; ?>
    </div>
</nav>
