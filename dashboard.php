<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['user']['role'];

switch ($role) {
    case 'admin':
        header("Location: index.php"); break;
    case 'student':
        header("Location: student_dashboard.php"); break;
    case 'teacher':
        header("Location: teacher_dashboard.php"); break;
    case 'parent':
        header("Location: parent_dashboard.php"); break;
    default:
        echo "Unauthorized role!";
}
exit;
