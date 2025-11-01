<?php
session_start();
require_once '../school_admin/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'parent') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$parent_id = $_SESSION['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $address  = trim($_POST['address']);
    $phone    = trim($_POST['phone']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if ($password) {
        $update = "UPDATE parents SET fullname=?, address=?, phone=?, password=? WHERE id=?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("ssssi", $fullname, $address, $phone, $password, $parent_id);
    } else {
        $update = "UPDATE parents SET fullname=?, address=?, phone=? WHERE id=?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("sssi", $fullname, $address, $phone, $parent_id);
    }

    if ($stmt->execute()) {
        $_SESSION['fullname'] = $fullname;
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Something went wrong. Try again.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
