<?php
session_start();
include("../includes/connection.php");

?>

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
    background: linear-gradient(rgba(250, 250, 250, 0.796), rgba(8, 52, 117, 0.942)), url('../assets/img/img.jpg') no-repeat center center fixed; 
    background-size: cover;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding-top: 80px;
    scroll-behavior: smooth;
  }
  
  /* Animation Classes */
  .fade-in {
    animation: fadeIn 1.5s ease-in-out;
  }
  
  .slide-in-left {
    animation: slideInLeft 1s ease-out;
  }
  
  .slide-in-right {
    animation: slideInRight 1s ease-out;
  }
  
  .pulse {
    animation: pulse 2s infinite;
  }
  
  .float {
    animation: float 3s ease-in-out infinite;
  }
  
  .feature-box:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0, 0, 100, 0.2);
  }
  
  /* Keyframe Animations */
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  
  @keyframes slideInLeft {
    from { 
      transform: translateX(-100px);
      opacity: 0;
    }
    to { 
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @keyframes slideInRight {
    from { 
      transform: translateX(100px);
      opacity: 0;
    }
    to { 
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
  }
  
  @keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
    100% { transform: translateY(0px); }
  }
  
  /* Navbar Animation */
  .navbar {
    transition: all 0.3s ease;
  }
  
  .navbar.scrolled {
    background-color: rgba(0, 42, 128, 0.9) !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }
  
  .nav-link {
    position: relative;
  }
  
  .nav-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: 0;
    left: 0;
    background-color: #007bff;
    transition: width 0.3s ease;
  }
  
  .nav-link:hover::after {
    width: 100%;
  }
  
  /* Button Animation */
  .btn-get-started {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }
  
  .btn-get-started:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
  }
  
  .btn-get-started::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: 0.5s;
  }
  
  .btn-get-started:hover::after {
    left: 100%;
  }
  
  /* Hero Section Animation */
  .hero-slant-section h1 {
    animation: fadeIn 1s ease-in-out, slideInLeft 1s ease-out;
  }
  
  .hero-slant-section p {
    animation: fadeIn 1.5s ease-in-out;
  }
  
  .hero-slant-section .btn-get-started {
    animation: fadeIn 2s ease-in-out;
  }
  
  .hero-logo-slant img {
    animation: float 4s ease-in-out infinite;
  }
  
  .hero-student-slant img {
    animation: slideInRight 1s ease-out, float 3s ease-in-out infinite 1s;
  }
  
  /* Feature Box Animation */
  .feature-box {
    transition: all 0.3s ease;
    transform: translateY(0);
  }
  
  /* About Us Animation */
  #about-us img {
    animation: fadeIn 1s ease-in-out, pulse 3s infinite 1s;
    transition: all 0.3s ease;
  }
  
  #about-us img:hover {
    transform: scale(1.03);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
  }
  
  /* Contact Section Animation */
  #contact div {
    animation: fadeIn 1s ease-in-out;
  }
  
  #contact h5 i {
    transition: all 0.3s ease;
  }
  
  #contact h5:hover i {
    transform: scale(1.2);
    color: #007bff;
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
        <li class="nav-item"><a class="nav-link nav-underline fw-bold" href="#">Home</a></li>
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
<section class="hero-slant-section position-relative">
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

<!-- Core Features Section -->
<section id="features" class="py-5 text-white" style="background: linear-gradient(#011f4b65, #011f4b65);">
  <div class="container">
    <div class="text-end mb-4">
      <h2 class="fw-bold text-primary animate__animated animate__fadeIn">Core Features</h2>
      <p class="text-light animate__animated animate__fadeIn animate__delay-1s">Explore what our School Management System offers.</p>
    </div>

    <div class="d-flex flex-wrap justify-content-end gap-3">
      <!-- Feature Box 1 -->
      <div class="feature-box animate__animated animate__fadeInUp animate__delay-1s">
        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
          <i class="fas fa-user-graduate fa-lg text-primary mb-2"></i>
          <h6 class="fw-bold mb-1">Enrollment</h6>
          <p class="text-light small mb-0">Student registration made simple.</p>
        </div>
      </div>

      <!-- Feature Box 2 -->
      <div class="feature-box animate__animated animate__fadeInUp animate__delay-1s">
        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
          <i class="fas fa-book-open fa-lg text-primary mb-2"></i>
          <h6 class="fw-bold mb-1">Subjects</h6>
          <p class="text-light small mb-0">Manage courses and subjects easily.</p>
        </div>
      </div>

      <!-- Feature Box 3 -->
      <div class="feature-box animate__animated animate__fadeInUp animate__delay-1s">
        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
          <i class="fas fa-users fa-lg text-primary mb-2"></i>
          <h6 class="fw-bold mb-1">Students</h6>
          <p class="text-light small mb-0">View and update student profiles.</p>
        </div>
      </div>

      <!-- Feature Box 4 -->
      <div class="feature-box animate__animated animate__fadeInUp animate__delay-1s">
        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
          <i class="fas fa-chalkboard-teacher fa-lg text-primary mb-2"></i>
          <h6 class="fw-bold mb-1">Faculty</h6>
          <p class="text-light small mb-0">Manage faculty members and classes.</p>
        </div>
      </div>

      <!-- Feature Box 5 -->
      <div class="feature-box animate__animated animate__fadeInUp animate__delay-1s">
        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
          <i class="fas fa-calendar-alt fa-lg text-primary mb-2"></i>
          <h6 class="fw-bold mb-1">Schedule</h6>
          <p class="text-light small mb-0">Class scheduling made efficient.</p>
        </div>
      </div>

      <!-- Feature Box 6 -->
      <div class="feature-box animate__animated animate__fadeInUp animate__delay-1s">
        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
          <i class="fas fa-clipboard-list fa-lg text-primary mb-2"></i>
          <h6 class="fw-bold mb-1">Grades</h6>
          <p class="text-light small mb-0">Record and track academic performance.</p>
        </div>
      </div>

      <!-- Feature Box 7 -->
      <div class="feature-box animate__animated animate__fadeInUp animate__delay-1s">
        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
          <i class="fas fa-user-shield fa-lg text-primary mb-2"></i>
          <h6 class="fw-bold mb-1">User Roles</h6>
          <p class="text-light small mb-0">Manage permissions and access.</p>
        </div>
      </div>

      <!-- Feature Box 8 -->
      <div class="feature-box animate__animated animate__fadeInUp animate__delay-1s">
        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
          <i class="fas fa-bell fa-lg text-primary mb-2"></i>
          <h6 class="fw-bold mb-1">Notifications</h6>
          <p class="text-light small mb-0">Stay updated with announcements.</p>
        </div>
      </div>

      <!-- Feature Box 9 -->
      <div class="feature-box animate__animated animate__fadeInUp animate__delay-1s">
        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
          <i class="fas fa-chart-line fa-lg text-primary mb-2"></i>
          <h6 class="fw-bold mb-1">Analytics</h6>
          <p class="text-light small mb-0">Track school performance visually.</p>
        </div>
      </div>

      <!-- Feature Box 10 -->
      <div class="feature-box animate__animated animate__fadeInUp animate__delay-1s">
        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
          <i class="fas fa-cogs fa-lg text-primary mb-2"></i>
          <h6 class="fw-bold mb-1">Settings</h6>
          <p class="text-light small mb-0">Configure your system preferences.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- About Us Section -->
<section id="about-us" class="py-5 text-white" style="background: linear-gradient(to right, #ffffff56, #3399ff23);">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6 mb-4 mb-md-0 text-center">
        <img src="../assets/img/studs.jpg" class="img-fluid rounded w-75 animate__animated animate__fadeInLeft" alt="About Us Image">
      </div>
      <div class="col-md-6 animate__animated animate__fadeInRight">
        <h2 class="fw-bold">About Us</h2>
        <p>
          Our School Management System is a modern solution designed to make academic and administrative 
          processes simple, efficient, and unified. Built for both educators and learners, we aim to provide 
          a digital space where everything just works — from enrollment to research.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- Contact Us Section -->
<section id="contact" class="py-3 text-white" style="background: linear-gradient(135deg, #0a0a3a1d, #1465b163);">
  <div class="container text-center">
    <h2 class="fw-bold mb-4 animate__animated animate__fadeIn">Contact Us</h2>
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="mb-3 animate__animated animate__fadeIn animate__delay-1s">
          <h5 class="fw-bold"><i class="fas fa-map-marker-alt me-2"></i> Address</h5>
          <p>Bestlink College of the Philippines, Quirino Highway, Quezon City, Metro Manila</p>
        </div>
        <div class="mb-3 animate__animated animate__fadeIn animate__delay-1s">
          <h5 class="fw-bold"><i class="fas fa-phone me-2"></i> Contact Number</h5>
          <p>(02) 1234-5678 / 0917-123-4567</p>
        </div>
        <div class="mb-3 animate__animated animate__fadeIn animate__delay-1s">
          <h5 class="fw-bold"><i class="fas fa-envelope me-2"></i> Email</h5>
          <p>sms.support@bestlink.edu.ph</p>
        </div>
        <div class="mb-3 animate__animated animate__fadeIn animate__delay-1s">
          <h5 class="fw-bold"><i class="fas fa-clock me-2"></i> Office Hours</h5>
          <p>Monday - Friday: 8:00 AM to 5:00 PM</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer Section -->
<section id="login" class="text-center py-4" style="background-color: #002a80;">
  <div class="container" style="width: 600px;">
    <p class="mb-0 text-white animate__animated animate__fadeIn">&copy; 2025 School Management System. All rights reserved.</p>
  </div>
</section>

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
    // Initialize animate.css on scroll
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