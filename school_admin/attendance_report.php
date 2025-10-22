<?php
require_once 'config.php';

// Get date (default = today)
$date = $_GET['date'] ?? date('Y-m-d');

// Fetch real attendance records for that date
$query = "
    SELECT 
        a.id, 
        s.fullname AS student_name, 
        a.status, 
        a.attendance_date
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE a.attendance_date = ?
    ORDER BY s.fullname ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

// Count totals based on actual records
$present = $absent = $late = 0;
$attendance_data = [];

while ($row = $result->fetch_assoc()) {
    $attendance_data[] = $row;
    if ($row['status'] === 'Present') $present++;
    elseif ($row['status'] === 'Absent') $absent++;
    elseif ($row['status'] === 'Late') $late++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance Report - <?= htmlspecialchars($date) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-4">
<div class="container">
  <div class="card shadow-sm p-4">
    <h3 class="text-center text-success mb-4">Attendance Report - <?= htmlspecialchars($date) ?></h3>

    <div class="row text-center mb-4">
      <div class="col"><strong>Present:</strong> <?= $present ?></div>
      <div class="col"><strong>Absent:</strong> <?= $absent ?></div>
      <div class="col"><strong>Late:</strong> <?= $late ?></div>
    </div>

    <table class="table table-bordered table-striped">
      <thead class="table-success">
        <tr>
          <th>#</th>
          <th>Student Name</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php $i = 1; foreach ($attendance_data as $row): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['student_name']) ?></td>
              <td>
                <?php if ($row['status'] === 'Present'): ?>
                  <span class="text-success fw-bold"><?= $row['status'] ?></span>
                <?php elseif ($row['status'] === 'Absent'): ?>
                  <span class="text-danger fw-bold"><?= $row['status'] ?></span>
                <?php elseif ($row['status'] === 'Late'): ?>
                  <span class="text-warning fw-bold"><?= $row['status'] ?></span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['attendance_date']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4" class="text-center text-muted">No attendance recorded for this date.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="text-center mt-3">
      <button onclick="window.print()" class="btn btn-outline-secondary">
        <i class="fa fa-print"></i> Print Report
      </button>
      <a href="attendance.php" class="btn btn-outline-primary">Back</a>
    </div>
  </div>
</div>
</body>
</html>
