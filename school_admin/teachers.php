<?php
require_once 'config.php'; // uses $conn

// Handle search
$search = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $query = "SELECT * FROM teachers WHERE fullname LIKE '%$search%' OR subject LIKE '%$search%' ORDER BY id DESC";
} else {
    $query = "SELECT * FROM teachers ORDER BY id DESC";
}

$result = $conn->query($query);

// Handle add teacher
if (isset($_POST['add_teacher'])) {
    $fullname = $_POST['fullname'];
    $subject = $_POST['subject'];
    $phone = $_POST['phone'];
    $hire_date = $_POST['hire_date'];

    if (!empty($fullname) && !empty($subject) && !empty($phone) && !empty($hire_date)) {
        $stmt = $conn->prepare("INSERT INTO teachers (fullname, subject, phone, hire_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $subject, $phone, $hire_date);
        $stmt->execute();
        echo "<div class='alert alert-success text-center'>Teacher added successfully!</div>";
    } else {
        echo "<div class='alert alert-warning text-center'>All fields are required.</div>";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM teachers WHERE id=$id");
    echo "<div class='alert alert-danger text-center'>Teacher deleted successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Teachers Management</title>
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
  <a href="#"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-success">Manage Teachers</h3>

    <!-- Search Form -->
    <form class="d-flex" method="get">
      <input type="text" name="search" class="form-control me-2" placeholder="Search by name or subject" value="<?php echo htmlspecialchars($search); ?>">
      <button class="btn btn-success" type="submit">Search</button>
    </form>

    <!-- Add Teacher Button -->
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">+ Add Teacher</button>
  </div>

  <!-- Teachers Table -->
  <div class="card">
    <div class="card-body">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Subject</th>
            <th>Phone</th>
            <th>Hire Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['fullname'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['subject'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['hire_date'] ?? ''); ?></td>





                
                <td>
                  <a href="edit_teacher.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                  <a href="teachers.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this teacher?')"><i class="fa fa-trash"></i></a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center">No teachers found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Add New Teacher</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="fullname" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Subject</label>
            <input type="text" name="subject" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Hire Date</label>
            <input type="date" name="hire_date" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add_teacher" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
