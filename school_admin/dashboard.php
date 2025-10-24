<?php
session_start();
require_once 'config.php';

// Define a fallback admin name
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';



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


  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-doughnutlabel"></script>


<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>



  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
body {
  min-height: 100vh;
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
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
  background-color: grey;
  border-left: 4px solid #fff;
}


.main-content {
  margin-left: 250px;
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


  <div class="sidebar" style position ="fixed">
  <img src="logo.png" alt="Logo" style="width:150px; height:auto; border-radius:8px; margin-bottom:10px;">


  <hr style="border-top: 2px solid rgba(255,255,255,0.3); margin: 0 20px 10px;" style color="#fff">
    
<a href="dashboard.php"><i class="fa fa-home me-2"></i> Dashboard</a>
<a href="students.php"><i class="fa fa-user-graduate me-2"></i> Students</a>
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
      <h5>Admin Dashboard Overview</h5>
      <div>
        <i class="fa fa-user-circle me-2 text-success"></i>
        <span><?php echo htmlspecialchars($admin_name); ?></span>
      </div>
    </div>

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













<div class="row align-items-stretch">

  <div class="col-md-8 mb-4 d-flex">
    <div class="card p-4 flex-fill shadow-sm" style="min-height: 500px;">
      <h5 class="mb-3">Analytics Overview</h5>
      <canvas id="statsChart" style="height: 380px;"></canvas>
    </div>
  </div>


  <div class="col-md-4 mb-4 d-flex">
    <div class="card shadow-sm flex-fill" style="min-height: 500px;">
      <div class="card-header bg-primary text-white">Upcoming Events</div>
      <div class="card-body">
        <div id="calendar" style="max-height: 420px;"></div>
      </div>
    </div>
  </div>
</div>


<div class="row mt-4">
 
  <div class="col-md-6 mb-4">
    <div class="card p-4 shadow-sm h-100">
      <h5 class="mb-3">🏆 Performance Highlights</h5>
      <?php
      $bestStudentQuery = $conn->query("SELECT fullname, class FROM students ORDER BY id ASC LIMIT 1");

      if ($bestStudentQuery && $bestStudentQuery->num_rows > 0) {
        $student = $bestStudentQuery->fetch_assoc();
        $performance = 95; 
        $subject = "Mathematics";
        
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

  <!-- Right: Attendance Doughnut Chart -->
<div class="col-md-4 mb-4 d-flex">
  <div  style="max-width: 300px; margin-left: auto;">
   
    <canvas id="attendanceChart" width="200" height="200"></canvas>
  </div>
</div>


</div>



 
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
    events: 'events.php', // Fetching events from backend
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
<script>
  const ctxAttendance = document.getElementById('attendanceChart').getContext('2d');

  const attendanceData = {
    Present: 80,
    Absent: 15,
    Late: 5
  };

  const total = Object.values(attendanceData).reduce((acc, value) => acc + value, 0);
  const percentage = ((attendanceData.Present / total) * 100).toFixed(2);

  new Chart(ctxAttendance, {
    type: 'doughnut',
    data: {
      labels: Object.keys(attendanceData),
      datasets: [{
        data: Object.values(attendanceData),
        backgroundColor: ['#198754', '#dc3545', '#ffc107'],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' },
        tooltip: {
          callbacks: {
            label: function(context) {
              const label = context.label;
              const value = context.raw;
              const percent = ((value / total) * 100).toFixed(2);
              return `${label}: ${value} (${percent}%)`;
            }
          }
        },
        doughnutlabel: {
          labels: [{
            text: `${percentage}%`,
            font: { size: 30, weight: 'bold' },
            color: '#000'
          }]
        }
      }
    }
  });
</script>

 
</body>
</html>
