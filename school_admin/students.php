<?php
require_once 'config.php'; // database connection ($conn)

// Handle search
$search = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $query = "SELECT * FROM students WHERE fullname LIKE '%$search%' OR class LIKE '%$search%' ORDER BY id DESC";
} else {
    $query = "SELECT * FROM students ORDER BY id DESC";
}
$result = $conn->query($query);

// Add Student
if (isset($_POST['add_student'])) {
    $fullname = $_POST['fullname'];
    $gender = $_POST['gender'];
    $class = $_POST['class'];
    $admission_date = $_POST['admission_date'];

    if (!empty($fullname) && !empty($class) && !empty($admission_date)) {
        $stmt = $conn->prepare("INSERT INTO students (fullname, gender, class, admission_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $gender, $class, $admission_date);
        $stmt->execute();
        header("Location: students.php");
        exit();
    }
}



if (isset($_POST['update_student'])) {
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $gender = $_POST['gender'];
    $class = $_POST['class'];
    $admission_date = $_POST['admission_date'];

    $update = $conn->prepare("UPDATE students SET fullname=?, gender=?, class=?, admission_date=? WHERE id=?");
    $update->bind_param("ssssi", $fullname, $gender, $class, $admission_date, $id);

    if ($update->execute()) {
        header("Location: students.php");
        exit();
    } else {
        $update_error = "Update failed: " . $conn->error;
    }
    
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM students WHERE id=$id");
    header("Location: students.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Students Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
  padding-bottom: 20px;
}
.sidebar img {
  width: 80px;
  display: block;
  margin: 15px auto;
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
.card {
  border: none;
  border-radius: 10px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
</style>
</head>
<body>


<div class="sidebar">
  <img src="../logo.jpg" alt="Logo">
  <h4>Admin Panel</h4>
  <a href="dashboard.php"><i class="fa fa-home me-2"></i> Dashboard</a>
  <a href="students.php" class="bg-white text-success fw-bold"><i class="fa fa-user-graduate me-2"></i> Students</a>


  <a href="teachers.php"><i class="fa fa-chalkboard-teacher me-2"></i> Teachers</a>
  <a href="parents.php"><i class="fa fa-users me-2"></i> Parents</a>
  <a href="#"><i class="fa fa-hand-holding-usd me-2"></i> Payments</a>
  <a href="staff.php"><i class="fa fa-briefcase me-2"></i> Staff</a>
  <a href="attendance.php"><i class="fa fa-calendar-check me-2"></i> Attendance</a>
  <a href="subjects.php"><i class="fa fa-book-open me-2"></i> Subjects / Courses</a>
  <a href="exams.php"><i class="fa fa-file-alt me-2"></i> Exams</a>
  <a href="reports.php"><i class="fa fa-chart-line me-2"></i> Reports</a>
  <a href="#"><i class="fa fa-cog me-2"></i> Settings</a>
  <a href="#"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
</div>


<div class="main-content">
  <div class="topbar">
    <h4>Manage Students</h4>
    <div>
      <form class="d-inline" method="get">
        <input type="text" name="search" class="form-control d-inline w-auto" placeholder="Search name or class" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-success" type="submit"><i class="fa fa-search"></i></button>
      </form>
      <button class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#addStudentModal">+ Add Student</button>
    </div>
  </div>

  
  <div class="card p-3">
    <h5 class="mb-3">Students List</h5>
    <table class="table table-striped align-middle">
      <thead class="table-success">
        <tr>
          <th>#</th>
          <th>Full Name</th>
          <th>Gender</th>
          <th>Class</th>
          <th>Admission Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['fullname']) ?></td>
            <td><?= $row['gender'] ?></td>
            <td><?= $row['class'] ?></td>
            <td><?= htmlspecialchars($row['admission_date'] ?: date('Y-m-d')) ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>"><i class="fa fa-edit"></i></button>
              <a href="students.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?');"><i class="fa fa-trash"></i></a>
            </td>
          </tr>

         
<div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title">Edit Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id" value="<?= $row['id'] ?>">

          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="fullname" class="form-control" 
                   value="<?= htmlspecialchars($row['fullname']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-control">
              <option value="Male" <?= $row['gender']=='Male'?'selected':'' ?>>Male</option>
              <option value="Female" <?= $row['gender']=='Female'?'selected':'' ?>>Female</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Class</label>
            <input type="text" name="class" class="form-control" 
                   value="<?= htmlspecialchars($row['class']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Admission Date</label>
            <input type="date" name="admission_date" class="form-control" 
                   value="<?= htmlspecialchars($row['admission_date']) ?>" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" name="update_student" class="btn btn-warning">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center">No students found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>


<div class="modal fade" id="addStudentModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Add Student</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Full Name</label>
          <input type="text" name="fullname" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Gender</label>
          <select name="gender" class="form-control" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
        <div class="mb-3">
          <label>Class</label>
          <input type="text" name="class" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Admission Date</label>
          <input type="date" name="admission_date" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="add_student" class="btn btn-success">Save</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
