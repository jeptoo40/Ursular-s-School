<?php
require_once 'config.php'; // Database connection ($conn)

// Handle search
$search = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $query = "SELECT * FROM subjects WHERE subject_name LIKE '%$search%' OR subject_code LIKE '%$search%' ORDER BY id DESC";
} else {
    $query = "SELECT * FROM subjects ORDER BY id DESC";
}
$result = $conn->query($query);

// Add subject
if (isset($_POST['add_subject'])) {
    $subject_name = $_POST['subject_name'];
    $subject_code = $_POST['subject_code'];
    $description = $_POST['description'];

    if (!empty($subject_name) && !empty($subject_code)) {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name, subject_code, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $subject_name, $subject_code, $description);
        $stmt->execute();
        header("Location: subjects.php");
        exit();
    }
}
// Handle Edit (Update)
if (isset($_POST['update_subject'])) {
    $id = (int)$_POST['id'];
    $subject_name = trim($_POST['subject_name']);
    $subject_code = trim($_POST['subject_code']);
    $description = trim($_POST['description']);

    if (!empty($subject_name) && !empty($subject_code)) {
        $update = $conn->prepare("UPDATE subjects SET subject_name=?, subject_code=?, description=? WHERE id=?");
        $update->bind_param("sssi", $subject_name, $subject_code, $description, $id);

        if ($update->execute()) {
            echo "<script>alert('Subject updated successfully'); window.location.href='subjects.php';</script>";
        } else {
            echo "<script>alert('Update failed: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Please fill all required fields.');</script>";
    }
}



// Delete subject
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM subjects WHERE id=$id");
    header("Location: subjects.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Subjects Management</title>
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
}
.sidebar img {
  width: 80px;
  display: block;
  margin: 15px auto;
  border-radius: 50%;
}
.sidebar a {
  display: block;
  color: #fff;
  text-decoration: none;
  padding: 12px 20px;
  transition: all 0.2s ease;
}
.sidebar a:hover, .sidebar a.active {
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

<!-- Sidebar -->
<div class="sidebar">
  <img src="../logo.jpg" alt="Logo">
  <h4 class="text-center mb-3">Admin Panel</h4>
  <a href="dashboard.php"><i class="fa fa-home me-2"></i> Dashboard</a>
  <a href="students.php"><i class="fa fa-user-graduate me-2"></i> Students</a>
  <a href="teachers.php"><i class="fa fa-chalkboard-teacher me-2"></i> Teachers</a>
  <a href="parents.php"><i class="fa fa-users me-2"></i> Parents</a>
  <a href="#"><i class="fa fa-hand-holding-usd me-2"></i> Payments</a>
  <a href="staff.php"><i class="fa fa-briefcase me-2"></i> Staff</a>
  <a href="attendance.php"><i class="fa fa-calendar-check me-2"></i> Attendance</a>
  <a href="subjects.php" class="active"><i class="fa fa-book-open me-2"></i> Subjects / Courses</a>
  <a href="#"><i class="fa fa-file-alt me-2"></i> Exams</a>
  <a href="#"><i class="fa fa-chart-line me-2"></i> Reports</a>
  <a href="#"><i class="fa fa-cog me-2"></i> Settings</a>
  <a href="#"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="topbar">
    <h4>Manage Subjects / Courses</h4>
    <div>
      <form class="d-inline" method="get">
        <input type="text" name="search" class="form-control d-inline w-auto" placeholder="Search subject" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-success" type="submit"><i class="fa fa-search"></i></button>
      </form>
      <button class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#addSubjectModal">+ Add Subject</button>
    </div>
  </div>

  <div class="card p-3">
    <h5 class="mb-3">Subjects List</h5>
    <table class="table table-striped align-middle">
      <thead class="table-success">
        <tr>
          <th>#</th>
          <th>Subject Name</th>
          <th>Code</th>
          <th>Description</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['subject_name']) ?></td>
            <td><?= htmlspecialchars($row['subject_code']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>"><i class="fa fa-edit"></i></button>
              <a href="subjects.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this subject?');"><i class="fa fa-trash"></i></a>
            </td>
          </tr>
<!-- Edit Modal -->
<div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title">Edit Subject</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

        <div class="mb-3">
          <label>Subject Name</label>
          <input type="text" name="subject_name" class="form-control" value="<?= htmlspecialchars($row['subject_name'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
          <label>Subject Code</label>
          <input type="text" name="subject_code" class="form-control" value="<?= htmlspecialchars($row['subject_code'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
          <label>Description</label>
          <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($row['description'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" name="update_subject" class="btn btn-warning">Update</button>
      </div>
    </form>
  </div>
</div>

          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center text-muted">No subjects found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Add Subject</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Subject Name</label>
          <input type="text" name="subject_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Subject Code</label>
          <input type="text" name="subject_code" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Description</label>
          <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="add_subject" class="btn btn-success">Save</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
  $(".edit-subject-form").on("submit", function(e) {
    e.preventDefault(); // prevent normal form submit

    const form = $(this);
    const formData = form.serialize();

    $.ajax({
      type: "POST",
      url: "update_subject.php",
      data: formData,
      success: function(response) {
        alert(response);
        form.closest(".modal").modal("hide"); // close modal
        location.reload(); // reload table data
      },
      error: function() {
        alert("Failed to update subject. Please try again.");
      }
    });
  });
});
</script>

</body>
</html>
