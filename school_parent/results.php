<?php
session_start();
require_once '../school_admin/config.php';

// Ensure parent is logged in
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'parent') {
    header("Location: ../auth/login.php");
    exit;
}

$full_name  = $_SESSION['fullname'] ?? 'Parent';
$parent_id  = $_SESSION['id'];
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    die("No linked student found.");
}

// Fetch parent info for settings modal
$parent = ['fullname' => '', 'address' => '', 'phone' => ''];
$query = "SELECT fullname, address, phone FROM parents WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $parent = $result->fetch_assoc();
}
$stmt->close();

// Fetch student info
$stmt = $conn->prepare("SELECT fullname, class FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student record not found.");
}

// Fetch student results
$query = "
SELECT r.id, r.score, r.exam_term, r.exam_year,
       sub.subject_name
FROM results r
JOIN subjects sub ON r.subject_id = sub.id
WHERE r.student_id = ?
ORDER BY r.exam_year DESC, r.exam_term, sub.subject_name
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$results = $stmt->get_result();
$stmt->close();

// Grade helper
function grade_from_score($score) {
    if ($score >= 80) return "A";
    if ($score >= 70) return "B";
    if ($score >= 60) return "C";
    if ($score >= 50) return "D";
    return "E";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Child's Performance</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background:#f8f9fa; font-family:'Poppins',sans-serif; }
.card { border:none; border-radius:12px; box-shadow:0 4px 8px rgba(0,0,0,0.05); }
.table thead { background-color:#198754; color:#fff; }
.sidebar { background-color: rgb(0, 31, 63); color:white; height:100vh; padding:20px; position:fixed; width:250px; }
.sidebar a { color:white; text-decoration:none; display:block; padding:10px; margin-bottom:5px; border-radius:6px; }
.sidebar a:hover, .sidebar a.active { background-color:burlywood; color:#000; }
.logo-section { text-align:center; padding:20px 0; border-bottom:1px solid rgba(255,255,255,0.3); }
.logo-section img { width:60px; height:60px; border-radius:50%; margin-bottom:10px; }
.topbar { background-color:#fff; border-radius:8px; padding:10px 20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-left:250px; margin-bottom:20px; display:flex; justify-content:center; align-items:center; text-align:center; }
.main-content { margin-left:250px; padding:30px; }
@media(max-width:768px){
  .sidebar { position:relative; width:100%; height:auto; }
  .topbar, .main-content { margin-left:0; }
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar d-flex flex-column justify-content-between">
  <div>
    <div class="logo-section">
      <img src="../logo.jpg" alt="Logo">
      <h5>St. Ursular's School</h5>
    </div>
    <nav class="nav flex-column mt-4">
      <a href="dashboard.php" class="nav-link"><i class="bi bi-house-door"></i> Overview</a>
      <a href="#" class="nav-link"><i class="bi bi-cash-stack"></i> Transactions</a>
      <a href="results.php" class="nav-link active"><i class="bi bi-bar-chart-line"></i> Academics</a>
      <a href="#" class="nav-link"><i class='bx bx-message-square-edit'></i> Teacher Remarks</a>
      <a href="events.php" class="nav-link"><i class="bi bi-calendar-event"></i> Upcoming Events</a>
      <a href="#" data-bs-toggle="modal" data-bs-target="#settingsModal" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
    </nav>
  </div>
  <div>
    <a href="#" class="nav-link"><i class="bi bi-question-circle"></i> Help</a>
    <a href="../auth/logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>
</div>

<!-- Topbar -->
<div class="topbar d-flex align-items-center justify-content-between px-4">
  <div class="dashboard-overview d-flex align-items-center gap-2">
    <i class="bi bi-grid-fill text-success fs-4"></i>
    <h3 class="mb-0">Overview</h3>
  </div>
  <div class="text-center flex-grow-1">
    <h3 class="welcome-text mb-0">Welcome, <span id="parentName"><?= htmlspecialchars($full_name); ?></span> 👋</h3>
    <p class="subtitle mb-0">Know what’s happening with your child at school.</p>
  </div>
  <div class="user-role d-flex align-items-center gap-2">
    <i class="bi bi-person-fill text-success"></i>
    <p class="mb-0">Parent</p>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="card p-4 mb-4">
    <h4 class="text-success fw-bold mb-3"><?= htmlspecialchars($student['fullname'] ?? 'Unknown Student') ?> — <?= htmlspecialchars($student['class'] ?? '') ?></h4>
    <h5 class="mb-3">Performance Results</h5>
    <table class="table table-bordered table-striped align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Subject</th>
          <th>Marks</th>
          <th>Grade</th>
          <th>Term</th>
          <th>Year</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($results->num_rows > 0): $i = 1; while ($r = $results->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($r['subject_name'] ?? '') ?></td>
          <td><?= $r['score'] ?></td>
          <td><strong><?= grade_from_score($r['score']) ?></strong></td>
          <td><?= htmlspecialchars($r['exam_term'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['exam_year'] ?? '') ?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6" class="text-center text-muted">No results found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="settingsModalLabel">Profile Settings</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="responseMessage"></div>
        <form id="settingsForm" method="POST">
          <div class="mb-3">
            <label class="form-label fw-bold">Full Name</label>
            <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($parent['fullname'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($parent['address'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($parent['phone'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">New Password (optional)</label>
            <input type="password" name="password" class="form-control" placeholder="Enter new password if you want to change it">
          </div>
          <button type="submit" class="btn btn-success w-100 fw-bold">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
  $('#settingsForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
      url: 'update_profile.php',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(response) {
        let alertBox = $('#responseMessage');
        if (response.status === 'success') {
          alertBox.html(`<div class="alert alert-success">${response.message}</div>`);
          // Update name dynamically without reload
          $('#parentName').text($('input[name="fullname"]').val());
          setTimeout(() => { alertBox.empty(); $('#settingsModal').modal('hide'); }, 1500);
        } else {
          alertBox.html(`<div class="alert alert-danger">${response.message}</div>`);
        }
      },
      error: function() {
        $('#responseMessage').html('<div class="alert alert-danger">Server error occurred.</div>');
      }
    });
  });
});
</script>

</body>
</html>
