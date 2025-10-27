<?php
session_start();
require_once 'config.php';


$teachers_result = $conn->query("SELECT id, fullname FROM teachers ORDER BY fullname ASC");


if (isset($_POST['add_subject'])) {
    $subject_name = trim($_POST['subject_name'] ?? '');
    $subject_code = trim($_POST['subject_code'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $teacher_id   = intval($_POST['teacher_id'] ?? 0);

    if ($subject_name !== '' && $subject_code !== '') {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name, subject_code, description, teacher_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $subject_name, $subject_code, $description, $teacher_id);
        $stmt->execute();
        header("Location: subjects.php");
        exit();
    } else {
        $add_error = "Please fill all required fields.";
    }
}


if (isset($_POST['update_subject'])) {
    $id           = intval($_POST['id'] ?? 0);
    $subject_name = trim($_POST['subject_name'] ?? '');
    $subject_code = trim($_POST['subject_code'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $teacher_id   = intval($_POST['teacher_id'] ?? 0);

    if ($id > 0 && $subject_name !== '' && $subject_code !== '') {
        $update = $conn->prepare("UPDATE subjects SET subject_name=?, subject_code=?, description=?, teacher_id=? WHERE id=?");
        $update->bind_param("sssii", $subject_name, $subject_code, $description, $teacher_id, $id);
        if ($update->execute()) {
            header("Location: subjects.php");
            exit();
        } else {
            $update_error = "Update failed: " . $conn->error;
        }
    } else {
        $update_error = "Please fill all required fields.";
    }
}


if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        $conn->query("DELETE FROM subjects WHERE id=$id");
        header("Location: subjects.php");
        exit();
    }
}


$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $query = "SELECT s.*, t.fullname AS teacher_name
              FROM subjects s
              LEFT JOIN teachers t ON s.teacher_id = t.id
              WHERE s.subject_name LIKE '%$search%' 
                 OR s.subject_code LIKE '%$search%' 
                 OR t.fullname LIKE '%$search%'
              ORDER BY s.id DESC";
} else {
    $query = "SELECT s.*, t.fullname AS teacher_name
              FROM subjects s
              LEFT JOIN teachers t ON s.teacher_id = t.id
              ORDER BY s.id DESC";
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Subjects Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { min-height: 100vh; display: flex; font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
.sidebar { position: fixed; top:0; left:0; width:250px; height:100vh; background: darkslategrey; color:#fff; overflow-y:auto; padding-bottom:20px; }
.sidebar img { width:80px; display:block; margin:15px auto; border-radius:50%; }
.sidebar h4 { text-align:center; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,.3); padding-bottom:10px; }
.sidebar a { display:block; color:#fff; text-decoration:none; padding:12px 20px; transition: all .15s ease; }
.sidebar a:hover { background:grey; border-left:4px solid #fff; }
.main-content { margin-left:250px; padding:20px; flex-grow:1; }
.card { border:none; border-radius:10px; box-shadow:0 2px 5px rgba(0,0,0,.05); }
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
    <a href="subjects.php" class="bg-white text-success fw-bold"><i class="fa fa-book-open me-2"></i> Subjects / Courses</a>
    <a href="exams.php"><i class="fa fa-file-alt me-2"></i> Exams</a>
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
    <h3 class="mb-4 text-success">Subjects Management</h3>

    <?php if (!empty($add_error)): ?><div class="alert alert-warning"><?= htmlspecialchars($add_error) ?></div><?php endif; ?>
    <?php if (!empty($update_error)): ?><div class="alert alert-danger"><?= htmlspecialchars($update_error) ?></div><?php endif; ?>

    <div class="d-flex justify-content-between mb-3">
        <form class="d-flex" method="get">
            <input type="text" name="search" class="form-control me-2" placeholder="Search subject..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-success" type="submit">Search</button>
        </form>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Subject</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Subject Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Teacher</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['subject_name']) ?></td>
                        <td><?= htmlspecialchars($row['subject_code']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['teacher_name'] ?? '—') ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this subject?')">Delete</a>
                        </td>
                    </tr>

                 
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
                                        <input type="text" name="subject_name" class="form-control" value="<?= htmlspecialchars($row['subject_name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Subject Code</label>
                                        <input type="text" name="subject_code" class="form-control" value="<?= htmlspecialchars($row['subject_code']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($row['description']) ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label>Teacher</label>
                                        <select name="teacher_id" class="form-control">
                                            <option value="">Select Teacher</option>
                                            <?php
                                            $teachers_result->data_seek(0);
                                            while ($t = $teachers_result->fetch_assoc()) {
                                                $selected = ($t['id'] == $row['teacher_id']) ? 'selected' : '';
                                                echo "<option value='".(int)$t['id']."' $selected>".htmlspecialchars($t['fullname'])."</option>";
                                            }
                                            ?>
                                        </select>
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
                    <tr><td colspan="7" class="text-center text-muted">No subjects found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header bg-primary text-white">
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
                <div class="mb-3">
                    <label>Teacher</label>
                    <select name="teacher_id" class="form-control">
                        <option value="">Select Teacher</option>
                        <?php
                        $teachers_result->data_seek(0);
                        while ($t = $teachers_result->fetch_assoc()) {
                            echo "<option value='".(int)$t['id']."'>".htmlspecialchars($t['fullname'])."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_subject" class="btn btn-primary">Save</button>
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
