<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>School Management System</title>
  <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
  <link rel="stylesheet" href="../assets/css/sms.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
</head>

<style>
  body {
      background: linear-gradient(rgba(250, 250, 250, 0.9), rgba(8, 52, 117, 0.9)), url('../assets/img/img.jpg') no-repeat center center fixed; 
      background-size: cover;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding-top: 80px;
      scroll-behavior: smooth;
      }
</style>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top shadow-sm stylish-navbar">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
      <img src="../assets/img/sms.png" alt="Logo" width="40" height="40" class="me-2">
      School Management
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <ul class="navbar-nav mx-auto gap-4">
        <li class="nav-item"><a class="nav-link nav-underline fw-bold" href="#home">Home</a></li>
        <li class="nav-item"><a class="nav-link nav-underline fw-bold" href="#features">Features</a></li>
        <li class="nav-item"><a class="nav-link nav-underline fw-bold" href="#about-us">About Us</a></li>
        <li class="nav-item"><a class="nav-link nav-underline fw-bold" href="#contact">Contact Us</a></li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="btn-get-started btn-primary px-4 py-2 rounded-pill fw-bold" href="../auth/login.php">Get Started</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<section id="home" class="hero-slant-section position-relative">
  <div class="container position-relative z-2">
    <div class="row align-items-center">
      <div class="col-md-6 hero-text">
        <h1 class="animate__animated animate__fadeInLeft">Welcome to <br><span class="text-primary">School Management System</span></h1>
        <p class="animate__animated animate__fadeIn animate__delay-1s">Efficiently manage student records, faculty activities, and school operations — all in one place.</p>
        <a href="../auth/login.php" class="btn-get-started animate__animated animate__fadeIn animate__delay-2s">Get Started</a>
      </div>
    </div>
  </div>
  <div class="slanted-bg"></div>
  <div class="hero-logo-slant">
    <img src="../assets/img/sms.png" alt="Logo" class="animate__animated animate__fadeIn animate__delay-1s" />
  </div>
  <div class="hero-student-slant">
    <img src="../assets/img/hero.png" alt="Student Images" class="animate__animated animate__fadeInRight animate__delay-1s" />
  </div>
</section>

<!-- Animated Gradient Features Section -->
<section id="features" class="py-5 text-white" style="background: linear-gradient( rgba(250, 250, 250, 0.05), #f7faff29);">
  <div class="container position-relative">
    <div class="text-center mb-5">
<span class="badge bg-primary text-white px-4 py-2 rounded-pill fw-semibold animate__animated animate__fadeIn" style="font-size: 1.1rem;">Key Features</span>
    <p class="text-muted mx-auto animate__animated animate__fadeIn" style="max-width: 600px;">
        Essential features to streamline your school administration
      </p>
    </div>

    <div class="row g-4">
      <!-- Feature 1 -->
      <div class="col-md-4 animate__animated animate__fadeInUp">
        <div class="card h-100 border-0 overflow-hidden feature-card">
          <div class="card-body p-4 text-center position-relative z-1">
            <div class="icon-md bg-white text-primary rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center">
              <i class="fas fa-user-graduate fa-lg"></i>
            </div>
            <h5 class="fw-bold mb-2 text-white">Student Enrollment</h5>
            <p class="text-white-50 small mb-0">Streamlined digital registration process</p>
          </div>
        </div>
      </div>

      <!-- Feature 2 -->
      <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-1s">
        <div class="card h-100 border-0 overflow-hidden feature-card">
          <div class="card-body p-4 text-center position-relative z-1">
            <div class="icon-md bg-white text-info rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center">
              <i class="fas fa-book-open fa-lg"></i>
            </div>
            <h5 class="fw-bold mb-2 text-white">Curriculum</h5>
            <p class="text-white-50 small mb-0">Manage courses and subjects</p>
          </div>
        </div>
      </div>

      <!-- Feature 3 -->
      <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-2s">
        <div class="card h-100 border-0 overflow-hidden feature-card">
          <div class="card-body p-4 text-center position-relative z-1">
            <div class="icon-md bg-white text-success rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center">
              <i class="fas fa-users fa-lg"></i>
            </div>
            <h5 class="fw-bold mb-2 text-white">Student Profiles</h5>
            <p class="text-white-50 small mb-0">Complete academic records</p>
          </div>
        </div>
      </div>

      <!-- Feature 4 -->
      <div class="col-md-4 animate__animated animate__fadeInUp">
        <div class="card h-100 border-0 overflow-hidden feature-card">
          <div class="card-body p-4 text-center position-relative z-1">
            <div class="icon-md bg-white text-warning rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center">
              <i class="fas fa-chalkboard-teacher fa-lg"></i>
            </div>
            <h5 class="fw-bold mb-2 text-white">Faculty</h5>
            <p class="text-white-50 small mb-0">Teacher management tools</p>
          </div>
        </div>
      </div>

      <!-- Feature 5 -->
      <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-1s">
        <div class="card h-100 border-0 overflow-hidden feature-card">
          <div class="card-body p-4 text-center position-relative z-1">
            <div class="icon-md bg-white text-danger rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center">
              <i class="fas fa-calendar-alt fa-lg"></i>
            </div>
            <h5 class="fw-bold mb-2 text-white">Scheduling</h5>
            <p class="text-white-50 small mb-0">Class timetable management</p>
          </div>
        </div>
      </div>

      <!-- Feature 6 -->
      <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-2s">
        <div class="card h-100 border-0 overflow-hidden feature-card">
          <div class="card-body p-4 text-center position-relative z-1">
            <div class="icon-md bg-white text-purple rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center">
              <i class="fas fa-clipboard-list fa-lg"></i>
            </div>
            <h5 class="fw-bold mb-2 text-white">Grades</h5>
            <p class="text-white-50 small mb-0">Performance tracking</p>
          </div>
        </div>
      </div>

      <!-- Feature 7 -->
      <div class="col-md-4 animate__animated animate__fadeInUp">
        <div class="card h-100 border-0 overflow-hidden feature-card">
          <div class="card-body p-4 text-center position-relative z-1">
            <div class="icon-md bg-white text-teal rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center">
              <i class="fas fa-user-shield fa-lg"></i>
            </div>
            <h5 class="fw-bold mb-2 text-white">User Roles</h5>
            <p class="text-white-50 small mb-0">Permission management</p>
          </div>
        </div>
      </div>

      <!-- Feature 8 -->
      <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-1s">
        <div class="card h-100 border-0 overflow-hidden feature-card">
          <div class="card-body p-4 text-center position-relative z-1">
            <div class="icon-md bg-white text-pink rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center">
              <i class="fas fa-bell fa-lg"></i>
            </div>
            <h5 class="fw-bold mb-2 text-white">Notifications</h5>
            <p class="text-white-50 small mb-0">Important alerts system</p>
          </div>
        </div>
      </div>

      <!-- Feature 9 -->
      <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-2s">
        <div class="card h-100 border-0 overflow-hidden feature-card">
          <div class="card-body p-4 text-center position-relative z-1">
            <div class="icon-md bg-white text-indigo rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center">
              <i class="fas fa-chart-line fa-lg"></i>
            </div>
            <h5 class="fw-bold mb-2 text-white">Analytics</h5>
            <p class="text-white-50 small mb-0">Performance insights</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  /* Gradient backgrounds for each feature */
  .feature-card {
    background: linear-gradient( #09387e4a, #06294c47);
    transition: all 0.4s ease;
    transform: translateY(0);
  }
  
  .feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  }
  
  /* Individual gradient colors */
  .feature-card:nth-child(1) {
    --gradient-start: #3a7bd5;
    --gradient-end: #00d2ff;
  }
  .feature-card:nth-child(2) {
    --gradient-start: #11998e;
    --gradient-end: #38ef7d;
  }
  .feature-card:nth-child(3) {
    --gradient-start: #8e2de2;
    --gradient-end: #4a00e0;
  }
  .feature-card:nth-child(4) {
    --gradient-start: #f46b45;
    --gradient-end: #eea849;
  }
  .feature-card:nth-child(5) {
    --gradient-start: #c31432;
    --gradient-end: #240b36;
  }
  .feature-card:nth-child(6) {
    --gradient-start: #7b4397;
    --gradient-end: #dc2430;
  }
  .feature-card:nth-child(7) {
    --gradient-start: #1d976c;
    --gradient-end: #93f9b9;
  }
  .feature-card:nth-child(8) {
    --gradient-start: #ff758c;
    --gradient-end: #ff7eb3;
  }
  .feature-card:nth-child(9) {
    --gradient-start: #5f2c82;
    --gradient-end: #49a09d;
  }

  /* Icon styling */
  .icon-md {
    width: 60px;
    height: 60px;
    transition: transform 0.3s ease;
  }
  
  .feature-card:hover .icon-md {
    transform: scale(1.1);
  }
  
  /* Text colors */
  .text-white-50 {
    color: rgba(255,255,255,0.7);
  }
  
  /* Animation delays */
  .animate__delay-1s {
    animation-delay: 0.2s;
  }
  .animate__delay-2s {
    animation-delay: 0.4s;
  }
  
  /* Ensure content stays above gradient */
  .z-1 {
    z-index: 1;
  }
</style>

<!-- About Us Section -->
<section id="about-us" class="py-5 text-white" style="background: linear-gradient( #f7faff29, #5290cd9a);">
    <div class="container">
    <div class="row align-items-center">
      <!-- Image Column -->
      <div class="col-md-6 mb-4 mb-md-0 text-center">
        <img src="../assets/img/studs.jpg" class="img-fluid rounded-4 shadow w-75 animate__animated animate__fadeInLeft" alt="About Us Image">
      </div>

      <!-- Text Column -->
      <div class="col-md-6 animate__animated animate__fadeInRight">
        <h2 class="fw-bold text-primary mb-3">About Us</h2>
        <p class="lead mb-3">
          The <strong>School Management System</strong> is a next-generation academic platform built to transform traditional school operations into a streamlined digital experience.
        </p>
        <ul class="list-unstyled mb-4">
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Hassle-free enrollment and subject management</li>
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Real-time access to grades, schedules, and updates</li>
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Designed for students, teachers, and administrators</li>
        </ul>
        <p class="small text-muted">
          Built with passion for education, our system simplifies the way schools operate — letting you focus on what matters most: learning.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- Contact Us Section -->
<section id="contact" class="py-5 text-white" style="background: linear-gradient( #5290cd9a, #052849b7);">
  <div class="container">
    <h2 class="fw-bold text-center mb-5 animate__animated animate__fadeIn">Contact Us</h2>
    <div class="row justify-content-center">
      <!-- Contact Info Column -->
      <div class="col-md-6 animate__animated animate__fadeIn animate__delay-1s">
        <div class="mb-4">
          <h5 class="fw-bold"><i class="fas fa-map-marker-alt me-2"></i> Address</h5>
          <p>Bestlink College of the Philippines, Quirino Highway, Quezon City, Metro Manila</p>
        </div>
        <div class="mb-4">
          <h5 class="fw-bold"><i class="fas fa-phone me-2"></i> Contact Number</h5>
          <p>(02) 1234-5678 / 0917-123-4567</p>
        </div>
        <div class="mb-4">
          <h5 class="fw-bold"><i class="fas fa-envelope me-2"></i> Email</h5>
          <p>sms.support@bestlink.edu.ph</p>
        </div>
        <div class="mb-4">
          <h5 class="fw-bold"><i class="fas fa-clock me-2"></i> Office Hours</h5>
          <p>Monday - Friday: 8:00 AM to 5:00 PM</p>
        </div>
      </div>

      <!-- Social Media Column -->
      <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-2s text-center text-md-start">
        <h5 class="fw-bold mb-3"><i class="fas fa-share-alt me-2"></i>Connect with us</h5>
        <div class="d-grid gap-3">
          <a href="#" class="text-white text-decoration-none">
            <i class="fab fa-facebook fa-lg me-2"></i> Facebook
          </a>
          <a href="#" class="text-white text-decoration-none">
            <i class="fab fa-twitter fa-lg me-2"></i> Twitter
          </a>
          <a href="#" class="text-white text-decoration-none">
            <i class="fab fa-instagram fa-lg me-2"></i> Instagram
          </a>
          <a href="#" class="text-white text-decoration-none">
            <i class="fab fa-linkedin fa-lg me-2"></i> LinkedIn
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer Section -->
<footer class="text-center py-4" style="background-color: #002a80;">
  <div class="container">
    <p class="mb-0 text-white animate__animated animate__fadeIn">&copy; 2025 School Management System. All rights reserved.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
  // Navbar scroll effect
  $(window).scroll(function() {
    if ($(this).scrollTop() > 50) {
      $('.navbar').addClass('scrolled');
    } else {
      $('.navbar').removeClass('scrolled');
    }
  });
  
  // Smooth scrolling for anchor links
  $('a[href*="#"]').on('click', function(e) {
    e.preventDefault();
    
    $('html, body').animate(
      {
        scrollTop: $($(this).attr('href')).offset().top - 70,
      },
      500,
      'linear'
    );
  });
  
  // Animation on scroll
  $(document).ready(function() {
    $('.animate__animated').each(function() {
      $(this).css('opacity', '0');
    });
    
    $(window).scroll(function() {
      $('.animate__animated').each(function() {
        var position = $(this).offset().top;
        var scroll = $(window).scrollTop();
        var windowHeight = $(window).height();
        
        if (scroll + windowHeight > position) {
          var animation = $(this).attr('class').split('animate__animated ')[1];
          $(this).css('opacity', '1').addClass(animation);
        }
      });
    }).scroll(); // Trigger scroll event on page load
  });
</script>
</body>
</html>
