<?php
session_start();
include("../includes/connection.php");

// Alert message variable
$alertMessage = "";
$alertType = ""; // success, error, info

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM user_tbl WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'Student') {
                header("Location: ../student-pages/student.php");
                exit();
            } else {
                $alertMessage = "Unknown user role.";
                $alertType = "error";
            }
        } else {
            $alertMessage = "Incorrect password.";
            $alertType = "error";
        }
    } else {
        $alertMessage = "No account found with that email.";
        $alertType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
  <title>CRAD | Student Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .input-group {
      position: relative;
      margin-bottom: 1.75rem;
    }
    .input-group input {
      width: 100%;
      padding: 1rem 1rem;
      border: 2px solid #cbd5e1;
      border-radius: 9999px;
      background: transparent;
      color: #111827;
      transition: all 0.3s ease;
    }
    .input-group label {
      position: absolute;
      left: 1.2rem;
      top: 50%;
      transform: translateY(-50%);
      background: white;
      padding: 0 0.4rem;
      color: #64748b;
      pointer-events: none;
      transition: all 0.3s ease;
    }
    .input-group input:focus {
      border-color: #1d4ed8;
      box-shadow: 0 0 10px rgba(29, 78, 216, 0.3);
    }
    .input-group input:focus + label,
    .input-group input:not(:placeholder-shown) + label {
      top: 0;
      left: 1rem;
      transform: translateY(-50%) scale(0.9);
      font-size: 0.8rem;
      color: #1d4ed8;
    }
    .eye-icon {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #64748b;
      transition: color 0.2s ease;
    }
    .eye-icon:hover { color: #1d4ed8; }

    /* Toast Alert Styles */
    .alert {
      position: fixed;
      top: 1.5rem;
      right: 1.5rem;
      z-index: 50;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 1rem 1.5rem;
      border-radius: 0.75rem;
      font-weight: 500;
      animation: slideIn 0.4s ease forwards;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .alert-success { background: #dcfce7; color: #166534; }
    .alert-error { background: #fee2e2; color: #991b1b; }
    .alert-info { background: #dbeafe; color: #1e40af; }

    @keyframes slideIn {
      from { opacity: 0; transform: translateX(100%); }
      to { opacity: 1; transform: translateX(0); }
    }
  </style>
</head>

<body class="bg-cover bg-center bg-no-repeat min-h-screen flex items-center justify-center font-sans"
style="background-image: linear-gradient(rgba(250, 250, 250, 0.937), rgba(8, 52, 117, 0.942)), url('../assets/img/img.jpg');">

  <?php if (!empty($alertMessage)): ?>
    <div id="alertBox" class="alert alert-<?php echo $alertType; ?>">
      <?php if ($alertType === "error"): ?>
        ❌
      <?php elseif ($alertType === "success"): ?>
        ✅
      <?php else: ?>
        ℹ️
      <?php endif; ?>
      <span><?php echo htmlspecialchars($alertMessage); ?></span>
    </div>
  <?php endif; ?>

  <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl max-w-5xl w-full mx-4 flex flex-col md:flex-row overflow-hidden border border-gray-200">
    
    <!-- Left Panel -->
    <div class="md:w-1/2 p-10 flex flex-col justify-center items-center text-center bg-gradient-to-br from-white via-blue-50 to-blue-100 text-gray-800 relative">
      <img src="../assets/img/sms-logo.png" alt="School Logo" class="w-32 h-32 mb-6 rounded-full shadow-lg border-4 border-white" />
      <h1 class="text-4xl md:text-5xl font-extrabold mb-3 tracking-tight text-blue-900">School Management System</h1>
      <p class="text-gray-700 mb-8 max-w-md leading-relaxed font-medium">
        Empowering education through a unified academic management system that enhances learning, streamlines processes, and connects the academic community.
      </p>
      <a href="../role/role-selection.php" class="bg-blue-700 hover:bg-blue-800 text-white font-extrabold px-6 py-3 rounded-lg shadow-md transition-all duration-300">
        Go Back
      </a>
    </div>

    <!-- Right Panel -->
    <div class="md:w-1/2 bg-white p-10 flex flex-col justify-center items-center text-center">
      
      <div class="mb-8">
        <h2 class="text-4xl font-extrabold text-blue-900 mb-2 tracking-wide">
          <span class="text-blue-700">CRAD</span>
        </h2>
        <p class="text-gray-600 text-sm italic">
          Intelligent Progressive Research Submission & Tracking System
        </p>
      </div>

      <h3 class="text-2xl font-bold text-blue-900 mb-6">Student Login</h3>

      <form id="loginForm" action="student-login.php" method="POST" class="w-full max-w-md text-left">
        
        <div class="input-group">
          <input type="text" id="email" name="email" placeholder=" " required />
          <label for="email">Email</label>
        </div>

        <div class="input-group">
          <input type="password" id="password" name="password" placeholder=" " required />
          <label for="password">Password</label>
          <svg id="togglePassword" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            class="eye-icon w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" 
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" 
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 
                   0 8.268 2.943 9.542 7-1.274 4.057-5.065 
                   7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
        </div>

        <button type="submit"
          class="w-full bg-gradient-to-r from-blue-700 to-blue-800 text-white font-semibold py-2 px-4 rounded-full shadow-md hover:from-blue-800 hover:to-blue-900 transform hover:scale-[1.02] transition-all duration-300">
          Log In
        </button>
      </form>

      <p class="mt-6 text-gray-600 text-sm">
        Don’t have an account?
        <a href="register.php" class="text-blue-700 font-semibold hover:underline">Sign up</a>
      </p>
    </div>
  </div>

  <script>
    // Password toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    togglePassword.addEventListener('click', () => {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      togglePassword.classList.toggle('text-blue-700');
    });

    // Auto-hide alert after 3 seconds
    const alertBox = document.getElementById("alertBox");
    if (alertBox) {
      setTimeout(() => {
        alertBox.style.opacity = "0";
        alertBox.style.transform = "translateX(100%)";
        setTimeout(() => alertBox.remove(), 500);
      }, 3000);
    }
  </script>
</body>
</html>
