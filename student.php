<?php
include 'db.php'; // Include your database connection file

// Handle form submission for adding a student
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $course_id = $_POST['course_id'];

    // Use prepared statements to prevent SQL injection
    $sql = "INSERT INTO students (name, email, course_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $name, $email, $course_id);

    if ($stmt->execute()) {
        // Redirect to avoid form resubmission
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
}

// Fetch all students to display the list
$students = [];
$sql = "SELECT s.id, s.name, s.email, c.course_name 
        FROM students s 
        JOIN courses c ON s.course_id = c.id";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch all courses for the dropdown
$courses = [];
$courseResult = $conn->query("SELECT id, course_name FROM courses");
if ($courseResult && $courseResult->num_rows > 0) {
    while ($course = $courseResult->fetch_assoc()) {
        $courses[] = $course;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
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
            margin-top: 20px;
        }
        form input, form select {
            margin: 5px;
            padding: 8px;
            width: 200px;
        }
        button {
            padding: 8px 12px;
            background-color: #35424a;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #455a64;
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
        .message {
            color: green;
            font-weight: bold;
            margin: 10px 0;
        }
        .error {
            color: red;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Manage Students</h1>
    </header>

<div class="container">
    <h2>Add Student</h2>

    <!-- Display message after submitting -->
    <?php if (isset($_GET['success'])): ?>
        <p class="message">New student added successfully!</p>
    <?php elseif (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="name" placeholder="Student Name" required>
        <input type="email" name="email" placeholder="Student Email" required>

        <select name="course_id" required>
            <option value="">Select Course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo htmlspecialchars($course['id']); ?>">
                    <?php echo htmlspecialchars($course['course_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Add Student</button>
    </form>

    <h2>Students List</h2>
    <?php if (!empty($students)): ?>
        <table>
            <tr>
                
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
            </tr>
            <?php foreach ($students as $student): ?>
                <tr>
                    
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                    <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No students found.</p>
    <?php endif; ?>

    <br>
    <a href="index.php">Back to home</a>
</div>

</body>
</html>
