<?php
include 'db.php'; // Include the database connection

$message = "";

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_attendance'])) {
    $student_id = $_POST['student_id'];
    $attendance_date = date('Y-m-d');
    $status = $_POST['status'];

    $check_sql = "SELECT * FROM attendance WHERE student_id = $student_id AND attendance_date = '$attendance_date'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $sql = "UPDATE attendance SET status = '$status' WHERE student_id = $student_id AND attendance_date = '$attendance_date'";
    } else {
        $sql = "INSERT INTO attendance (student_id, attendance_date, status) VALUES ($student_id, '$attendance_date', '$status')";
    }

    if ($conn->query($sql) === TRUE) {
        $message = "Attendance marked successfully!";
    } else {
        $message = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle edit action
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_sql = "SELECT * FROM attendance WHERE id = $edit_id";
    $edit_result = $conn->query($edit_sql);
    if ($edit_result->num_rows > 0) {
        $edit_record = $edit_result->fetch_assoc();
    } else {
        $message = "Record not found.";
    }
}

// Handle update attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_attendance'])) {
    $attendance_id = $_POST['attendance_id'];
    $status = $_POST['status'];

    $update_sql = "UPDATE attendance SET status = '$status' WHERE id = $attendance_id";
    if ($conn->query($update_sql) === TRUE) {
        $message = "Attendance updated successfully!";
        header("Location: attendance.php");
        exit();
    } else {
        $message = "Error updating attendance: " . $conn->error;
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $delete_sql = "DELETE FROM attendance WHERE id = $delete_id";
    if ($conn->query($delete_sql) === TRUE) {
        $message = "Attendance deleted successfully!";
        header("Location: attendance.php");
        exit();
    } else {
        $message = "Error deleting record: " . $conn->error;
    }
}

// Fetch all students
$students = [];
$result = $conn->query("SELECT * FROM students");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch all attendance records
$attendance_records = [];
$sql = "SELECT a.*, s.name FROM attendance a JOIN students s ON a.student_id = s.id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $attendance_records[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Attendance</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        header { background: #35424a; color: white; text-align: center; padding: 10px 0; }
        .container { width: 80%; margin: auto; background: white; padding: 20px; margin-top: 20px; box-shadow: 0 0 10px #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background-color: #35424a; color: white; }
        .message { color: green; font-weight: bold; }
        .actions a { margin-right: 10px; text-decoration: none; color: #0066cc; }
    </style>
</head>
<body>
<header>
    <h1>Track Attendance</h1>
</header>

<div class="container">
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <h2><?php echo isset($edit_record) ? 'Edit Attendance' : 'Mark Attendance'; ?></h2>
    <form method="POST">
        <?php if (isset($edit_record)): ?>
            <input type="hidden" name="attendance_id" value="<?php echo $edit_record['id']; ?>">
            <input type="hidden" name="update_attendance" value="1">
            <p><strong>Student:</strong>
                <?php
                foreach ($students as $student) {
                    if ($student['id'] == $edit_record['student_id']) {
                        echo $student['name'];
                        break;
                    }
                }
                ?>
            </p>
        <?php else: ?>
            <select name="student_id" required>
                <option value="">Select Student</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo $student['id']; ?>"><?php echo $student['name']; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="mark_attendance" value="1">
        <?php endif; ?>

        <select name="status" required>
            <option value="Present" <?php if (isset($edit_record) && $edit_record['status'] == 'Present') echo 'selected'; ?>>Present</option>
            <option value="Absent" <?php if (isset($edit_record) && $edit_record['status'] == 'Absent') echo 'selected'; ?>>Absent</option>
        </select>

        <button type="submit"><?php echo isset($edit_record) ? 'Update Attendance' : 'Mark Attendance'; ?></button>
    </form>

    <h2>Attendance Records</h2>
    <table>
        <tr>
            <th>Student Name</th>
            <th>Attendance Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php if (count($attendance_records) > 0): ?>
            <?php foreach ($attendance_records as $record): ?>
                <tr>
                    <td><?php echo $record['name']; ?></td>
                    <td><?php echo $record['attendance_date']; ?></td>
                    <td><?php echo $record['status']; ?></td>
                    <td class="actions">
                        <a href="attendance.php?edit=<?php echo $record['id']; ?>">Edit</a>
                        <a href="attendance.php?delete=<?php echo $record['id']; ?>" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No attendance records found.</td></tr>
        <?php endif; ?>
    </table>

    <p><a href="index.php">Back to Home</a></p>
</div>
</body>
</html>
