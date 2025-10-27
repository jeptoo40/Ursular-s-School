<?php
require_once '../school_admin/config.php';
session_start();

if (!isset($_SESSION['id'])) {
    echo "<script>alert('Session expired. Please log in again.');</script>";
    exit;
}

$user_id = $_SESSION['id'];

// Update Profile
if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $full_name, $email, $user_id);
    $stmt->execute();

    $_SESSION['fullname'] = $full_name;
    $_SESSION['email'] = $email;

    echo "<script>alert('Profile updated successfully!'); window.location.href='dashboard.php';</script>";
    exit;
}

// Update Password
if (isset($_POST['update_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "<script>alert('User not found.'); window.location.href='dashboard.php';</script>";
        exit;
    }

    if (!password_verify($current, $user['password'])) {
        echo "<script>alert('Current password is incorrect.'); window.location.href='dashboard.php';</script>";
    } elseif ($new !== $confirm) {
        echo "<script>alert('Passwords do not match.'); window.location.href='dashboard.php';</script>";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $hashed, $user_id);
        $update->execute();

        echo "<script>alert('Password updated successfully!'); window.location.href='dashboard.php';</script>";
    }
}
?>
