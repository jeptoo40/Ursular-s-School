<?php
require_once '../school_admin/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($role === 'admin') {
                header("Location: ../school_admin/dashboard.php");
            } elseif ($role === 'teacher') {
                header("Location: ../school_teacher/dashboard.php");
            } elseif ($role === 'parent') {
                header("Location: ../school_parent/dashboard.php");
            }
            exit;
        } else {
            echo "<script>alert('Incorrect password.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with that email and role.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
