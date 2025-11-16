<?php
// take_attendance.php

$message = "";
$today_file = 'attendance_' . date('Y-m-d') . '.json';

// Check if attendance for today already exists
if (file_exists($today_file)) {
    $message = "Attendance for today has already been taken.";
    $students = [];
} else {
    // Load students
    $students_file = 'students.json';
    if (file_exists($students_file)) {
        $students = json_decode(file_get_contents($students_file), true);
        if (!is_array($students)) $students = [];
    } else {
        $students = [];
    }

    // If form submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $attendance = [];
        foreach ($students as $student) {
            $status = $_POST['status'][$student['student_id']] ?? 'absent';
            $attendance[] = [
                'student_id' => $student['student_id'],
                'status' => $status
            ];
        }

        // Save today's attendance
        file_put_contents($today_file, json_encode($attendance, JSON_PRETTY_PRINT));
        $message = "Attendance saved successfully!";
        $students = []; // hide form after submit
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Take Attendance</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include "nav.php"; ?>

<div class="container">

    <h2>Take Attendance</h2>

    <?php if ($message != ""): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if (!empty($students)) : ?>
        <form method="post" action="">
            <table>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Group</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($students as $student) : ?>
                <tr>
                    <td><?php echo $student['student_id']; ?></td>
                    <td><?php echo $student['name']; ?></td>
                    <td><?php echo $student['group']; ?></td>
                    <td>
                        <select name="status[<?php echo $student['student_id']; ?>]">
                            <option value="present">Present</option>
                            <option value="absent" selected>Absent</option>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <br>
            <input type="submit" value="Submit Attendance" class="btn">
        </form>
    <?php endif; ?>

</div>

</body>
</html>
