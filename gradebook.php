<?php
include 'db.php';

$message = '';
$edit_mode = false;
$edit_grade = '';
$edit_student_id = '';
$edit_subject = '';

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['student_id'], $_GET['subject'])) {
    $student_id = $_GET['student_id'];
    $subject = $_GET['subject'];

    $delete_sql = "DELETE FROM grades WHERE student_id = ? AND subject = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('is', $student_id, $subject);

    if ($stmt->execute()) {
        header("Location: gradebook.php?msg=deleted");
        exit();
    } else {
        $message = "Error deleting grade: " . $stmt->error;
    }
}

// Handle add/update submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_grade'])) {
    $student_id = $_POST['student_id'];
    $grade = $_POST['grade'];
    $subject = trim($_POST['subject']);

    $check_sql = "SELECT * FROM grades WHERE student_id = ? AND subject = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param('is', $student_id, $subject);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows > 0) {
        $sql = "UPDATE grades SET grade = ? WHERE student_id = ? AND subject = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sis', $grade, $student_id, $subject);
    } else {
        $sql = "INSERT INTO grades (student_id, subject, grade) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iss', $student_id, $subject, $grade);
    }

    if ($stmt->execute()) {
        header("Location: gradebook.php?msg=success");
        exit();
    } else {
        $message = "Error: " . $stmt->error;
    }
}

// Handle edit
if (isset($_GET['edit']) && isset($_GET['student_id'], $_GET['subject'])) {
    $edit_student_id = $_GET['student_id'];
    $edit_subject = $_GET['subject'];

    $edit_sql = "SELECT grade FROM grades WHERE student_id = ? AND subject = ?";
    $stmt = $conn->prepare($edit_sql);
    $stmt->bind_param('is', $edit_student_id, $edit_subject);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $edit_mode = true;
        $row = $result->fetch_assoc();
        $edit_grade = $row['grade'];
    }
}

// Fetch all students
$students = [];
$sql = "SELECT * FROM students";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch all grades
$grades = [];
$sql = "SELECT g.student_id, g.grade, g.subject, s.name AS student_name FROM grades g 
        JOIN students s ON g.student_id = s.id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
}

// Handle redirect messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'success') $message = "Grade updated successfully!";
    if ($_GET['msg'] == 'deleted') $message = "Grade deleted successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Grades</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        header { background: #35424a; color: #fff; padding: 10px 0; text-align: center; }
        .container { width: 80%; margin: auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background: #35424a; color: white; }
        .message { color: green; font-weight: bold; }
        .actions a { margin-right: 10px; }
    </style>
</head>
<body>
    <header>
        <h1>Manage Grades</h1>
    </header>

    <div class="container">
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <h2><?php echo $edit_mode ? 'Edit Grade' : 'Add Grade'; ?></h2>
        <form method="POST">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($edit_student_id); ?>">
                <input type="hidden" name="subject" value="<?php echo htmlspecialchars($edit_subject); ?>">
                <label>Student:</label>
                <select disabled>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>" <?php if ($student['id'] == $edit_student_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($student['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <label for="student_id">Student:</label>
                <select name="student_id" id="student_id" required>
                    <option value="">Select Student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>">
                            <?php echo htmlspecialchars($student['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <br><br>

            <label for="grade">Grade:</label>
<input type="number" name="grade" id="grade" step="0.1" min="0" max="10" required value="<?php echo htmlspecialchars($edit_grade); ?>" />


            <br><br>

            <label for="subject">Subject:</label>
            <?php if ($edit_mode): ?>
                <input type="text" disabled value="<?php echo htmlspecialchars($edit_subject); ?>">
            <?php else: ?>
                <input type="text" name="subject" id="subject" required placeholder="Enter subject">
            <?php endif; ?>

            <br><br>
            <button type="submit" name="add_grade"><?php echo $edit_mode ? 'Update Grade' : 'Submit Grade'; ?></button>
        </form>

        <h2>All Grades</h2>
        <table>
            <tr>
                <th>Student Name</th>
                <th>Grade</th>
                <th>Subject</th>
                <th>Actions</th>
            </tr>
            <?php if (!empty($grades)): ?>
                <?php foreach ($grades as $grade): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grade['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($grade['grade']); ?></td>
                        <td><?php echo htmlspecialchars($grade['subject']); ?></td>
                        <td class="actions">
                            <a href="?edit=1&student_id=<?php echo urlencode($grade['student_id']); ?>&subject=<?php echo urlencode($grade['subject']); ?>">Edit</a>
                            <a href="?delete=1&student_id=<?php echo urlencode($grade['student_id']); ?>&subject=<?php echo urlencode($grade['subject']); ?>" onclick="return confirm('Delete this grade?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No grades found.</td></tr>
            <?php endif; ?>
        </table>

        <br><a href="index.php">Back to Home</a>
    </div>
</body>
</html>
