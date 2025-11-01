<?php
session_start();
require_once 'config.php';


if (isset($_POST['mark_attendance'])) {
    $date = $_POST['attendance_date'] ?? date('Y-m-d');
    $student_ids = $_POST['student_id'] ?? [];
    $statuses = $_POST['status'] ?? [];

    foreach ($student_ids as $student_id) {
        $student_id = intval($student_id);
        $status = $statuses[$student_id] ?? 'Present'; 

        $stmt = $conn->prepare("INSERT INTO attendance (student_id, status, attendance_date)
                                VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE status=?");
        $stmt->bind_param("isss", $student_id, $status, $date, $status);
        $stmt->execute();
    }

    $success = "Attendance saved for $date";
}


$students_result = $conn->query("SELECT id, fullname FROM students ORDER BY fullname ASC");


$filter_date = $_POST['attendance_date'] ?? $_GET['date'] ?? date('Y-m-d');


$attendance_result = $conn->query("SELECT * FROM attendance WHERE attendance_date='$filter_date'");
$attendance_data = [];
while ($row = $attendance_result->fetch_assoc()) {
    $attendance_data[$row['student_id']] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>School Attendance</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body {
    min-height: 100vh;
    display: flex;
    font-family: 'Segoe UI', sans-serif;
    background: #f8f9fa;
}
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    background: darkslategrey;
    color: #fff;
    overflow-y: auto;
    padding-bottom: 20px;
}
.sidebar img { width: 80px; display: block; margin: 15px auto; border-radius: 50%; }
.sidebar h4 { text-align: center; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.3); padding-bottom: 10px; }
.sidebar a { display: block; color: #fff; text-decoration: none; padding: 12px 20px; transition: all .15s ease; }
.sidebar a:hover { background: grey; border-left: 4px solid #fff; }
.main-content { margin-left: 250px; padding: 20px; flex-grow: 1; }

.topbar {
  background-color: #fff;
  border-radius: 8px;
  padding: 10px 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <img src="../logo.jpg" alt="Logo">
    <h4>Admin Panel</h4>
    <a href="dashboard.php"><i class="fa fa-home me-2"></i> Dashboard</a>
    <a href="students.php"><i class="fa fa-user-graduate me-2"></i> Students</a>
    <a href="teachers.php"><i class="fa fa-chalkboard-teacher me-2"></i> Teachers</a>
    <a href="parents.php"><i class="fa fa-users me-2"></i> Parents</a>
    <a href="staff.php"><i class="fa fa-briefcase me-2"></i> Staff</a>
    <a href="attendance.php" class="bg-white text-success fw-bold"><i class="fa fa-calendar-check me-2"></i> Attendance</a>
    <a href="subjects.php"><i class="fa fa-book-open me-2"></i> Subjects / Courses</a>
    <a href="exams.php"><i class="fa fa-file-alt me-2"></i> Exams</a>
    <a href="results.php"><i class="fa fa-file-alt me-2"></i> Results</a>
    <a href="reports.php"><i class="fa fa-chart-line me-2"></i> Reports</a>
    <a href="#"><i class="fa fa-hand-holding-usd me-2"></i> Payments</a>

<a href="#" data-bs-toggle="modal" data-bs-target="#settingsModal">
  <i class="fa fa-cog me-2"></i> Settings
</a>

    <a href="../auth/logout.php"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
</div>

<div class="main-content container mt-5">
    
<div class="topbar">
      <h5>Admin Dashboard Overview</h5>
      <div>
        <i class="fa fa-user-circle me-2 text-success"></i>
        <span><?php echo htmlspecialchars($admin_name ?? 'Admin'); ?>
</span>
      </div>
    </div>
    <h3 class="mb-4 text-success">School Attendance - <?= htmlspecialchars($filter_date) ?></h3>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="mb-4">
        <div class="row g-2 align-items-center mb-3">
            <div class="col-md-3">
                <input type="date" name="attendance_date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>" required>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit" name="mark_attendance">Load / Save Attendance</button>
            </div>
        </div>




        <form method="post" class="mb-4" id="attendanceForm" action="attendance.php">
    <div class="row g-2 align-items-center mb-3">
        <div class="col-md-3">
            <input type="date" name="attendance_date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>" required>
        </div>
        <div class="col-md-3 d-flex gap-2">
            
            <a href="attendance_report.php?date=<?= htmlspecialchars($filter_date) ?>" class="btn btn-success w-50">Generate Report</a>
        </div>
    </div>








        
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; $students_result->data_seek(0); ?>
                <?php while ($student = $students_result->fetch_assoc()): ?>
                    <?php
                        $status = $attendance_data[$student['id']]['status'] ?? 'Present';
                        $att_id = $attendance_data[$student['id']]['id'] ?? 0;
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($student['fullname']) ?></td>
                        <td>
                            <select name="status[<?= $student['id'] ?>]" class="form-select status-select"
                                    data-id="<?= $att_id ?>"
                                    data-student="<?= $student['id'] ?>"
                                    data-date="<?= $filter_date ?>">
                                <option value="Present" <?= $status=='Present'?'selected':'' ?>>Present</option>
                                <option value="Absent" <?= $status=='Absent'?'selected':'' ?>>Absent</option>
                                <option value="Late" <?= $status=='Late'?'selected':'' ?>>Late</option>
                            </select>
                        </td>
                        <input type="hidden" name="student_id[]" value="<?= $student['id'] ?>">
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </form>
</div>
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

<script>
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', async () => {
        const student_id = select.dataset.student;
        const date = select.dataset.date;
        const value = select.value;

        const formData = new FormData();
        formData.append('student_id', student_id);
        formData.append('status', value);
        formData.append('date', date);

        await fetch('attendance_save.php', { method: 'POST', body: formData });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
