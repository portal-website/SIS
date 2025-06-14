<?php
include 'db.php'; // Include the database connection

// Handle delete student and related records
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // First delete attendance and grades related to this student
    $conn->query("DELETE FROM attendance WHERE student_id = $delete_id");
    $conn->query("DELETE FROM grades WHERE student_id = $delete_id");

    // Then delete the student
    if ($conn->query("DELETE FROM students WHERE id = $delete_id") === TRUE) {
        $message = "Student and related records deleted successfully.";
    } else {
        $message = "Error deleting record: " . $conn->error;
    }
}


// Fetch all students with their grades and attendance
$reports = [];
$sql = "SELECT s.id, s.name, g.grade, g.subject, a.status, a.attendance_date 
        FROM students s 
        LEFT JOIN grades g ON s.id = g.student_id 
        LEFT JOIN attendance a ON s.id = a.student_id 
        ORDER BY s.id DESC, a.attendance_date DESC";

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }

    if (isset($_GET['delete'])) {
        $delete_id = intval($_GET['delete']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background: #35424a;
            color: #ffffff;
            padding: 10px 0;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
            padding: 20px;
            background: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #35424a;
            color: white;
        }
        .message { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <h1>Student Performance Reports</h1>
    </header>
    
    <div class="container">
    <?php if (isset($message)): ?>
    <p class="message"><?php echo $message; ?></p>
<?php endif; ?>

        <h2>Reports</h2>
        <table>
    <tr>
        <th>Student Name</th>
        <th>Subject</th>
        <th>Grade</th>
        <th>Attendance Date</th>
        <th>Attendance Status</th>
        <th>Actions</th>
    </tr>
    <?php if (count($reports) > 0): ?>
        <?php foreach ($reports as $report): ?>
            <tr>
                <td><?php echo $report['name']; ?></td>
                <td><?php echo isset($report['subject']) ? $report['subject'] : 'N/A'; ?></td>
                <td><?php echo isset($report['grade']) ? $report['grade'] : 'N/A'; ?></td>
                <td><?php echo isset($report['attendance_date']) ? $report['attendance_date'] : 'N/A'; ?></td>
                <td><?php echo isset($report['status']) ? $report['status'] : 'N/A'; ?></td>
                <td>
                    <a href="report.php?delete=<?php echo $report['id']; ?>" onclick="return confirm('Delete this student and all related records?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6">No reports found.</td>
        </tr>
    <?php endif; ?>
</table>

        <a href="index.php">Back to home</a>
    </div>
</body>
</html>
