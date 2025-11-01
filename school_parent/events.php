<?php
session_start();
require_once '../school_admin/config.php';

// Ensure the user is logged in and is a parent
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'parent') {
    header("Location: ../auth/login.php");
    exit;
}

$parent_id = $_SESSION['id'];
$full_name = $_SESSION['fullname'];

// Initialize feedback messages
$success = $error = "";

// Fetch parent info for settings modal
$parent = ['fullname' => '', 'address' => '', 'phone' => ''];
$query = "SELECT fullname, address, phone FROM parents WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $parent = $result->fetch_assoc();
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if ($password) {
        $update = "UPDATE parents SET fullname=?, address=?, phone=?, password=? WHERE id=?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("ssssi", $fullname, $address, $phone, $password, $parent_id);
    } else {
        $update = "UPDATE parents SET fullname=?, address=?, phone=? WHERE id=?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("sssi", $fullname, $address, $phone, $parent_id);
    }

    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        $_SESSION['fullname'] = $fullname;
        $parent['fullname'] = $fullname;
        $parent['address'] = $address;
        $parent['phone'] = $phone;
    } else {
        $error = "Something went wrong. Please try again.";
    }
}

// Check if 'description' column exists for events
$checkColumn = $conn->query("SHOW COLUMNS FROM events LIKE 'description'");
$hasDescription = ($checkColumn && $checkColumn->num_rows > 0);

// Fetch events
if ($hasDescription) {
    $eventQuery = "SELECT id, title, start_date, end_date, description FROM events WHERE end_date >= CURDATE() ORDER BY start_date ASC";
} else {
    $eventQuery = "SELECT id, title, start_date, end_date FROM events WHERE end_date >= CURDATE() ORDER BY start_date ASC";
}
$eventResult = $conn->query($eventQuery);
if (!$eventResult) {
    die("Error fetching events: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upcoming Events | Parent Dashboard</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
    }
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
    .sidebar .nav-link {
      color: white;
      font-size: 15px;
      margin: 8px 0;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background-color: burlywood;
      border-radius: 8px;
    }
    .logo-section {
      text-align: center;
      padding: 20px 0;
      border-bottom: 1px solid rgba(255,255,255,0.3);
    }
    .logo-section img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      margin-bottom: 10px;
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
    .main-content {
      margin-left: 250px;
      padding: 30px;
      transition: all 0.3s ease;
    }
    @media (max-width: 992px) {
      .sidebar { left: -250px; }
      .sidebar.active { left: 0; }
      .hamburger { display: block; }
      .topbar { margin-left: 0; flex-wrap: wrap; gap: 10px; }
      .main-content { margin-left: 0; padding: 15px; }
    }
    .event-card {
      border-left: 6px solid #198754;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 15px;
      background-color: #fff;
      box-shadow: 0 3px 8px rgba(0,0,0,0.05);
      transition: transform 0.2s ease;
    }
    .event-card:hover { transform: scale(1.02); }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar d-flex flex-column justify-content-between p-3" id="sidebar">
    <div>
      <div class="logo-section">
        <img src="../logo.jpg" alt="Logo">
        <h5 class="mt-2">St. Ursula's School</h5>
      </div>

      <nav class="nav flex-column mt-4">
        <a href="dashboard.php" class="nav-link"><i class="bi bi-house-door"></i> Overview</a>
        <a href="#" class="nav-link"><i class="bi bi-cash-stack"></i> Transactions</a>
        <a href="results.php" class="nav-link"><i class="bi bi-bar-chart-line"></i> Academics</a>
        <a href="#" class="nav-link"><i class='bx bx-message-square-edit'></i> Teacher Remarks</a>
        <a href="events.php" class="nav-link active"><i class="bi bi-calendar-event"></i> Upcoming Events</a>
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
      <i class="bi bi-list hamburger" id="toggleSidebar"></i>
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-calendar-event-fill text-success fs-4"></i>
        <h3 class="mb-0">Upcoming Events</h3>
      </div>
    </div>

    <div class="text-center flex-grow-1">
      <h3 class="welcome-text mb-0">Welcome, <?= htmlspecialchars($full_name); ?> 👋</h3>
      <p class="subtitle mb-0">Stay updated with school activities.</p>
    </div>

    <div class="user-role d-flex align-items-center gap-2">
      <i class="bi bi-person-fill text-success"></i>
      <p class="mb-0">Parent</p>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container">
      <?php if ($eventResult->num_rows > 0): ?>
        <?php while ($event = $eventResult->fetch_assoc()): ?>
          <div class="event-card">
            <h5 class="text-dark mb-1"><i class="bi bi-calendar3 text-success me-2"></i><?= htmlspecialchars($event['title']); ?></h5>
            <p class="text-muted mb-2">
              <i class="bi bi-clock-history me-1"></i>
              <?= date("M d, Y", strtotime($event['start_date'])); ?> → <?= date("M d, Y", strtotime($event['end_date'])); ?>
            </p>
            <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#eventModal<?= $event['id']; ?>">
              <i class="bi bi-eye"></i> View Details
            </button>
          </div>

          <!-- Event Modal -->
          <div class="modal fade" id="eventModal<?= $event['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header bg-success text-white">
                  <h5 class="modal-title"><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($event['title']); ?></h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <p><strong>Start Date:</strong> <?= date("M d, Y", strtotime($event['start_date'])); ?></p>
                  <p><strong>End Date:</strong> <?= date("M d, Y", strtotime($event['end_date'])); ?></p>
                  <?php if ($hasDescription): ?>
                    <p><strong>Description:</strong></p>
                    <p><?= nl2br(htmlspecialchars($event['description'])); ?></p>
                  <?php else: ?>
                    <p><em>No description available for this event.</em></p>
                  <?php endif; ?>
                </div>
                <div class="modal-footer">
                  <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="alert alert-warning text-center">No upcoming events found.</div>
      <?php endif; ?>
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

  <script>
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('active');
      if (toggleBtn.classList.contains('bi-list')) {
        toggleBtn.classList.replace('bi-list', 'bi-x-lg');
      } else {
        toggleBtn.classList.replace('bi-x-lg', 'bi-list');
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
