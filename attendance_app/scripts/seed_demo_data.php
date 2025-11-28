<?php
require_once __DIR__ . '/db_connect.php';

// Ensure student is enrolled and sessions exist, with starter records
try {
    $studentId = $pdo->query("SELECT id FROM users WHERE username='student'")->fetchColumn();
    if ($studentId) {
        // Enroll student in both courses
        $stmt = $pdo->prepare("INSERT IGNORE INTO enrollments (student_id, course_id) VALUES (?,?)");
        $stmt->execute([$studentId, 1]);
        $stmt->execute([$studentId, 2]);

        // Create sessions if none
        foreach ([1,2] as $cid) {
            $hasSession = $pdo->prepare("SELECT COUNT(*) FROM attendance_sessions WHERE course_id=?");
            $hasSession->execute([$cid]);
            if (!$hasSession->fetchColumn()) {
                $mk = $pdo->prepare("INSERT INTO attendance_sessions (course_id, session_date, is_open) VALUES (?,?,1)");
                $mk->execute([$cid, date('Y-m-d')]);
            }
        }

        // Seed attendance records per session
        $sessions = $pdo->query("SELECT id FROM attendance_sessions")->fetchAll();
        $ins = $pdo->prepare("INSERT IGNORE INTO attendance_records (session_id, student_id, status) VALUES (?,?, 'absent')");
        foreach ($sessions as $s) {
            $ins->execute([$s['id'], $studentId]);
        }
    }

    echo "<h3 style='color:green;'>✔ Demo data seeded (enrollments, sessions, records)</h3>";
} catch (PDOException $e) {
    die("<p style='color:red;'>Seeding failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}
