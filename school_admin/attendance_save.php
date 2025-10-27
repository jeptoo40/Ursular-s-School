<?php
require_once 'config.php';


$student_id = intval($_POST['student_id'] ?? 0);
$status = $_POST['status'] ?? 'Present';
$date = $_POST['date'] ?? date('Y-m-d');

if ($student_id <= 0) {
    http_response_code(400);
    echo "Invalid student ID";
    exit;
}


$stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id=? AND attendance_date=?");
$stmt->bind_param("is", $student_id, $date);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  
    $stmt->close();
    $update = $conn->prepare("UPDATE attendance SET status=? WHERE student_id=? AND attendance_date=?");
    $update->bind_param("sis", $status, $student_id, $date);
    $update->execute();
} else {

    $stmt->close();
    $insert = $conn->prepare("INSERT INTO attendance (student_id, status, attendance_date) VALUES (?, ?, ?)");
    $insert->bind_param("iss", $student_id, $status, $date);
    $insert->execute();
}

echo "saved";
