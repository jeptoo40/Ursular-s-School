<?php
require_once 'config.php'; // $conn

// ---------- Handle Add ----------
if (isset($_POST['add_parent'])) {
    $fullname   = trim($_POST['fullname'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $student_id = intval($_POST['student_id'] ?? 0);

    if ($fullname !== '' && $phone !== '') {
        $stmt = $conn->prepare("INSERT INTO parents (fullname, phone, address, student_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $fullname, $phone, $address, $student_id);
        $stmt->execute();
        header("Location: parents.php");
        exit();
    } else {
        $add_error = "Please fill required fields.";
    }
}

// ---------- Handle Update ----------
if (isset($_POST['update_parent'])) {
    $id         = intval($_POST['id'] ?? 0);
    $fullname   = trim($_POST['fullname'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $student_id = intval($_POST['student_id'] ?? 0);

    if ($id > 0) {
        $update = $conn->prepare("UPDATE parents SET fullname=?, phone=?, address=?, student_id=? WHERE id=?");
        $update->bind_param("sssii", $fullname, $phone, $address, $student_id, $id);

        if ($update->execute()) {
            header("Location: parents.php");
            exit();
        } else {
            $update_error = "Update failed: " . $conn->error;
        }
    } else {
        $update_error = "Invalid parent ID.";
    }
}

// ---------- Handle Delete ----------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        $conn->query("DELETE FROM parents WHERE id=$id");
        header("Location: parents.php");
        exit();
    }
}

// ---------- Handle Search & Fetch ----------
$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $query = "SELECT p.*, s.fullname AS student_name 
              FROM parents p 
              LEFT JOIN students s ON p.student_id = s.id
              WHERE p.fullname LIKE '%$search%' 
                 OR s.fullname LIKE '%$search%' 
                 OR p.phone LIKE '%$search%'
              ORDER BY p.id DESC";
} else {
    $query = "SELECT p.*, s.fullname AS student_name 
              FROM parents p 
              LEFT JOIN students s ON p.student_id = s.id
              ORDER BY p.id DESC";
}

$result = $conn->query($query);

// Fetch students for dropdowns
$students_result = $conn->query("SELECT id, fullname FROM students ORDER BY fullname ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Parents Management</title>
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
    z-index: 1000 !important;
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
    border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    padding-bottom: 10px;
  }

  .sidebar a {
    display: block;
    color: #fff;
    text-decoration: none;
    padding: 12px 20px;
    transition: all .15s ease;
  }

  .sidebar a:hover {
    background: grey;
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
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  }


  .modal {
    position: fixed !important;
    z-index: 99999 !important;
    pointer-events: auto !important;
  }

  .modal-dialog {
    pointer-events: auto !important;
  }

  .modal-backdrop {
    z-index: 99998 !important;
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
  <a href="parents.php" class="bg-white text-success fw-bold"><i class="fa fa-users me-2"></i> Parents</a>
  <a href="#"><i class="fa fa-hand-holding-usd me-2"></i> Payments</a>
  <a href="staff.php"><i class="fa fa-briefcase me-2"></i> Staff</a>
  <a href="attendance.php"><i class="fa fa-calendar-check me-2"></i> Attendance</a>
  <a href="subjects.php"><i class="fa fa-book-open me-2"></i> Subjects / Courses</a>
  <a href="exams.php"><i class="fa fa-file-alt me-2"></i> Exams</a>
  <a href="reports.php"><i class="fa fa-chart-line me-2"></i> Reports</a>
  <a href="#"><i class="fa fa-cog me-2"></i> Settings</a>
  <a href="#"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
</div>

<!-- Main content -->
<div class="main-content">
  <h3 class="mb-4 text-success">Parents Management</h3>

  <!-- messages -->
  <?php if (!empty($add_error)): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($add_error) ?></div>
  <?php endif; ?>
  <?php if (!empty($update_error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($update_error) ?></div>
  <?php endif; ?>

  <div class="d-flex justify-content-between mb-3">
    <form class="d-flex" method="get">
      <input type="text" name="search" class="form-control me-2" placeholder="Search parent..." value="<?= htmlspecialchars($search ?? '') ?>">
      <button class="btn btn-success" type="submit">Search</button>
    </form>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Parent</button>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-hover">
        <thead class="table-success">
          <tr>
            <th>#</th>
            <th>Full Name</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Student</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['fullname'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['address'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['student_name'] ?? '—') ?></td>
                <td>
                  <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                  <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this parent?')">Delete</a>
                </td>
              </tr>

              <!-- Edit Modal -->
              <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                  <form method="post" class="modal-content">
                    <div class="modal-header bg-warning text-white">
                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

                      <h5 class="modal-title">Edit Parent</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                      <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($row['fullname'] ?? '') ?>" required>
                      </div>
                      <div class="mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($row['phone'] ?? '') ?>" required>
                      </div>
                      <div class="mb-3">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($row['address'] ?? '') ?>">
                      </div>
                      <div class="mb-3">
                        <label>Student</label>
                        <select name="student_id" class="form-control">
                          <option value="">Select Student</option>
                          <?php
                          // reuse students_result (rewind pointer)
                          $students = $conn->query("SELECT id, fullname FROM students ORDER BY fullname ASC");
                          while ($student = $students->fetch_assoc()) {
                              $selected = ($student['id'] == $row['student_id']) ? 'selected' : '';
                              echo "<option value='".(int)$student['id']."' $selected>".htmlspecialchars($student['fullname'])."</option>";
                          }
                          ?>
                        </select>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" name="update_parent" class="btn btn-warning">Update</button>
                    </div>
                  </form>
                </div>
              </div>

            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center text-muted">No parents found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Add Parent</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Full Name</label>
          <input type="text" name="fullname" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Phone</label>
          <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Address</label>
          <input type="text" name="address" class="form-control">
        </div>
        <div class="mb-3">
          <label>Student</label>
          <select name="student_id" class="form-control">
            <option value="">Select Student</option>
            <?php
            // rewind and use students_result
            $students_result->data_seek(0);
            while ($s = $students_result->fetch_assoc()) {
                echo "<option value='".(int)$s['id']."'>".htmlspecialchars($s['fullname'])."</option>";
            }
            ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_parent" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
