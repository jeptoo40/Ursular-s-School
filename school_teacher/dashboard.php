<?php
session_start();
require_once '../school_admin/config.php';

// Ensure the user is logged in and is a teacher
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

$teacher_id    = $_SESSION['id'];
$teacher_name  = $_SESSION['fullname'] ?? 'Teacher';
$profile_image = $_SESSION['profile_image'] ?? '../assets/img/default-avatar.png';

/* ---------- HELPER ---------- */
function table_exists($conn, $table_name) {
    if (!$conn) return false;
    $table_name = $conn->real_escape_string($table_name);
    $result = $conn->query("SHOW TABLES LIKE '{$table_name}'");
    return ($result && $result->num_rows > 0);
}

/* ---------- DEFAULT VALUES ---------- */
$students = 0;
$total_classes = 0;
$attendance = 0;
$assignments_due = 0;
$classes = [];

/* ---------- STUDENTS ---------- */
if (table_exists($conn, 'students')) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM students");
    if ($result) {
        $row = $result->fetch_assoc();
        $students = intval($row['total'] ?? 0);
        $result->free();
    }
}

/* ---------- CLASSES ---------- */
if (table_exists($conn, 'classes')) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM classes");
    if ($result) {
        $row = $result->fetch_assoc();
        $total_classes = intval($row['total'] ?? 0);
        $result->free();
    }
    $result = $conn->query("SELECT * FROM classes LIMIT 10");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $classes[] = [
                'name' => $row['name'] ?? $row['class_name'] ?? 'Unnamed Class',
                'students' => $row['student_count'] ?? rand(20, 40)
            ];
        }
        $result->free();
    }
}

/* ---------- ATTENDANCE ---------- */
if (table_exists($conn, 'attendance')) {
    $result = $conn->query("
        SELECT 
          (SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) / COUNT(*)) * 100 AS avg_att
        FROM attendance
    ");
    if ($result) {
        $row = $result->fetch_assoc();
        $attendance = round($row['avg_att'] ?? 0);
        $result->free();
    }
}

/* ---------- ASSIGNMENTS ---------- */
if (table_exists($conn, 'assignments')) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM assignments WHERE due_date >= CURDATE()");
    if ($result) {
        $row = $result->fetch_assoc();
        $assignments_due = intval($row['total'] ?? 0);
        $result->free();
    }
}

/* ---------- FALLBACK DATA ---------- */
if (empty($classes)) {
    $classes = [
        ['name' => 'Mathematics - Grade 10', 'students' => 34],
        ['name' => 'Physics - Grade 11', 'students' => 28],
        ['name' => 'Computer Studies - Grade 12', 'students' => 22],
    ];
}

$upcoming_events = [
    ['date' => '2025-11-10', 'title' => 'Parent Meeting'],
    ['date' => '2025-11-15', 'title' => 'Sports Day'],
    ['date' => '2025-11-20', 'title' => 'Exam Prep Week'],
];

$messages = [
    ['from' => 'Admin', 'snippet' => 'Reminder: submit term report templates by Friday.'],
    ['from' => 'HOD', 'snippet' => 'Please update attendance for Class 10A.'],
    ['from' => 'ICT', 'snippet' => 'New software update scheduled for next week.'],
];



// ===== Exams Fetch =====
$exams = [];
if (table_exists($conn, 'exams')) {
    $result = $conn->query("SELECT exam_name, class, exam_date FROM exams ORDER BY exam_date DESC LIMIT 5");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $exams[] = [
                'exam_name' => $row['exam_name'],
                'class' => $row['class'],
                'exam_date' => date('M j, Y', strtotime($row['exam_date']))
            ];
        }
        $result->free();
    }
}

// Fallback sample data if table empty
if (empty($exams)) {
    $exams = [
        ['exam_name' => 'Midterm Mathematics', 'class' => 'Grade 10', 'exam_date' => 'Nov 12, 2025'],
        ['exam_name' => 'Science Final', 'class' => 'Grade 11', 'exam_date' => 'Nov 20, 2025'],
        ['exam_name' => 'ICT Practical', 'class' => 'Grade 12', 'exam_date' => 'Nov 25, 2025']
    ];
}











?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Teacher Dashboard</title>

  <!-- Fonts / CSS -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root{
      --bg: #f1f5f9; --muted:#64748b; --card:#fff;
      --primary:#2563eb; --success:#10b981; --warn:#f59e0b; --accent:#7c3aed;
    }
    body { background:var(--bg); font-family:'Poppins',sans-serif; color:#0f172a; }

    /* Sidebar Styling */
    .sidebar {
      width: 230px;
      height: 100vh;
      background-color: #1e293b;
      color: #fff;
      display: flex;
      flex-direction: column;
      align-items: start;
      padding-top: 20px;
      position: fixed;
      left: 0;
      top: 0;
    }
    .sidebar .logo {
      text-align: center;
      width: 100%;
      margin-bottom: 25px;
    }
    .sidebar .logo img {
      width: 70px; height: 70px;
      border-radius: 50%;
      border: 2px solid #fff;
      object-fit: cover;
    }
    .nav { width: 100%; }
    .nav a {
      display: flex; align-items: center; gap: 10px;
      color: #cbd5e1; text-decoration: none;
      padding: 10px 20px; font-size: 15px;
      transition: all 0.3s ease;
    }
    .nav a:hover, .nav a.active {
      background-color: #334155;
      color: #fff;
      border-left: 4px solid #38bdf8;
    }
    .nav a i { width: 20px; text-align: center; }
    .nav .logout {
      color: #ff6b6b; margin-top: auto;
    }
    .nav .logout:hover { background-color: rgba(255,107,107,0.2); }

    /* Main Section */
    .main {
      margin-left: 230px; /* Matches sidebar width */
      padding: 24px;
      transition: margin-left 0.3s ease;
    }

    .topbar {
      display:flex; align-items:center; justify-content:space-between; gap:12px;
    }
    .search-input{ width:420px; max-width:calc(100% - 120px); }
    .profile{ display:flex; align-items:center; gap:10px; }
    .profile img{ width:44px;height:44px;border-radius:50%; object-fit:cover; border:2px solid #fff; }

    .welcome{ margin-top:18px; background:var(--card); border-radius:12px; padding:18px; box-shadow:0 6px 18px rgba(15,23,42,0.06); }

    .stats-grid{ margin-top:18px; display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
    .stat{ background:var(--card); border-radius:12px; padding:16px; display:flex; align-items:center; gap:12px; box-shadow:0 6px 12px rgba(2,6,23,0.04); transition:transform .15s, box-shadow .15s; }
    .stat:hover{ transform:translateY(-6px); box-shadow:0 10px 20px rgba(2,6,23,0.08); }
    .stat .icon{ width:54px;height:54px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;}
    .icon.primary{ background:linear-gradient(135deg,#2563eb,#3b82f6);}
    .icon.success{ background:linear-gradient(135deg,#10b981,#34d399);}
    .icon.warn{ background:linear-gradient(135deg,#f59e0b,#fbdb4a);}
    .icon.accent{ background:linear-gradient(135deg,#7c3aed,#a855f7);}

    .row-grid{ margin-top:18px; display:grid; grid-template-columns:2fr 1fr; gap:16px; }
    .card-panel{ background:var(--card); border-radius:12px; padding:16px; box-shadow:0 8px 18px rgba(2,6,23,0.04); }
    .classes-list{ display:flex; flex-direction:column; gap:10px; margin-top:12px; }
    .class-item{ display:flex; justify-content:space-between; padding:12px; border-radius:10px; background:#fbfdff; border:1px solid rgba(2,6,23,0.03); }
    .list-compact{ margin-top:10px; display:flex; flex-direction:column; gap:8px; }
    .msg{ padding:10px; border-radius:10px; background:#fcfdff; border:1px solid rgba(2,6,23,0.03); }

    @media (max-width:992px) {
      .stats-grid{ grid-template-columns:repeat(2,1fr); }
      .row-grid{ grid-template-columns:1fr; }
      .search-input{ width:100%; }
      .sidebar{ width:72px; }
      .main{ margin-left:80px; padding:16px; }
    }


    .nav {
  display: flex;
  flex-direction: column;
  height: 100%;
}
.nav .mt-auto {
  margin-top: auto;
}



/* Special styling for the Exams panel */
.exams-panel {
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: #fff;
  border: none;
  box-shadow: 0 8px 16px rgba(30, 58, 138, 0.3);
}

.exams-panel h6 {
  color: #e2e8f0;
  font-weight: 600;
}

.exam-item {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #fff;
}

.exam-item:hover {
  background: rgba(255, 255, 255, 0.12);
  transform: translateY(-3px);
  transition: all 0.2s ease;
}

.exam-item a.btn {
  color: #fff;
  border-color: #fff;
}

.exam-item a.btn:hover {
  background: #fff;
  color: #1e3a8a;
}


  </style>
</head>

<body>
<!-- Sidebar -->
<aside class="sidebar">
  <div class="logo" title="School Logo">
    <img src="../logo.jpg" alt="Logo">
  </div>
  <nav class="nav">
    <a href="#" class="active"><i class="fa fa-home fa-lg"></i>Dashboard</a>
    <a href="#"><i class="fa fa-user-graduate fa-lg"></i>Students</a>
    <a href="#"><i class="fa fa-chalkboard fa-lg"></i>Classes</a>
    <a href="#"><i class="fa fa-folder-open fa-lg"></i>Records</a>

    <a href="#"><i class="fa fa-comment-dots fa-lg"></i><span>Staffroom Chats</span></a>


    <a href="#"><i class="fa fa-cog fa-lg"></i><span>Settings</span></a>


<!-- Bottom section -->
<div class="mt-auto w-100">
   
    <a href="../auth/logout.php" class="logout"><i class="fa fa-sign-out-alt fa-lg"></i><span>Logout</span></a>
  </div>
</nav>

    
  </nav>
</aside>

<!-- Main -->
<main class="main">
  <div class="topbar">
    <div class="d-flex align-items-center gap-3">
      <div class="search-input">
        <div class="input-group">
          <input class="form-control" placeholder="Search classes, documents, or events..." />
          <button class="btn btn-outline-secondary"><i class="fa fa-search"></i></button>
        </div>
      </div>
      <div class="d-none d-md-block text-muted">|</div>
      <div class="text-muted d-none d-md-block">Today: <?php echo date('D, M j, Y'); ?></div>
    </div>

    <div class="profile">
      <div class="text-end me-2">
        <div style="font-weight:600;"><?php echo htmlspecialchars($teacher_name); ?></div>
        <div class="text-muted" style="font-size:12px;">Teacher</div>
      </div>
      <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" />
    </div>
  </div>

  <section class="welcome">
    <h4>Welcome back, <?php echo htmlspecialchars($teacher_name); ?> 👋</h4>
    <p class="text-muted">Here’s a summary of your classes and activities today.</p>
  </section>

  <section class="stats-grid">
    <div class="stat">
      <div class="icon primary"><i class="fa fa-user-graduate"></i></div>
      <div><div class="meta">Total Students</div><div class="value"><?php echo $students; ?></div></div>
    </div>
    <div class="stat">
      <div class="icon success"><i class="fa fa-chalkboard"></i></div>
      <div><div class="meta">Active Classes</div><div class="value"><?php echo $total_classes; ?></div></div>
    </div>
    <div class="stat">
      <div class="icon warn"><i class="fa fa-calendar-check"></i></div>
      <div><div class="meta">Attendance Rate</div><div class="value"><?php echo $attendance; ?>%</div></div>
    </div>
    <div class="stat">
      <div class="icon accent"><i class="fa fa-book-open"></i></div>
      <div><div class="meta">Assignments Due</div><div class="value"><?php echo $assignments_due; ?></div></div>
    </div>
  </section>

  <section class="row-grid">
    <div>
      <div class="card-panel">
        <h6>Performance Overview</h6>
        <canvas id="performanceChart" height="120"></canvas>
      </div>

      <div class="card-panel mt-3">
        <h6>Attendance Trend</h6>
        <canvas id="attendanceChart" height="100"></canvas>
      </div>

      <div class="card-panel mt-3">
        <h6>Your Classes</h6>
        <div class="classes-list">
          <?php foreach($classes as $c): ?>
            <div class="class-item">
              <div>
                <div style="font-weight:600;"><?php echo htmlspecialchars($c['name']); ?></div>
                <div class="text-muted small"><?php echo htmlspecialchars($c['students']); ?> students</div>
              </div>
              <div><a class="btn btn-sm btn-outline-primary" href="#">Open</a></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <aside>
      <div class="card-panel">
        <h6>Upcoming Events</h6>
        <div class="list-compact">
          <?php foreach($upcoming_events as $ev): ?>
            <div class="msg"><div style="font-weight:600"><?php echo date('M j, Y', strtotime($ev['date'])); ?></div><div class="text-muted small"><?php echo htmlspecialchars($ev['title']); ?></div></div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="card-panel mt-3">
        <h6>Staffroom Messages</h6>
        <div class="list-compact">
          <?php foreach($messages as $m): ?>
            <div class="msg"><div style="font-weight:600"><?php echo htmlspecialchars($m['from']); ?></div><div class="text-muted small"><?php echo htmlspecialchars($m['snippet']); ?></div></div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="card-panel mt-3">
        <h6>Quick Actions</h6>
        <div class="d-grid gap-2 mt-2">
          <a href="#" class="btn btn-primary btn-sm">Take Attendance</a>
          <a href="#" class="btn btn-outline-secondary btn-sm">Create Assignment</a>
          <a href="#" class="btn btn-outline-secondary btn-sm">Message Class</a>
        </div>
      </div>
<!-- Exams Section -->
<div class="card-panel exams-panel mt-3">
  <h6 class="text-white">Upcoming Exams</h6>
  <div class="classes-list">
    <?php foreach($exams as $e): ?>
      <div class="class-item exam-item">
        <div>
          <div style="font-weight:600;"><?php echo htmlspecialchars($e['exam_name']); ?></div>
          <div class="text-light small">
            <?php echo htmlspecialchars($e['class']); ?> • <?php echo htmlspecialchars($e['exam_date']); ?>
          </div>
        </div>
        <div><a class="btn btn-sm btn-outline-light" href="#">View</a></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

    </aside>
  </section>
</main>

<script>
  const perfCtx = document.getElementById('performanceChart').getContext('2d');
  new Chart(perfCtx, {
    type: 'line',
    data: {
      labels: ['Week 1','Week 2','Week 3','Week 4','Week 5','Week 6'],
      datasets: [{
        label: 'Average Score',
        data: [72,78,75,81,79,84],
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37,99,235,0.08)',
        tension: 0.3,
        pointRadius: 3,
        pointBackgroundColor: '#2563eb',
        fill: true
      }]
    },
    options: { responsive:true, plugins:{legend:{display:false}}, scales:{ y:{ beginAtZero:true, max:100, ticks:{ stepSize:10 } } } }
  });

  const attCtx = document.getElementById('attendanceChart').getContext('2d');
  new Chart(attCtx, {
    type: 'bar',
    data: {
      labels: ['Mon','Tue','Wed','Thu','Fri'],
      datasets: [{
        label: 'Attendance %',
        data: [95,96,94,97,96],
        backgroundColor: ['#10b981','#10b981','#f59e0b','#10b981','#10b981'],
        borderRadius: 6
      }]
    },
    options: { responsive:true, plugins:{legend:{display:false}}, scales:{ y:{ beginAtZero:true, max:100 } } }
  });
</script>
</body>
</html>
