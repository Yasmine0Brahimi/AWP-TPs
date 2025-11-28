<?php
// add_student.php

require_once 'db_connect.php'; // connect to the database

$message = "";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = trim($_POST['student_id']); // corresponds to 'matricule'
    $name = trim($_POST['name']);             // corresponds to 'fullname'
    $group = trim($_POST['group']);           // corresponds to 'group_id'

    // Simple validation
    if (empty($student_id) || empty($name) || empty($group)) {
        $message = "All fields are required!";
    } else {
        try {
            $db = getDbConnection();
            $stmt = $db->prepare("INSERT INTO students (fullname, matricule, group_id) VALUES (?, ?, ?)");
            $stmt->execute([$name, $student_id, $group]);

            $message = "Student added successfully!";
        } catch (PDOException $e) {
            // Optional: log errors
            file_put_contents('db_errors.log', date('Y-m-d H:i:s') . " - " . $e->getMessage() . PHP_EOL, FILE_APPEND);
            
            $message = "Error: Could not add student. Maybe the matricule already exists.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

<?php include "nav.php"; ?>

<div class="container">

    <h2>Add Student</h2>

    <?php if ($message != ""): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Student ID:</label>
        <input type="text" name="student_id" required>

        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Group:</label>
        <input type="text" name="group" required>

        <input type="submit" value="Add Student" class="btn">
    </form>

</div>

</body>
</html>
