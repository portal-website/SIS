<?php
session_start();
include 'db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

// Get user ID and retrieve the corresponding student ID
$user_id = $_SESSION['user']['id'];
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    // Try to fetch student ID if not already in session
    $stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($student = $result->fetch_assoc()) {
        $student_id = $student['id'];
        $_SESSION['student_id'] = $student_id;
    } else {
        echo "No student record found.";
        exit;
    }
}

// Fetch grades for the student
$grades = [];
$sql = "SELECT g.subject, g.grade FROM grades g WHERE g.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $grades[] = $row;
}

// Fetch attendance records for the student
$attendance_records = [];
$sql = "SELECT a.attendance_date, a.status FROM attendance a WHERE a.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $attendance_records[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #35424a;
            color: white;
            padding: 10px;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        th {
            background-color: #35424a;
            color: white;
        }
        .message {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['username']); ?></h1>
    </header>

    <div class="container">
        <h2>Your Grades</h2>
        <table>
            <tr>
                <th>Subject</th>
                <th>Grade</th>
            </tr>
            
            <?php if (!empty($grades)): ?>
                <?php foreach ($grades as $grade): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grade['subject']); ?></td>
                        <td><?php echo htmlspecialchars($grade['grade']); ?></td>
                    </tr>
                    echo "<h2>Grades</h2><ul>";
foreach ($grades as $g) echo "<li>{$g['subject']}: {$g['grade']}</li>";
echo "</ul>";
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="2">No grades recorded yet.</td></tr>
            <?php endif; ?>
        </table>

        <h2>Your Attendance</h2>
        <table>
            <tr>
                <th>Attendance Date</th>
                <th>Status</th>
                
            </tr>
            <?php if (!empty($attendance_records)): ?>
                <?php foreach ($attendance_records as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['attendance_date']); ?></td>
                        <td><?php echo htmlspecialchars($record['status']); ?></td>
                    </tr>
                    echo "<h2>Attendance</h2><ul>";
foreach ($attendance_records as $a) echo "<li>{$a['attendance_date']}: {$a['status']}</li>";
echo "</ul>";
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="2">No attendance records found.</td></tr>
            <?php endif; ?>
        </table>
        <a href="logout.php" style="text-decoration: none; padding: 10px; background-color: #35424a; color: white; border-radius: 5px;">Logout</a>

    </div>
</body>
</html>
