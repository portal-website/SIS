<?php
include 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password_plain = $_POST['password'];
    $role = $_POST['role'];

    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $username, $password, $role);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // If role is student, insert into students table
        if ($role === 'student') {
            $student_name = $username; // Or replace with $_POST['name'] if using a name input
            $stmt2 = $conn->prepare("INSERT INTO students (user_id, name) VALUES (?, ?)");
            $stmt2->bind_param('is', $user_id, $student_name);
            $stmt2->execute();
        }

        $message = "Registration successful!";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    
</body>
</html>
<h2>Register</h2>
<?php if (!empty($message)) echo "<p>$message</p>"; ?>

<form method="POST">
    <input type="text" name="username" required placeholder="Username"><br>
    <input type="password" name="password" required placeholder="Password"><br>

    <!-- Optionally, you can add this input if you want a real name field -->
    <!-- <input type="text" name="name" placeholder="Full Name (optional for student)"> -->

    <select name="role" required>
        <option value="">Select Role</option>
        <option value="student">Student</option>
        <option value="teacher">Teacher</option>
        <option value="parent">Parent</option>
        <option value="admin">Admin</option>
    </select><br>
    <button type="submit">Register</button>
</form>

<a href="login.php">Already registered? Login here</a>
