<?php
require_once 'config.php'; // database connection

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ---------- Handle Add ----------
if (isset($_POST['add_staff'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $role     = trim($_POST['role'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $hire_date= trim($_POST['hire_date'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    if ($fullname !== '' && $phone !== '') {
        $stmt = $conn->prepare("INSERT INTO staff (fullname, role, phone, hire_date, email, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $fullname, $role, $phone, $hire_date, $email, $address);
        $stmt->execute();
        header("Location: staff.php");
        exit();
    } else {
        $add_error = "Please fill in required fields.";
    }
}

// ---------- Handle Update ----------
if (isset($_POST['update_staff'])) {
    $id       = intval($_POST['id'] ?? 0);
    $fullname = trim($_POST['fullname'] ?? '');
    $role     = trim($_POST['role'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $hire_date= trim($_POST['hire_date'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    if ($id > 0) {
        $update = $conn->prepare("UPDATE staff SET fullname=?, role=?, phone=?, hire_date=?, email=?, address=? WHERE id=?");
        if ($update) {
            $update->bind_param("ssssssi", $fullname, $role, $phone, $hire_date, $email, $address, $id);
            if ($update->execute()) {
                header("Location: staff.php");
                exit();
            } else {
                $update_error = "Execute failed: " . $update->error;
            }
        } else {
            $update_error = "Prepare failed: " . $conn->error;
        }
    } else {
        $update_error = "Invalid staff ID.";
    }
}

// ---------- Handle Delete ----------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        $conn->query("DELETE FROM staff WHERE id=$id");
        header("Location: staff.php");
        exit();
    }
}

// ---------- Handle Search ----------
$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $query = "SELECT * FROM staff 
              WHERE fullname LIKE '%$search%' 
                 OR phone LIKE '%$search%' 
                 OR role LIKE '%$search%' 
                 OR email LIKE '%$search%' 
                 OR address LIKE '%$search%'
              ORDER BY id DESC";
} else {
    $query = "SELECT * FROM staff ORDER BY id DESC";
}
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Management</title>
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

  /* --- Critical Fix for Non-editable Modal --- */
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


<div class="sidebar">
  <img src="../logo.jpg" alt="Logo">
  <h4>Admin Panel</h4>
  <a href="dashboard.php"><i class="fa fa-home me-2"></i> Dashboard</a>
  <a href="students.php"><i class="fa fa-user-graduate me-2"></i> Students</a>
  <a href="teachers.php"><i class="fa fa-chalkboard-teacher me-2"></i> Teachers</a>
  <a href="parents.php"><i class="fa fa-users me-2"></i> Parents</a>
  <a href="staff.php" class="bg-white text-success fw-bold"><i class="fa fa-briefcase me-2"></i> Staff</a>
  <a href="attendance.php"><i class="fa fa-calendar-check me-2"></i> Attendance</a>
  <a href="subjects.php"><i class="fa fa-book-open me-2"></i> Subjects / Courses</a>
  <a href="#"><i class="fa fa-file-alt me-2"></i> Exams</a>
  <a href="#"><i class="fa fa-chart-line me-2"></i> Reports</a>
  <a href="#"><i class="fa fa-hand-holding-usd me-2"></i> Payments</a>
  <a href="#"><i class="fa fa-cog me-2"></i> Settings</a>
  <a href="#"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
</div>

<!-- Main content -->
<div class="main-content">
<h3 class="mb-4 text-success">Staff Management</h3>

<?php if (!empty($add_error)): ?>
  <div class="alert alert-warning"><?= htmlspecialchars($add_error) ?></div>
<?php endif; ?>
<?php if (!empty($update_error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($update_error) ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between mb-3">
  <form class="d-flex" method="get">
    <input type="text" name="search" class="form-control me-2" placeholder="Search staff..." value="<?= htmlspecialchars($search ?? '') ?>">
    <button class="btn btn-success" type="submit">Search</button>
  </form>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Staff</button>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <table class="table table-bordered table-hover">
      <thead class="table-success">
        <tr>
          <th>#</th>
          <th>Full Name</th>
          <th>Role</th>
          <th>Phone</th>
          <th>Hire Date</th>
          <th>Email</th>
          <th>Address</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['fullname'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['role'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['hire_date'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['address'] ?? '') ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
              <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this staff member?')">Delete</a>
            </td>
          </tr>

          <!-- Edit Modal -->
          <div class="modal fade" id="editModal<?= (int)$row['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <form method="post" class="modal-content">
                <div class="modal-header bg-warning text-white">
                  <h5 class="modal-title">Edit Staff</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">
                  <div class="mb-3">
                    <label>Full Name</label>
                    <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($row['fullname'] ?? '') ?>" required>
                  </div>
                  <div class="mb-3">
                    <label>Role</label>
                    <input type="text" name="role" class="form-control" value="<?= htmlspecialchars($row['role'] ?? '') ?>">
                  </div>
                  <div class="mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($row['phone'] ?? '') ?>">
                  </div>
                  <div class="mb-3">
                    <label>Hire Date</label>
                    <input type="date" name="hire_date" class="form-control" value="<?= htmlspecialchars($row['hire_date'] ?? '') ?>">
                  </div>
                  <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email'] ?? '') ?>">
                  </div>
                  <div class="mb-3">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($row['address'] ?? '') ?>">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="update_staff" class="btn btn-warning">Update</button>
                </div>
              </form>
            </div>
          </div>

          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center text-muted">No staff found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Add Staff</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Full Name</label>
          <input type="text" name="fullname" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Role</label>
          <input type="text" name="role" class="form-control">
        </div>
        <div class="mb-3">
          <label>Phone</label>
          <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Hire Date</label>
          <input type="date" name="hire_date" class="form-control">
        </div>
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3">
          <label>Address</label>
          <input type="text" name="address" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_staff" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
