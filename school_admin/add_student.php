<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $class = $conn->real_escape_string($_POST['class']);
    $admission_date = $conn->real_escape_string($_POST['admission_date']);

    $query = "INSERT INTO students (fullname, gender, class, admission_date)
              VALUES ('$fullname', '$gender', '$class', '$admission_date')";

    if ($conn->query($query)) {
        header("Location: students.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
