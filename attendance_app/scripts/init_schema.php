<?php
require_once __DIR__ . '/db_connect.php';

try {
    // USERS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin','teacher','student') NOT NULL
        )
    ");

    // COURSES
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(20) NOT NULL,
            title VARCHAR(100) NOT NULL
        )
    ");

    // CLASS GROUPS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS class_groups (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL
        )
    ");

    // ENROLLMENTS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS enrollments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            course_id INT NOT NULL,
            group_id INT NULL,
            FOREIGN KEY (student_id) REFERENCES users(id),
            FOREIGN KEY (course_id) REFERENCES courses(id),
            FOREIGN KEY (group_id) REFERENCES class_groups(id)
        )
    ");

    // SESSIONS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS attendance_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            session_date DATE NOT NULL,
            is_open TINYINT(1) DEFAULT 1,
            FOREIGN KEY (course_id) REFERENCES courses(id)
        )
    ");

    // RECORDS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS attendance_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id INT NOT NULL,
            student_id INT NOT NULL,
            status ENUM('present','absent','late') DEFAULT 'absent',
            FOREIGN KEY (session_id) REFERENCES attendance_sessions(id),
            FOREIGN KEY (student_id) REFERENCES users(id),
            UNIQUE KEY uniq_session_student (session_id, student_id)
        )
    ");

    // JUSTIFICATIONS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS justifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            session_id INT NOT NULL,
            reason TEXT,
            file_path VARCHAR(255),
            status ENUM('pending','accepted','rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES users(id),
            FOREIGN KEY (session_id) REFERENCES attendance_sessions(id)
        )
    ");

    // SEED users (replace to ensure fresh demo accounts)
    $stmt = $pdo->prepare("REPLACE INTO users (username, password, role) VALUES (?,?,?)");
    $stmt->execute(['admin', password_hash("1234", PASSWORD_DEFAULT), 'admin']);
    $stmt->execute(['teacher', password_hash("1234", PASSWORD_DEFAULT), 'teacher']);
    $stmt->execute(['student', password_hash("1234", PASSWORD_DEFAULT), 'student']);

    // SEED courses
    $pdo->exec("REPLACE INTO courses (id, code, title) VALUES
        (1, 'WP101', 'Web Programming'),
        (2, 'DB102', 'Databases')
    ");

    echo "<h3 style='color:green;'>✔ Schema initialized. Demo users: admin/1234, teacher/1234, student/1234</h3>";
} catch (PDOException $e) {
    die("<p style='color:red;'>Schema init failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}
