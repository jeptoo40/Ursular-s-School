<?php
require_once '../school_admin/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        echo "<script>alert('Account created successfully! You can now log in.'); window.location.href='../login.php';</script>";
    } else {
        echo "<script>alert('Error creating account: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
