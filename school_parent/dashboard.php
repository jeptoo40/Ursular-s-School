<?php
session_start();
require_once '../school_admin/config.php';

// Ensure the user is logged in and is a parent
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'parent') {
    header("Location: ../auth/login.php");
    exit;
}

// initialize variables to avoid undefined notices
$success = $error = "";
$parent = ['fullname' => '', 'address' => '', 'phone' => ''];

// parent id from session
$parent_id = intval($_SESSION['id']);

// Fetch parent info (safe)
if ($parent_id > 0) {
    $query = "SELECT fullname, address, phone FROM parents WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $parent_id);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $parent['fullname'] = $row['fullname'] ?? '';
                $parent['address']  = $row['address']  ?? '';
                $parent['phone']    = $row['phone']    ?? '';
            }
        }
        $stmt->close();
    }
}

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // grab inputs safely
    $fullname = trim($_POST['fullname'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($fullname === '' || $phone === '') {
        $error = "Full name and phone are required.";
    } else {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = "UPDATE parents SET fullname=?, address=?, phone=?, password=? WHERE id=?";
            if ($stmt = $conn->prepare($update)) {
                $stmt->bind_param("ssssi", $fullname, $address, $phone, $hashed, $parent_id);
                if ($stmt->execute()) {
                    $success = "Profile updated successfully!";
                } else {
                    $error = "Update failed: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Prepare failed: " . $conn->error;
            }
        } else {
            $update = "UPDATE parents SET fullname=?, address=?, phone=? WHERE id=?";
            if ($stmt = $conn->prepare($update)) {
                $stmt->bind_param("sssi", $fullname, $address, $phone, $parent_id);
                if ($stmt->execute()) {
                    $success = "Profile updated successfully!";
                } else {
                    $error = "Update failed: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Prepare failed: " . $conn->error;
            }
        }
        // refresh $parent values and session
        if ($success) {
            $parent['fullname'] = $fullname;
            $parent['address']  = $address;
            $parent['phone']    = $phone;
            $_SESSION['fullname'] = $fullname;
        }
    }
}

$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$full_name = $_SESSION['fullname'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Parent Dashboard</title>

  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
    }

    /* Sidebar */
    .sidebar {
      background-color: rgb(0, 31, 63);
      color: white;
      min-height: 100vh;
      width: 250px;
      position: fixed;
      top: 0;
      left: 0;
      transition: all 0.3s ease;
      z-index: 1050;
    }
    .sidebar.active { left: 0; }
    .sidebar.collapsed { left: -250px; }

    .sidebar .nav-link {
      color: white;
      font-size: 15px;
      margin: 8px 0;
    }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
      background-color: burlywood;
      border-radius: 8px;
    }
    .logo-section { text-align: center; padding: 20px 0; border-bottom: 1px solid rgba(255,255,255,0.3); }
    .logo-section img { width: 60px; height: 60px; border-radius: 50%; margin-bottom: 10px; }

    /* Topbar */
    .topbar {
      background-color: #fff;
      border-radius: 8px;
      padding: 10px 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-left: 250px;
      position: relative;
      z-index: 1000;
    }

    .hamburger {
      display: none;
      font-size: 1.8rem;
      color: #0d6efd;
      cursor: pointer;
    }

    .main-content { margin-left: 250px; padding: 30px; transition: all 0.3s ease; }

    /* Charts */
    #attendanceChart { height: 250px !important; width: 300px !important; }

    /* Cards style kept from your design */
    .card { border: none; border-radius: 25px; box-shadow: 0 6px 16px rgba(0,0,0,0.08); padding: 2rem; min-height: 180px; color: #fff; transition: all 0.3s ease; }
    .card:hover { transform: scale(1.03); box-shadow: 0 10px 20px rgba(0,0,0,0.12); }
    .card-fee { background: linear-gradient(135deg, #007bff, #00b4d8); }
    .card-balance { background: linear-gradient(135deg, #ff7b00, #ffb700); }
    .card-manage { background: linear-gradient(135deg, #20c997, #198754); }
    .card-performance { background: linear-gradient(135deg, #6f42c1, #b37fff); }
    .card button { background-color: rgba(255,255,255,0.2); color: #fff; border: none; }
    .card button:hover { background-color: rgba(255,255,255,0.35); }

    /* Responsive adjustments */
    @media (max-width: 992px) {
      .sidebar { left: -250px; }
      .sidebar.active { left: 0; }
      .hamburger { display: block; }
      .topbar { margin-left: 0; flex-wrap: wrap; gap: 10px; }
      .main-content { margin-left: 0; padding: 15px; }
      .welcome-text { font-size: 1.2rem; }
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <div class="sidebar d-flex flex-column justify-content-between p-3" id="sidebar">
    <div>
      <div class="logo-section">
        <img src="../logo.jpg" alt="Logo">
        <h5 class="mt-2">St. Ursular's School</h5>
      </div>

      <nav class="nav flex-column mt-4">
        <a href="#" class="nav-link active"><i class="bi bi-house-door"></i> Overview</a>
        <a href="#" class="nav-link"><i class="bi bi-cash-stack"></i> Transactions</a>
        <a href="results.php" class="nav-link"><i class="bi bi-bar-chart-line"></i> Academics</a>
        <a href="events.php" class="nav-link"><i class="bi bi-calendar-event"></i> Upcoming Events</a>
        <a href="#" class="nav-link"><i class='bx bx-message-square-edit'></i> Teacher Remarks</a>
        <a href="#" data-bs-toggle="modal" data-bs-target="#settingsModal" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
      </nav>
    </div>

    <div>
      <a href="#" class="nav-link"><i class="bi bi-question-circle"></i> Help</a>
      <a href="../auth/logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>

  <!-- Topbar -->
  <div class="topbar d-flex align-items-center justify-content-between px-4">
    <div class="d-flex align-items-center gap-3">
      <!-- hamburger toggle -->
      <i class="bi bi-list hamburger" id="toggleSidebar" role="button" aria-label="Toggle menu"></i>
      <div class="dashboard-overview d-flex align-items-center gap-2">
        <i class="bi bi-grid-fill text-success fs-4"></i>
        <h3 class="mb-0">Overview</h3>
      </div>
    </div>

    <div class="text-center flex-grow-1">
      <h3 class="welcome-text mb-0">Welcome, <?php echo htmlspecialchars($full_name); ?> 👋</h3>
      <p class="subtitle mb-0">Know what’s happening with your child at school.</p>
    </div>

    <div class="user-role d-flex align-items-center gap-2">
      <i class="bi bi-person-fill text-success"></i>
      <p class="mb-0">Parent</p>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="row g-4 mt-4">
      <div class="col-md-3 col-6">
        <div class="card card-fee p-3">
          <h6>Total Fee</h6>
          <h4>Ksh 50,000</h4>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card card-balance p-3">
          <h6>Total Balance</h6>
          <h4>Ksh 15,000</h4>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card card-manage p-3">
          <h6>Manage Fee</h6>
          <button class="btn btn-sm btn-light mt-2">View Details</button>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card card-performance p-3">
          <h6>Performance Rate</h6>
          <h4>82%</h4>
        </div>
      </div>
    </div>

    <div class="card mt-4 p-4">
      <h5>Fee Payment Overview</h5>
      <canvas id="feeChart" height="250"></canvas>
    </div>

    <div class="row mt-4">
      <div class="col-md-8">
        <div class="card p-4">
          <h5 class="text-success mb-3 d-flex align-items-center">
            <i class="bi bi-calendar-event-fill me-2 text-success fs-5"></i>
            Upcoming School Events
          </h5>

          <?php
          $eventQuery = "
            SELECT title, start_date, end_date 
            FROM events 
            WHERE end_date >= CURDATE() 
            ORDER BY start_date ASC 
            LIMIT 5
          ";
          $eventResult = $conn->query($eventQuery);
          if ($eventResult && $eventResult->num_rows > 0): ?>
            <ul class="list-group list-group-flush">
              <?php while ($event = $eventResult->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <strong><?= htmlspecialchars($event['title']); ?></strong><br>
                    <small class="text-muted">
                      <?= date("M d, Y", strtotime($event['start_date'])); ?> 
                      → <?= date("M d, Y", strtotime($event['end_date'])); ?>
                    </small>
                  </div>
                  <i class="bi bi-calendar-week text-success fs-5"></i>
                </li>
              <?php endwhile; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted mb-0">No upcoming events found.</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-3">
          <h5 class="text-success"><i class="bi bi-person-check-fill me-2"></i>Attendance</h5>
          <canvas id="attendanceChart" height="100" style="max-width: 100%; height: 100px;"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Settings Modal -->
  <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 shadow-lg">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="settingsModalLabel">Profile Settings</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
          <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="POST" novalidate>
            <div class="mb-3">
              <label class="form-label fw-bold">Full Name</label>
              <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($parent['fullname'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Address</label>
              <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($parent['address'] ?? '') ?>">
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Phone</label>
              <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($parent['phone'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">New Password (optional)</label>
              <input type="password" name="password" class="form-control" placeholder="Enter new password if you want to change it">
            </div>

            <button type="submit" class="btn btn-success w-100 fw-bold">Save Changes</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    // Sidebar toggle for mobile + icon toggle to X
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        // toggle icon classes
        toggleBtn.classList.toggle('bi-list');
        toggleBtn.classList.toggle('bi-x');
      });
    }

    // Fee Chart
    const ctx1 = document.getElementById('feeChart');
    if (ctx1) {
      new Chart(ctx1, {
        type: 'line',
        data: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
          datasets: [{
            label: 'Fees Paid',
            data: [12000, 15000, 10000, 17000, 13000, 18000],
            borderColor: '#0d6efd',
            fill: false,
            tension: 0.3
          }]
        }
      });
    }

    // Attendance Chart
    const ctx2 = document.getElementById('attendanceChart');
    if (ctx2) {
      new Chart(ctx2, {
        type: 'pie',
        data: {
          labels: ['Present', 'Absent'],
          datasets: [{
            data: [85, 15],
            backgroundColor: ['#0d6efd', '#dc3545']
          }]
        }
      });
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
