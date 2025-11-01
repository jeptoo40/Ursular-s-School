<?php
require_once '../school_admin/config.php';

session_start();
// Prevent undefined session warnings
$_SESSION['fullname'] = $_SESSION['fullname'] ?? '';
$_SESSION['email'] = $_SESSION['email'] ?? '';

$admin_name = 'Administrator';
if (isset($_SESSION['admin_id'])) {
    $admin_id = (int) $_SESSION['admin_id'];
    $stmtAdmin = $conn->prepare("SELECT fullname FROM admins WHERE id = ?");
    $stmtAdmin->bind_param("i", $admin_id);
    $stmtAdmin->execute();
    $resAdmin = $stmtAdmin->get_result();
    if ($rowA = $resAdmin->fetch_assoc()) {
        $admin_name = $rowA['fullname'];
    }
    $stmtAdmin->close();
}

// Handle add result submission
$insert_success = "";
$insert_error = "";
if (isset($_POST['add_result'])) {
    $student_id = (int) $_POST['student_id'];
    $subject_id = (int) $_POST['subject_id'];
    $score = (int) $_POST['score'];
    $exam_term = $conn->real_escape_string(trim($_POST['exam_term']));
    $exam_year = (int) $_POST['exam_year'];

    // Basic validation
    if ($student_id > 0 && $subject_id > 0 && $score >= 0 && $score <= 100 && $exam_year > 2000) {
        $stmt = $conn->prepare("INSERT INTO results (student_id, subject_id, score, exam_term, exam_year) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisi", $student_id, $subject_id, $score, $exam_term, $exam_year);
        if ($stmt->execute()) {
            $insert_success = "Result saved successfully.";
        } else {
            $insert_error = "Failed to save result: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        $insert_error = "Please provide valid student, subject, score (0-100), term and year.";
    }
}

// Handle delete result (optional)
if (isset($_GET['delete_result'])) {
    $del_id = (int) $_GET['delete_result'];
    $stmtDel = $conn->prepare("DELETE FROM results WHERE id = ?");
    $stmtDel->bind_param("i", $del_id);
    $stmtDel->execute();
    $stmtDel->close();
    header("Location: results.php");
    exit();
}

// Fetch dropdown data: students and subjects
$students = [];
$subjects = [];

$resS = $conn->query("SELECT id, fullname, class FROM students ORDER BY fullname");
if ($resS) {
    while ($r = $resS->fetch_assoc()) $students[] = $r;
}

$resSub = $conn->query("SELECT id, subject_name FROM subjects ORDER BY subject_name");
if ($resSub) {
    while ($r = $resSub->fetch_assoc()) $subjects[] = $r;
}

// Fetch results join
$query = "
SELECT r.id, r.score, r.exam_term, r.exam_year,
       s.id AS student_id, s.fullname, s.class,
       sub.id AS subject_id, sub.subject_name
FROM results r
JOIN students s ON r.student_id = s.id
JOIN subjects sub ON r.subject_id = sub.id
ORDER BY s.class, s.fullname, r.exam_year DESC, r.exam_term DESC, sub.subject_name
";
$resResults = $conn->query($query);

// Grade helper function
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
<title>Student Results - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; min-height:100vh; }
.sidebar { position: fixed; top:0; left:0; width:250px; height:100vh; background-color: darkslategrey; color:#fff; overflow-y:auto; padding-bottom:20px; }
.sidebar img { width:80px; display:block; margin:15px auto; border-radius:50%; }
.sidebar h4 { text-align:center; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.15); padding-bottom:10px; }
.sidebar a { display:block; color:#fff; text-decoration:none; padding:12px 20px; transition:all .15s ease; }
.sidebar a:hover { background-color: grey; border-left:4px solid #fff; }
.main-content { margin-left:250px; padding:20px; }
.topbar { background-color:#fff; border-radius:8px; padding:10px 20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; }
.card { border:none; border-radius:10px; box-shadow:0 2px 5px rgba(0,0,0,0.05); }
.table thead { background-color:#198754; color:#fff; }
tr:hover { background:#f1f1f1; }
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
<a href="#"><i class="fa fa-hand-holding-usd me-2"></i> Payments</a>
<a href="staff.php"><i class="fa fa-briefcase me-2"></i> Staff</a>
<a href="attendance.php"><i class="fa fa-calendar-check me-2"></i> Attendance</a>
<a href="subjects.php"><i class="fa fa-book-open me-2"></i> Subjects / Courses</a>
<a href="exams.php"><i class="fa fa-file-alt me-2"></i> Exams</a>

<a href="results.php" class="bg-white text-success fw-bold"><i class="fa fa-chart-line me-2"></i> Results</a>
<a href="reports.php"><i class="fa fa-chart-line me-2"></i> Reports</a>


<a href="#" data-bs-toggle="modal" data-bs-target="#settingsModal">
  <i class="fa fa-cog me-2"></i> Settings
</a>
  <a href="../auth/logout.php"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
</div>

<div class="main-content">
  <div class="topbar">
    <h5>Student Performance Results</h5>
    <div><i class="fa fa-user-circle me-2 text-success"></i><span><?= htmlspecialchars($admin_name) ?></span></div>
  </div>

  <div class="topbar mb-3">
    <div>
      <form class="d-inline" method="get" action="">
        <input type="text" name="q" class="form-control d-inline w-auto" placeholder="Search student or class" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
        <button class="btn btn-success" type="submit"><i class="fa fa-search"></i></button>
      </form>
      <button class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#addResultModal">+ Add Result</button>
    </div>
  </div>

  <?php if ($insert_success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($insert_success) ?></div>
  <?php endif; ?>
  <?php if ($insert_error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($insert_error) ?></div>
  <?php endif; ?>

  <div class="card p-3">
    <h5 class="mb-3">Results List</h5>

    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Student</th>
            <th>Class</th>
            <th>Subject</th>
            <th>Marks</th>
            <th>Grade</th>
            <th>Term</th>
            <th>Year</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($resResults && $resResults->num_rows > 0):
            $i = 1;
            while ($row = $resResults->fetch_assoc()):
              $grade = grade_from_score((int)$row['score']);
          ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['fullname']) ?></td>
              <td><?= htmlspecialchars($row['class']) ?></td>
              <td><?= htmlspecialchars($row['subject_name']) ?></td>
              <td><?= (int)$row['score'] ?></td>
              <td><strong><?= $grade ?></strong></td>
              <td><?= htmlspecialchars($row['exam_term']) ?></td>
              <td><?= htmlspecialchars($row['exam_year']) ?></td>
              <td>
                <a href="edit_result.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                <a href="results.php?delete_result=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this result?');"><i class="fa fa-trash"></i></a>
              </td>
            </tr>
          <?php
            endwhile;
          else:
          ?>
            <tr><td colspan="9" class="text-center text-muted">No results found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- Add Result Modal -->
<div class="modal fade" id="addResultModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Add Result</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Student</label>
          <select name="student_id" class="form-control" required>
            <option value="">-- Select student --</option>
            <?php foreach ($students as $st): ?>
              <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['fullname']) ?> (<?= htmlspecialchars($st['class']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label>Subject</label>
          <select name="subject_id" class="form-control" required>
            <option value="">-- Select subject --</option>
            <?php foreach ($subjects as $sub): ?>
              <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['subject_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label>Marks (0 - 100)</label>
          <input type="number" name="score" class="form-control" min="0" max="100" required>
        </div>

        <div class="mb-3">
          <label>Term</label>
          <select name="exam_term" class="form-control" required>
            <option value="Term 1">Term 1</option>
            <option value="Term 2">Term 2</option>
            <option value="Term 3">Term 3</option>
            <option value="Midterm">Midterm</option>
            <option value="Final">Final</option>
          </select>
        </div>

        <div class="mb-3">
          <label>Year</label>
          <input type="number" name="exam_year" class="form-control" min="2000" max="2100" value="<?= date('Y') ?>" required>
        </div>

      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
        <button type="submit" name="add_result" class="btn btn-success">Save Result</button>
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
</body>
</html>
