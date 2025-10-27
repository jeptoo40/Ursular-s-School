<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$id = intval($_GET['id']);
$query = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$teacher = $result->fetch_assoc();

if (!$teacher) {
    die("Teacher not found!");
}

// Handle update
if (isset($_POST['update_teacher'])) {
    $fullname = $_POST['fullname'];
    $subject = $_POST['subject'];
    $phone = $_POST['phone'];
    $hire_date = $_POST['hire_date'];

    if (!empty($fullname) && !empty($subject) && !empty($phone) && !empty($hire_date)) {
        $update = $conn->prepare("UPDATE teachers SET fullname=?, subject=?, phone=?, hire_date=? WHERE id=?");
        $update->bind_param("ssssi", $fullname, $subject, $phone, $hire_date, $id);
        $update->execute();
        echo "<div class='alert alert-success text-center'>Teacher updated successfully!</div>";
        header("Refresh:1; url=teachers.php");
    } else {
        echo "<div class='alert alert-warning text-center'>All fields are required.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Teacher</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<style>
  body {
    min-height: 100vh;
    display: flex;
    font-family: 'Segoe UI', sans-serif;
    background-color: #f8f9fa;
  }

  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    background-color: darkslategrey;
    color: #fff;
    overflow-y: auto;
    flex-shrink: 0;
    padding-bottom: 20px;
  }

  .sidebar img {
    width: 80px;
    display: block;
    margin: 10px auto;
    border-radius: 50%;
  }

  .sidebar h4 {
    text-align: center;
    margin-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.3);
    padding-bottom: 10px;
  }

  .sidebar a {
    display: block;
    color: #fff;
    text-decoration: none;
    padding: 12px 20px;
    transition: all 0.2s ease;
  }

  .sidebar a:hover {
    background-color: grey;
    border-left: 4px solid #fff;
  }

  .main-content {
    margin-left: 250px;
    padding: 20px;
    flex-grow: 1;
  }

  .card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  }
</style>

<!-- Sidebar -->
<div class="sidebar">
  <img src="../logo.jpg" alt="Logo">
  <h4>Admin Panel</h4>
  <a href="dashboard.php"><i class="fa fa-home me-2"></i> Dashboard</a>
  <a href="students.php"><i class="fa fa-user-graduate me-2"></i> Students</a>
  <a href="teachers.php" class="bg-white text-success fw-bold"><i class="fa fa-chalkboard-teacher me-2"></i> Teachers</a>
  <a href="parents.php"><i class="fa fa-users me-2"></i> Parents</a>
  <a href="#"><i class="fa fa-hand-holding-usd me-2"></i> Payments</a>
  <a href="staff.php"><i class="fa fa-briefcase me-2"></i> Staff</a>
  <a href="attendance.php"><i class="fa fa-calendar-check me-2"></i> Attendance</a>
  <a href="subjects.php"><i class="fa fa-book-open me-2"></i> Subjects / Courses</a>
  <a href="exams.php"><i class="fa fa-file-alt me-2"></i> Exams</a>
  <a href="reports.php"><i class="fa fa-chart-line me-2"></i> Reports</a>
  
  <a href="#"><i class="fa fa-cog me-2"></i> Settings</a>
  <a href="../auth/logout.php"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="card p-4 mx-auto" style="max-width: 600px;">
    <h4 class="text-center text-success mb-4"><i class="fa fa-edit me-2"></i>Edit Teacher</h4>

    <form method="post">
      <div class="mb-3">
        <label>Full Name</label>
        <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($teacher['fullname']); ?>" required>
      </div>

      <div class="mb-3">
        <label>Subject</label>
        <input type="text" name="subject" class="form-control" value="<?php echo htmlspecialchars($teacher['subject']); ?>" required>
      </div>

      <div class="mb-3">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($teacher['phone']); ?>" required>
      </div>

      <div class="mb-3">
        <label>Hire Date</label>
        <input type="date" name="hire_date" class="form-control" value="<?php echo htmlspecialchars($teacher['hire_date']); ?>" required>
      </div>

      <div class="text-center">
        <button type="submit" name="update_teacher" class="btn btn-success px-4">Update</button>
        <a href="teachers.php" class="btn btn-secondary px-4">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
