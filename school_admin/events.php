<?php
require_once 'config.php';
header('Content-Type: application/json');

$query = "SELECT id, title, start_date AS start, end_date AS end FROM events";
$result = $conn->query($query);

$events = [];

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $events[] = [
      'id' => $row['id'],
      'title' => $row['title'],
      'start' => $row['start'],
      'end' => $row['end'] ?: null
    ];
  }
}

echo json_encode($events);
?>
