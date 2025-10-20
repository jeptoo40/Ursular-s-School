<?php
// config.php — connection only, no session start here!

$servername = "localhost";
$username = "root";
$password = "1234";
$database = "school_db";

// Create MySQLi connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
