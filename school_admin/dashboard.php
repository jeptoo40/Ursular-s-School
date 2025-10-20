<?php
session_start();
require_once 'config.php';

// Define a fallback admin name
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';


// Fetch totals from DB
$students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'] ?? 0;
$teachers = $conn->query("SELECT COUNT(*) AS total FROM teachers")->fetch_assoc()['total'] ?? 0;
$payments = $conn->query("SELECT COUNT(*) AS total FROM payments")->fetch_assoc()['total'] ?? 0;
$parents = $conn->query("SELECT COUNT(*) AS total FROM parents")->fetch_assoc()['total'] ?? 0;
$staff = $conn->query("SELECT COUNT(*) AS total FROM staff")->fetch_assoc()['total'] ?? 0;
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> St-Ursular's School - Admin Dashboard</title>


<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>


  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <style>
    body {
      min-height: 100vh;
      display: flex;
      font-family: 'Segoe UI', sans-serif;
    }
    .sidebar {
      width: 250px;
      background-color: #198754;

      color: #fff;
      flex-shrink: 0;
    }
    .sidebar h4 {
      text-align: center;
      padding: 1.2rem 0;
      border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    .sidebar a {
      display: block;
      color: #fff;
      text-decoration: none;
      padding: 12px 20px;
      border-left: 4px solid transparent;
      transition: all 0.2s ease;
    }
    .sidebar a:hover {
      background-color: rgba(255,255,255,0.1);
      border-left: 4px solid #fff;
    }
    .main-content {
      flex-grow: 1;
      background-color: #f8f9fa;
      padding: 20px;
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
    <h4>Admin Panel</h4>
   <!-- Sidebar Menu -->
<a href="#"><i class="fa fa-home me-2"></i> Dashboard</a>
<a href="#"><i class="fa fa-user-graduate me-2"></i> Students</a>
<a href="#"><i class="fa fa-chalkboard-teacher me-2"></i> Teachers</a>
<a href="#"><i class="fa fa-users me-2"></i> Parents</a>
<a href="#"><i class="fa fa-hand-holding-usd me-2"></i> Payments</a>
<a href="#"><i class="fa fa-briefcase me-2"></i> Staff</a>
<a href="#"><i class="fa fa-calendar-check me-2"></i> Attendance</a>
<a href="#"><i class="fa fa-book-open me-2"></i> Subjects / Courses</a>
<a href="#"><i class="fa fa-file-alt me-2"></i> Exams</a>
<a href="#"><i class="fa fa-chart-line me-2"></i> Reports</a>
<a href="#"><i class="fa fa-cog me-2"></i> Settings</a>
<a href="#"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>

  </div>

  <!-- Main content -->
  <div class="main-content">
    <!-- Topbar -->
    <div class="topbar">
      <h5>Dashboard Overview</h5>
      <div>
        <i class="fa fa-user-circle me-2 text-success"></i>
        <span><?php echo htmlspecialchars($admin_name); ?></span>
      </div>
    </div>

    <!-- Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-4 col-xl-2">
        <div class="card text-center p-3">
          <i class="fa fa-user-graduate fa-2x text-success mb-2"></i>
          <h5 class="card-title">Students</h5>
        <h3><?php echo $students; ?></h3>
        </div>
      </div>
      <div class="col-md-4 col-xl-2">
        <div class="card text-center p-3">
          <i class="fa fa-chalkboard-teacher fa-2x text-primary mb-2"></i>
          <h5 class="card-title">Teachers</h5>
        <h3><?php echo $teachers; ?></h3>
        </div>
      </div>
      <div class="col-md-4 col-xl-2">
        <div class="card text-center p-3">
          <i class="fa fa-money-bill fa-2x text-warning mb-2"></i>
          <h5 class="card-title">Payments</h5>
        <h3><?php echo $payments; ?></h3>
        </div>
      </div>
      <div class="col-md-4 col-xl-2">
        <div class="card text-center p-3">
          <i class="fa fa-user-friends fa-2x text-danger mb-2"></i>
          <h5 class="card-title">Parents</h5>
        <h3><?php echo $parents; ?></h3>
        </div>
      </div>
      <div class="col-md-4 col-xl-2">
        <div class="card text-center p-3">
          <i class="fa fa-briefcase fa-2x text-secondary mb-2"></i>
          <h5 class="card-title">Staff</h5>
        <h3><?php echo $staff; ?></h3>
        </div>
      </div>
    </div>












<!-- Analytics and Events Section -->
<div class="row align-items-stretch">
  <!-- Left: Analytics Overview -->
  <div class="col-md-8 mb-4 d-flex">
    <div class="card p-4 flex-fill shadow-sm" style="min-height: 500px;">
      <h5 class="mb-3">Analytics Overview</h5>
      <canvas id="statsChart" style="height: 380px;"></canvas>
    </div>
  </div>

  <!-- Right: Upcoming Events -->
  <div class="col-md-4 mb-4 d-flex">
    <div class="card shadow-sm flex-fill" style="min-height: 500px;">
      <div class="card-header bg-primary text-white">Upcoming Events</div>
      <div class="card-body">
        <div id="calendar" style="max-height: 420px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Below Both: Best Performing Student -->
<div class="row mt-4">
  <div class="col-md-12">
    <div class="card p-4 shadow-sm">
      <h5 class="mb-3">🏆 Performance Highlights</h5>
      <?php
      // Fetch best performing student (you can later join this with grades table)
      $bestStudentQuery = $conn->query("SELECT fullname, class FROM students ORDER BY id ASC LIMIT 1");

      if ($bestStudentQuery && $bestStudentQuery->num_rows > 0) {
        $student = $bestStudentQuery->fetch_assoc();
        $performance = 95; // static percentage for now
        $subject = "Mathematics"; // example most performing subject (can be dynamic)
        
        echo "
          <div class='mb-3'>
            <p class='fs-5 mb-1 text-success'><strong>Best Student:</strong> {$student['fullname']}</p>
            <p class='mb-1'><strong>Class:</strong> {$student['class']}</p>
            <p class='mb-1'><strong>Performance:</strong> <span class='text-success'>{$performance}%</span></p>
          </div>
          <hr>
          <div>
            <p class='fs-5 mb-1 text-primary'><strong>Top Subject:</strong> {$subject}</p>
          </div>
        ";
      } else {
        echo "<p class='text-muted'>No performance data available</p>";
      }
      ?>
    </div>
  </div>
</div>






  <!-- Bootstrap JS + Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    const ctx = document.getElementById('statsChart').getContext('2d');
    const statsChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Students', 'Teachers', 'Payments', 'Parents', 'Staff'],
        datasets: [{
          label: 'Totals',
          data: [<?php echo $students; ?>, <?php echo $teachers; ?>, <?php echo $payments; ?>, <?php echo $parents; ?>, <?php echo $staff; ?>],


          backgroundColor: ['#198754','#0d6efd','#ffc107','#dc3545','#6c757d']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: {
            display: true,
            text: 'Current Overview'
          }
        }
      }
    });
  </script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 500,
    events: 'events.php', // Fetch events from backend
    eventColor: '#198754',
    displayEventTime: false,
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,listWeek'
    }
  });

  calendar.render();
});
</script>

</body>
</html>
