<?php
// add_student.php

$message = "";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $group = trim($_POST['group']);

    // Simple validation
    if (empty($student_id) || empty($name) || empty($group)) {
        $message = "All fields are required!";
    } else {
        $file = 'students.json';

        // Load existing students
        if (file_exists($file)) {
            $students = json_decode(file_get_contents($file), true);
            if (!is_array($students)) $students = [];
        } else {
            $students = [];
        }

        // Add new student
        $students[] = [
            'student_id' => $student_id,
            'name' => $name,
            'group' => $group
        ];

        // Save back to JSON
        file_put_contents($file, json_encode($students, JSON_PRETTY_PRINT));

        $message = "Student added successfully!";
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
        <input type="text" name="student_id">

        <label>Name:</label>
        <input type="text" name="name">

        <label>Group:</label>
        <input type="text" name="group">

        <input type="submit" value="Add Student" class="btn">
    </form>

</div>

</body>
</html>
