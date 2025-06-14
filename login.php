<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user['role'] === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }

    if ($result && $user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "Invalid credentials";
        }
    } else {
        $message = "User not found";
    }
}
?>

<h2>Login</h2>
<?php if (isset($message)) echo "<p>$message</p>"; ?>
<form method="POST">
    <input type="text" name="username" required placeholder="Username"><br>
    <input type="password" name="password" required placeholder="Password"><br>
    <button type="submit">Login</button>
</form>
<?php if (isset($_GET['msg']) && $_GET['msg'] == 'logged_out'): ?>
    <p>You have been logged out successfully.</p>
<?php endif; ?>

<a href="register.php">Don't have an account? Register</a>
