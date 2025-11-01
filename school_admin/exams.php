<?php
session_start();
require_once 'config.php'; 


if (isset($_POST['add_exam'])) {
    $exam_name   = trim($_POST['exam_name'] ?? '');
    $subject_id  = intval($_POST['subject_id'] ?? 0);
    $exam_date   = $_POST['exam_date'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $class       = trim($_POST['class'] ?? '');

    if ($exam_name && $subject_id && $exam_date && $class) {
        $stmt = $conn->prepare("INSERT INTO exams (exam_name, subject_id, exam_date, description, class) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $exam_name, $subject_id, $exam_date, $description, $class);
        $stmt->execute();
        header("Location: exams.php");
        exit();
    } else {
        $add_error = "Please fill all required fields.";
    }
}


if (isset($_POST['update_exam'])) {
    $id          = intval($_POST['id'] ?? 0);
    $exam_name   = trim($_POST['exam_name'] ?? '');
    $subject_id  = intval($_POST['subject_id'] ?? 0);
    $exam_date   = $_POST['exam_date'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $class       = trim($_POST['class'] ?? '');

    if ($id && $exam_name && $subject_id && $exam_date && $class) {
        $stmt = $conn->prepare("UPDATE exams SET exam_name=?, subject_id=?, exam_date=?, description=?, class=? WHERE id=?");
        $stmt->bind_param("sisssi", $exam_name, $subject_id, $exam_date, $description, $class, $id);
        $stmt->execute();
        header("Location: exams.php");
        exit();
    } else {
        $update_error = "Please fill all required fields.";
    }
}


if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        $conn->query("DELETE FROM exams WHERE id=$id");
        header("Location: exams.php");
        exit();
    }
}


$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search'] ?? '');
    $query = "SELECT e.*, s.subject_name
              FROM exams e
              LEFT JOIN subjects s ON e.subject_id = s.id
              WHERE e.exam_name LIKE '%$search%' OR s.subject_name LIKE '%$search%'
              ORDER BY e.exam_date DESC";
} else {
    $query = "SELECT e.*, s.subject_name
              FROM exams e
              LEFT JOIN subjects s ON e.subject_id = s.id
              ORDER BY e.exam_date DESC";
}
$result = $conn->query($query);


$subjects_result = $conn->query("SELECT id, subject_name FROM subjects ORDER BY subject_name ASC");
$classes_result  = $conn->query("SELECT DISTINCT class FROM students ORDER BY class ASC");


$total_exams        = $conn->query("SELECT COUNT(*) AS total FROM exams")->fetch_assoc()['total'] ?? 0;
$upcoming_exams     = $conn->query("SELECT COUNT(*) AS upcoming FROM exams WHERE exam_date >= CURDATE()")->fetch_assoc()['upcoming'] ?? 0;
$subjects_covered   = $conn->query("SELECT COUNT(DISTINCT subject_id) AS subjects FROM exams")->fetch_assoc()['subjects'] ?? 0;
$total_students     = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'] ?? 0;


$class_perf = [];
$class_query = $conn->query("SELECT st.class, AVG(r.score) AS avg_score 
                             FROM results r 
                             JOIN students st ON r.student_id = st.id
                             GROUP BY st.class");
while ($row = $class_query->fetch_assoc()) {
    $class_perf[] = ['class' => $row['class'], 'avg_score' => (float)$row['avg_score']];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Exams Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family: 'Segoe UI', sans-serif; background:#f8f9fa; display:flex; min-height:100vh; }
.sidebar { position: fixed; top:0; left:0; width:250px; height:100vh; background:darkslategrey; color:#fff; overflow-y:auto; padding-bottom:20px; }
.sidebar img { width:80px; display:block; margin:15px auto; border-radius:50%; }
.sidebar h4 { text-align:center; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.3); padding-bottom:10px; }
.sidebar a { display:block; color:#fff; text-decoration:none; padding:12px 20px; transition:all .15s ease; }
.sidebar a:hover, .sidebar a.active { background:grey; border-left:4px solid #fff; }
.main-content { margin-left:250px; padding:20px; flex-grow:1; }
.card-circle { border:none; border-radius:50%; display:flex; flex-direction:column; justify-content:center; align-items:center; width:120px; height:120px; margin:auto; text-align:center; }
.card-circle h2 { font-size:1.5rem; margin:0; }
.card-circle h5 { font-size:0.9rem; font-weight:500; margin-top:5px; }
.modal { position: fixed !important; z-index: 99999 !important; pointer-events: auto !important; }
.modal-dialog { pointer-events: auto !important; }
.modal-backdrop { z-index: 99998 !important; }

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


<div class="sidebar">
    <img src="../logo.jpg" alt="Logo">
    <h4>Admin Panel</h4>
    <a href="dashboard.php"><i class="fa fa-home me-2"></i> Dashboard</a>
    <a href="students.php"><i class="fa fa-user-graduate me-2"></i> Students</a>
    <a href="teachers.php"><i class="fa fa-chalkboard-teacher me-2"></i> Teachers</a>
    <a href="parents.php"><i class="fa fa-users me-2"></i> Parents</a>
  <a href="staff.php"><i class="fa fa-briefcase me-2"></i> Staff</a>
    <a href="attendance.php"><i class="fa fa-calendar-check me-2"></i> Attendance</a>
    <a href="subjects.php"><i class="fa fa-book-open me-2"></i> Subjects</a>
    <a href="exams.php" class="bg-white text-success fw-bold"><i class="fa fa-file-alt me-2"></i> Exams</a>
    <a href="results.php"><i class="fa fa-file-alt me-2"></i> Results</a>
    <a href="reports.php"><i class="fa fa-chart-line me-2"></i> Reports</a>
    <a href="#"><i class="fa fa-hand-holding-usd me-2"></i> Payments</a>
     
  <a href="#" data-bs-toggle="modal" data-bs-target="#settingsModal">
  <i class="fa fa-cog me-2"></i> Settings
</a>
    <a href="../auth/logout.php"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
</div>

<div class="main-content">
       
<div class="topbar">
      <h5>Admin Dashboard Overview</h5>
      <div>
        <i class="fa fa-user-circle me-2 text-success"></i>
        <span><?php echo htmlspecialchars($admin_name ?? 'Admin'); ?>
</span>
      </div>
    </div>
<h3 class="mb-4 text-success">Exams Management</h3>

<?php if(!empty($add_error)) echo "<div class='alert alert-warning'>$add_error</div>"; ?>
<?php if(!empty($update_error)) echo "<div class='alert alert-danger'>$update_error</div>"; ?>


<div class="d-flex justify-content-between mb-4 flex-wrap gap-4">
    <div class="card-circle bg-success text-white">
        <h2><?= $total_exams ?></h2>
        <h5>Total Exams</h5>
    </div>
    <div class="card-circle bg-primary text-white">
        <h2><?= $upcoming_exams ?></h2>
        <h5>Upcoming Exams</h5>
    </div>
    <div class="card-circle bg-warning text-white">
        <h2><?= $subjects_covered ?></h2>
        <h5>Subjects Covered</h5>
    </div>
    <div class="card-circle bg-info text-white">
        <h2><?= $total_students ?></h2>
        <h5>Total Students</h5>
    </div>
</div>


<div class="card mb-4 shadow-sm border-0 p-3" style="border-radius:10px;">
    <h5 class="text-success mb-3 text-center">Exam Performance by Class</h5>
    <canvas id="classChart" height="100"></canvas>
</div>


<div class="d-flex justify-content-between mb-3">
    <form class="d-flex" method="get">
        <input type="text" name="search" class="form-control me-2" placeholder="Search exam..." value="<?= htmlspecialchars($search ?? '') ?>">
        <button class="btn btn-success">Search</button>
    </form>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Exam</button>
</div>

<div class="card shadow-sm p-3 border-0">
<div class="table-responsive">
<table class="table table-bordered table-hover w-100">
<thead class="table-success">
<tr>
<th>#</th>
<th>Exam Name</th>
<th>Subject</th>
<th>Class</th>
<th>Students</th>
<th>Exam Date</th>
<th>Description</th>

<th>Actions</th>

</tr>
</thead>
<tbody>
<?php if($result && $result->num_rows > 0): ?>
<?php while($row = $result->fetch_assoc()):
    $class_name = $row['class'] ?? '';
    $student_count = $conn->query("SELECT COUNT(*) AS total FROM students WHERE class='". $conn->real_escape_string($class_name) ."'")->fetch_assoc()['total'] ?? 0;
?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['exam_name'] ?? '') ?></td>
<td><?= htmlspecialchars($row['subject_name'] ?? '—') ?></td>
<td><?= htmlspecialchars($row['class'] ?? '') ?></td>
<td><?= $student_count ?></td>
<td><?= htmlspecialchars($row['exam_date'] ?? '') ?></td>
<td><?= htmlspecialchars($row['description'] ?? '') ?></td>
<td>
<button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
<a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this exam?')">Delete</a>
</td>
</tr>


<div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
<div class="modal-dialog">
<form method="post" class="modal-content">
<div class="modal-header bg-warning text-white">
<h5 class="modal-title">Edit Exam</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
<div class="mb-3">
<label>Exam Name</label>
<input type="text" name="exam_name" class="form-control" value="<?= htmlspecialchars($row['exam_name']) ?>" required>
</div>
<div class="mb-3">
<label>Subject</label>
<select name="subject_id" class="form-control" required>
<option value="">Select Subject</option>
<?php
$subjects_result->data_seek(0);
while($s = $subjects_result->fetch_assoc()):
$selected = ($s['id'] == $row['subject_id']) ? 'selected' : '';
echo "<option value='".(int)$s['id']."' $selected>".htmlspecialchars($s['subject_name'])."</option>";
endwhile;
?>
</select>
</div>
<div class="mb-3">
<label>Class</label>
<select name="class" class="form-control" required>
<option value="">Select Class</option>
<?php
$classes_result->data_seek(0);
while($c = $classes_result->fetch_assoc()):
$selected = ($c['class'] == $row['class']) ? 'selected' : '';
echo "<option value='".htmlspecialchars($c['class'])."' $selected>".htmlspecialchars($c['class'])."</option>";
endwhile;
?>
</select>
</div>
<div class="mb-3">
<label>Exam Date</label>
<input type="date" name="exam_date" class="form-control" value="<?= $row['exam_date'] ?>" required>
</div>
<div class="mb-3">
<label>Description</label>
<textarea name="description" class="form-control"><?= htmlspecialchars($row['description']) ?></textarea>
</div>
</div>
<div class="modal-footer">
<button type="submit" name="update_exam" class="btn btn-warning">Update</button>
</div>
</form>
</div>
</div>

<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="8" class="text-center text-muted">No exams found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>


<div class="modal fade" id="addModal" tabindex="-1">
<div class="modal-dialog">
<form method="post" class="modal-content">
<div class="modal-header bg-primary text-white">
<h5 class="modal-title">Add Exam</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<div class="mb-3">
<label>Exam Name</label>
<input type="text" name="exam_name" class="form-control" required>
</div>
<div class="mb-3">
<label>Subject</label>
<select name="subject_id" class="form-control" required>
<option value="">Select Subject</option>
<?php
$subjects_result->data_seek(0);
while($s = $subjects_result->fetch_assoc()):
echo "<option value='".(int)$s['id']."'>".htmlspecialchars($s['subject_name'])."</option>";
endwhile;
?>
</select>
</div>
<div class="mb-3">
<label>Class</label>
<select name="class" class="form-control" required>
<option value="">Select Class</option>
<?php
$classes_result->data_seek(0);
while($c = $classes_result->fetch_assoc()):
echo "<option value='".htmlspecialchars($c['class'])."'>".htmlspecialchars($c['class'])."</option>";
endwhile;
?>
</select>
</div>
<div class="mb-3">
<label>Exam Date</label>
<input type="date" name="exam_date" class="form-control" required>
</div>
<div class="mb-3">
<label>Description</label>
<textarea name="description" class="form-control"></textarea>
</div>
</div>
<div class="modal-footer">
<button type="submit" name="add_exam" class="btn btn-primary">Save</button>
</div>
</form>
</div>
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















<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const classData = <?= json_encode($class_perf) ?>;
const labels = classData.map(c => c.class);
const scores = classData.map(c => c.avg_score);

const ctx = document.getElementById('classChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Average Score',
            data: scores,
            backgroundColor: 'rgba(25, 135, 84, 0.7)',
            borderColor: 'rgba(25, 135, 84, 1)',
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: {
        responsive:true,
        scales:{ y:{beginAtZero:true, max:100} },
        plugins:{ legend:{display:false} }
    }
});
</script>

</body>
</html>
