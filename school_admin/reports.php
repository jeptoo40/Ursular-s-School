<?php
require_once 'config.php';
session_start();


$admin_name = $_SESSION['admin_name'] ?? 'Administrator';


$students = $conn->query("SELECT id, fullname, gender, class, admission_date FROM students ORDER BY class ASC");
$teachers = $conn->query("SELECT id, fullname, subject, phone, hire_date FROM teachers ORDER BY fullname ASC");
$exams = $conn->query("SELECT id, exam_name, class, exam_date, description FROM exams ORDER BY exam_date DESC");

$attendance = $conn->query("
    SELECT s.class, COUNT(*) AS total_days,
    SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END) AS present_days,
    ROUND(SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END)/COUNT(*)*100,2) AS attendance_rate
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    GROUP BY s.class
");

$performance = $conn->query("
    SELECT s.class, ROUND(AVG(r.score),2) AS avg_score
    FROM results r
    JOIN students s ON r.student_id = s.id
    GROUP BY s.class
    ORDER BY s.class ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>School Reports | Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body {
    min-height: 100vh;
    display: flex;
    background: #f8f9fa;
    font-family: 'Segoe UI', sans-serif;
}
.sidebar {
    width: 230px;
    background: darkslategrey;
    color: #fff;
    height: 100vh;
    padding: 20px 10px;
    position: fixed;
    top: 0;
    left: 0;
}
.sidebar img {
    width: 70px;
    display: block;
    margin: 0 auto 15px;
    border-radius: 50%;
}
.sidebar a {
    color: #fff;
    display: block;
    padding: 8px 14px;
    text-decoration: none;
    margin: 4px 0;
    border-radius: 4px;
    font-size: 15px;
}
.sidebar a:hover, .sidebar a.active {
    background: white;
    color: green;
}
.main-content {
    margin-left: 250px;
    padding: 20px;
    width: calc(100% - 250px);
}
@media print {
    .sidebar, .btn-print { display: none !important; }
    .main-content { margin: 0; width: 100%; }
}
h3.section-title {
    margin-top: 40px;
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
    padding-bottom: 5px;
}
table th {
    background: #0d6efd;
    color: white;
}
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border-bottom: 1px solid #ddd;
    padding: 10px 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}
</style>
</head>
<body>

<div class="sidebar">
    <img src="../logo.jpg" alt="Logo">
    <h5 class="text-center mb-3">Admin Panel</h5>
    <a href="dashboard.php"><i class="fa fa-home me-2"></i> Dashboard</a>
    <a href="students.php"><i class="fa fa-user-graduate me-2"></i> Students</a>
    <a href="teachers.php"><i class="fa fa-chalkboard-teacher me-2"></i> Teachers</a>
    <a href="parents.php"><i class="fa fa-users me-2"></i> Parents</a>
  <a href="staff.php"><i class="fa fa-briefcase me-2"></i> Staff</a>
    <a href="attendance.php"><i class="fa fa-calendar-check me-2"></i> Attendance</a>
    <a href="subjects.php"><i class="fa fa-book-open me-2"></i> Subjects / Courses</a>
    <a href="exams.php"><i class="fa fa-file-alt me-2"></i> Exams</a>
<a href="results.php"><i class="fa fa-file-alt me-2"></i> Results</a>
    <a href="reports.php" class="active"><i class="fa fa-chart-line me-2"></i>Reports</a>
    <a href="#"><i class="fa fa-hand-holding-usd me-2"></i> Payments</a>
     
    <a href="#" data-bs-toggle="modal" data-bs-target="#settingsModal">
  <i class="fa fa-cog me-2"></i> Settings
</a>
    <a href="../auth/logout.php"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
</div>
<div class="main-content">

    <div class="topbar">
        <h5>School Reports Overview</h5>
        <div>
            <i class="fa fa-user-circle me-2 text-success"></i>
            <span><?php echo htmlspecialchars($admin_name); ?></span>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-success fw-bold"></h2>
        <button class="btn btn-primary btn-print" onclick="window.print()">
            <i class="fa fa-print me-2"></i>Print / Download
        </button>
    </div>

  
    <h3 class="section-title">Students Report</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr><th>ID</th><th>Full Name</th><th>Gender</th><th>Class</th><th>Admission Date</th></tr>
        </thead>
        <tbody>
        <?php while($s = $students->fetch_assoc()): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= htmlspecialchars($s['fullname']) ?></td>
                <td><?= $s['gender'] ?></td>
                <td><?= $s['class'] ?></td>
                <td><?= $s['admission_date'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>


    <h3 class="section-title">Teachers Report</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Subject</th><th>Phone</th><th>Hire Date</th></tr>
        </thead>
        <tbody>
        <?php while($t = $teachers->fetch_assoc()): ?>
            <tr>
                <td><?= $t['id'] ?></td>
                <td><?= htmlspecialchars($t['fullname']) ?></td>
                <td><?= htmlspecialchars($t['subject']) ?></td>
                <td><?= htmlspecialchars($t['phone']) ?></td>
                <td><?= htmlspecialchars($t['hire_date']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    
    <h3 class="section-title">Exams Report</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr><th>ID</th><th>Exam Name</th><th>Class</th><th>Date</th><th>Description</th></tr>
        </thead>
        <tbody>
        <?php while($e = $exams->fetch_assoc()): ?>
            <tr>
                <td><?= $e['id'] ?></td>
                <td><?= htmlspecialchars($e['exam_name']) ?></td>
                <td><?= htmlspecialchars($e['class']) ?></td>
                <td><?= htmlspecialchars($e['exam_date']) ?></td>
                <td><?= htmlspecialchars($e['description']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    
    <h3 class="section-title">Attendance Report</h3>
    <table class="table table-bordered table-striped">
        <thead><tr><th>Class</th><th>Total Days</th><th>Present Days</th><th>Attendance Rate (%)</th></tr></thead>
        <tbody>
        <?php while($a = $attendance->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($a['class']) ?></td>
                <td><?= $a['total_days'] ?></td>
                <td><?= $a['present_days'] ?></td>
                <td><?= $a['attendance_rate'] ?>%</td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

   
    <h3 class="section-title">Performance Summary</h3>
    <table class="table table-bordered table-striped">
        <thead><tr><th>Class</th><th>Average Score</th></tr></thead>
        <tbody>
        <?php while($p = $performance->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($p['class']) ?></td>
                <td><?= $p['avg_score'] ?>%</td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<!-- ⚙️ Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">

      <div class="modal-header bg-success text-white rounded-top-4">
        <h5 class="modal-title"><i class="fa fa-cog me-2"></i> Account Settings</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <ul class="nav nav-tabs mb-3">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile">Profile</button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#password">Change Password</button>
          </li>
        </ul>

        <div class="tab-content">
          <!-- Profile -->
          <div class="tab-pane fade show active" id="profile">
            <form method="POST" action="update_settings.php">
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['fullname']); ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
              </div>
              <button type="submit" name="update_profile" class="btn btn-success w-100">Save Changes</button>
            </form>
          </div>

          <!-- Change Password -->
          <div class="tab-pane fade" id="password">
            <form method="POST" action="update_settings.php">
              <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>
              <button type="submit" name="update_password" class="btn btn-warning w-100">Change Password</button>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
