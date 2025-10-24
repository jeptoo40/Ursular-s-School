<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> St.Ursular’s School System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    
    
    .navbar {
      border-radius: 50px;
      margin: 20px auto;
      width: 97%;
      background-color: darkslategrey !important;
    }
    .navbar-brand img {
      height: 55px;
      width: auto;
      margin-right: 10px;
      border-radius: 50%;
      position:center;
    }
    /* Hero Section */
    .hero {
      position: relative;
      text-align: center;
      color: white;
      margin-top: 20px;
    }

    .hero img {
      width: 100%;
      height: 80vh; /* smaller image height */
      object-fit: cover;
      border-radius: 20px;
      filter: brightness(60%);
    }

    .hero-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: rgba(0, 0, 0, 0.1);
      padding: 40px;
      border-radius: 20px;
      width: 70%;
    }

    .hero h1 {
      font-weight: 700;
    }

    @media (max-width: 768px) {
      .hero img {
        height: 50vh;
      }
      .hero-content {
        width: 90%;
        padding: 20px;
      }
    }
  </style>
</head>
<body>
    


<!-- Navbar with Motto and Email -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow rounded">
  <div class="container">

    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="logo-removebg-preview.png" alt="School Logo" width="50" class="me-2">
      <i class="bi bi-geo-alt-fill text-light me-1"></i>
      <span class="text-light">Kitale</span>

    <!-- Motto and Email (right on navbar) -->
    <div class="d-none d-lg-flex align-items-center ms-auto me-3">
    <span class="me-3" style="color: gold;">
  <strong>Motto: To enable these students to take their place in the society</strong>
</span>

      <i class="bi bi-envelope-fill text-light me-1"></i>
      <a href="mailto:info@smartschool.com" class="text-decoration-none text-light">Ursulineschool2006@gmail.com</a>
    </div>

    
   
  </div>
</nav>


    


  <!-- ✅ Hero Section -->
  <section class="hero">
    <img src="background.jpg" alt="background">
    <div class="hero-content">
      <h1 class="display-6 mb-3">Welcome to St Ursular’s School System</h1>
      <p class="lead mb-4">
        A unified platform for administrators, teachers, and parents to manage and monitor school activities,
        exams, and student performance efficiently — all in one place.
      </p>
    
      <!-- Buttons with icons -->
<button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
  <i class="bi bi-box-arrow-in-right me-1"></i> Login
</button>

<button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#signupModal">
  <i class="bi bi-person-plus-fill me-1"></i> Sign Up
</button>
    </div>
  </section>
 <!-- ✅ Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="auth/login.php" method="POST" class="modal-content border-0 shadow-lg rounded-4 p-4">
      
      <!-- Logo + Title -->
      <div class="text-center mb-4">
        <img src="logo-removebg-preview.png" alt="School Logo" style="height: 80px;" class="mb-2">
        <h5 class="fw-bold text-dark">Welcome Back — Please Login</h5>
        <p class="text-muted small">Access your account to continue</p>
      </div>

      <!-- Body -->
      <div class="modal-body p-0">
        <div class="mb-3">
          <label for="role" class="form-label fw-semibold">Select Role</label>
          <select class="form-select" id="role" name="role" required>
            <option value="">-- Choose Role --</option>
            <option value="admin">Admin</option>
            <option value="teacher">Teacher</option>
            <option value="parent">Parent</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label fw-semibold">Email Address</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label fw-semibold">Password</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer border-0 p-0">
        <button type="submit" class="btn btn-success w-100 py-2 rounded-pill shadow-sm">
          Login
        </button>
      </div>

      <div class="text-center mt-3">
        <small class="text-muted">
          Don’t have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#signupModal" class="text-success text-decoration-none">Register</a>
        </small>
      </div>
    </form>
  </div>
</div>


    </form>
  </div>
</div>

<!-- ✅ Signup Modal -->
<div class="modal fade" id="signupModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="auth/signup.php" method="POST" class="modal-content border-0 shadow-lg rounded-4 p-4">
      
      <!-- Header with centered logo -->
      <div class="text-center mb-4">
        <img src="logo-removebg-preview.png" alt="School Logo" width="70" class="mb-2">
        <h5 class="fw-bold text-dark">Create an Account</h5>
        <p class="text-muted small">Get the best Digital Smart Experience with us.</p>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <!-- Role selection -->
        <div class="mb-3">
          <label for="role" class="form-label">Select Role</label>
          <select class="form-select" id="role" name="role" required>
            <option value="">-- Choose Role --</option>
            <option value="admin">Admin</option>
            <option value="teacher">Teacher</option>
            <option value="parent">Parent</option>
          </select>
        </div>

        <!-- Full Name -->
        <div class="mb-3 input-group">
          <span class="input-group-text bg-white"><i class="bi bi-person-fill text-secondary"></i></span>
          <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required>
        </div>

        <!-- Email -->
        <div class="mb-3 input-group">
          <span class="input-group-text bg-white"><i class="bi bi-envelope-fill text-secondary"></i></span>
          <input type="email" name="email" class="form-control" placeholder="Enter email" required>
        </div>

        <!-- Password -->
        <div class="mb-3 input-group">
          <span class="input-group-text bg-white"><i class="bi bi-lock-fill text-secondary"></i></span>
          <input type="password" name="password" class="form-control" placeholder="Create password" required>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer border-0">
        <button type="submit" class="btn btn-success w-100">
          <i class="bi bi-person-plus-fill me-1"></i> Sign Up
        </button>
        <div class="text-center mt-3">
          <small class="text-muted">
            Have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="text-success text-decoration-none">Login</a>
          </small>
        </div>
      </div>
    </form>
  </div>
</div>


  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
