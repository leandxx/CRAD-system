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

<!-- Modern Animated Glassmorphic Features Section -->
<section id="features" class="py-5 text-white" style="background: linear-gradient( rgba(250, 250, 250, 0.05), #f7faff29);">
    <div class="container">
    <div class="text-center mb-5">
      <span class="badge bg-white text-dark px-4 py-2 rounded-pill fw-bold animate__animated animate__fadeIn" style="font-size: 1.2rem;">
        ✨ Core Features
      </span>
      <p class="text-light mx-auto mt-3 animate__animated animate__fadeIn" style="max-width: 600px; font-size: 1rem;">
        Powerful tools to transform school administration into a smart digital ecosystem.
      </p>
    </div>

    <div class="row g-4">
      <!-- Feature Loop -->
      <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0s;">
        <div class="glass-card text-center p-4">
          <div class="icon-box mb-3 bg-primary">
            <i class="fas fa-user-graduate fa-2x"></i>
          </div>
          <h4 class="fw-bold mb-2">Student Enrollment</h4>
          <p class="text-light small">Streamlined digital registration process</p>
        </div>
      </div>

      <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
        <div class="glass-card text-center p-4">
          <div class="icon-box mb-3 bg-info">
            <i class="fas fa-book-open fa-2x"></i>
          </div>
          <h4 class="fw-bold mb-2">Curriculum</h4>
          <p class="text-light small">Manage courses and subjects</p>
        </div>
      </div>

      <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
        <div class="glass-card text-center p-4">
          <div class="icon-box mb-3 bg-success">
            <i class="fas fa-users fa-2x"></i>
          </div>
          <h4 class="fw-bold mb-2">Student Profiles</h4>
          <p class="text-light small">Complete academic records</p>
        </div>
      </div>

      <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
        <div class="glass-card text-center p-4">
          <div class="icon-box mb-3 bg-warning">
            <i class="fas fa-chalkboard-teacher fa-2x"></i>
          </div>
          <h4 class="fw-bold mb-2">Faculty</h4>
          <p class="text-light small">Teacher management tools</p>
        </div>
      </div>

      <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
        <div class="glass-card text-center p-4">
          <div class="icon-box mb-3 bg-danger">
            <i class="fas fa-calendar-alt fa-2x"></i>
          </div>
          <h4 class="fw-bold mb-2">Scheduling</h4>
          <p class="text-light small">Class timetable management</p>
        </div>
      </div>

      <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
        <div class="glass-card text-center p-4">
          <div class="icon-box mb-3" style="background-color: #7b4397;">
            <i class="fas fa-clipboard-list fa-2x"></i>
          </div>
          <h4 class="fw-bold mb-2">Grades</h4>
          <p class="text-light small">Performance tracking</p>
        </div>
      </div>

      <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.6s;">
        <div class="glass-card text-center p-4">
          <div class="icon-box mb-3" style="background-color: #1d976c;">
            <i class="fas fa-user-shield fa-2x"></i>
          </div>
          <h4 class="fw-bold mb-2">User Roles</h4>
          <p class="text-light small">Permission management</p>
        </div>
      </div>

      <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.7s;">
        <div class="glass-card text-center p-4">
          <div class="icon-box mb-3" style="background-color: #ff758c;">
            <i class="fas fa-bell fa-2x"></i>
          </div>
          <h4 class="fw-bold mb-2">Notifications</h4>
          <p class="text-light small">Important alerts system</p>
        </div>
      </div>

      <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.8s;">
        <div class="glass-card text-center p-4">
          <div class="icon-box mb-3" style="background-color: #5f2c82;">
            <i class="fas fa-chart-line fa-2x"></i>
          </div>
          <h4 class="fw-bold mb-2">Analytics</h4>
          <p class="text-light small">Performance insights</p>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  .glass-card {
    background: rgba(5, 31, 92, 0.24);
    border: 1px solid rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(12px);
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .glass-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.3);
  }

  .icon-box {
    width: 70px;
    height: 70px;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    transition: transform 0.3s ease;
  }

  .glass-card:hover .icon-box {
    transform: scale(1.1);
  }

  h4 {
    color: #004aad;
  }

  p {
    color: rgba(255, 255, 255, 0.8);
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
