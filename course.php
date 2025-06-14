<?php
include 'db.php'; // Include the database connection

// Handle course addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];

    $stmt = $conn->prepare("INSERT INTO courses (course_name) VALUES (?)");
    $stmt->bind_param("s", $course_name);
    
    if ($stmt->execute()) {
        $message = "New course added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle course deletion
if (isset($_GET['delete'])) {
    $course_id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    
    if ($stmt->execute()) {
        $message = "Course deleted successfully!";
    } else {
        $message = "Error deleting course: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all courses
$courses = [];
$sql = "SELECT * FROM courses";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
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
        .message {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1>Manage Courses</h1>
    </header>
    
    <div class="container">
        <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <h2>Add New Course</h2>
        <form method="POST">
            <input type="text" name="course_name" placeholder="Course Name" required>
            <button type="submit" name="add_course">Add Course</button>
        </form>

        <h2>Existing Courses</h2>
        <table>
            <tr>
                
                <th>Course Name</th>
                <th>Action</th>
            </tr>
            <?php if (count($courses) > 0): ?>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        
                        <td><?php echo $course['course_name']; ?></td>
                        <td><a href="?delete=<?php echo $course['id']; ?>" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No courses found.</td>
                </tr>
            <?php endif; ?>
        </table>
        <a href="index.php">Back to home</a>
    </div>
</body>
</html>