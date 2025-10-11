<?php
session_start();
include("../includes/connection.php");

$alertMessage = "";
$alertType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = $_POST['userType'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alertMessage = "Please enter a valid email address!";
        $alertType = "error";
    } elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[\W]/', $password)
    ) {
        $alertMessage = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
        $alertType = "error";
    } elseif ($password !== $confirmPassword) {
        $alertMessage = "Passwords do not match!";
        $alertType = "error";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM user_tbl WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $alertMessage = "Email is already registered!";
            $alertType = "error";
        } else {
            $stmt->close();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO user_tbl (email, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                $alertMessage = "Registration successful!";
                $alertType = "success";
                echo "<script>
                        setTimeout(() => { 
                            window.location.href='student-login.php';
                        }, 1500);
                      </script>";
            } else {
                $alertMessage = "Registration failed: " . $stmt->error;
                $alertType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
  <title>CRAD | Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <style>
    /* Input group styling (same as admin/student) */
    .input-group {
      position: relative;
      margin-bottom: 1.75rem;
    }

    .input-group input,
    .input-group select {
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

    .input-group input:focus,
    .input-group select:focus {
      border-color: #1d4ed8;
      box-shadow: 0 0 10px rgba(29, 78, 216, 0.3);
    }

    .input-group input:focus + label,
    .input-group input:not(:placeholder-shown) + label,
    .input-group select:focus + label,
    .input-group select:not(:placeholder-shown) + label {
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

    .eye-icon:hover {
      color: #1d4ed8;
    }

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
      <?php echo $alertType === "success" ? "✅" : "❌"; ?>
      <span><?php echo htmlspecialchars($alertMessage); ?></span>
    </div>
  <?php endif; ?>

  <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl max-w-5xl w-full mx-4 flex flex-col md:flex-row overflow-hidden border border-gray-200">
    <!-- Left Panel -->
    <div class="md:w-1/2 p-10 flex flex-col justify-center items-center text-center bg-gradient-to-br from-white via-blue-50 to-blue-100 text-gray-800 relative">
      <img src="../assets/img/sms-logo.png" alt="School Logo" class="w-32 h-32 mb-6 rounded-full shadow-lg border-4 border-white" />
      <h1 class="text-4xl md:text-5xl font-extrabold mb-3 tracking-tight text-blue-900">School Management System</h1>
      <p class="text-gray-700 mb-8 max-w-md leading-relaxed font-medium">
        Register to access CRAD’s smart research submission and tracking platform — designed to simplify your academic workflow.
      </p>
      <a href="../auth/student-login.php" class="bg-blue-700 hover:bg-blue-800 text-white font-extrabold px-6 py-3 rounded-lg shadow-md transition-all duration-300">
        Go Back
      </a>
    </div>

    <!-- Right Panel -->
    <div class="md:w-1/2 bg-white p-10 flex flex-col justify-center items-center text-center">

      <h3 class="text-2xl font-bold text-blue-900 mb-6">Register Your Account</h3>

      <form method="POST" class="w-full max-w-md text-left">

        <!-- Email -->
        <div class="input-group">
          <input type="email" id="email" name="email" placeholder=" " required />
          <label for="email">Email</label>
        </div>

        <!-- Password -->
        <div class="input-group">
          <input type="password" id="password" name="password" placeholder=" " required />
          <label for="password">Password</label>
          <i class="fas fa-eye eye-icon" id="togglePassword"></i>
        </div>

        <!-- Confirm Password -->
        <div class="input-group">
          <input type="password" id="confirmPassword" name="confirmPassword" placeholder=" " required />
          <label for="confirmPassword">Confirm Password</label>
          <i class="fas fa-eye eye-icon" id="toggleConfirmPassword"></i>
        </div>

        <!-- Role -->
        <div class="input-group">
          <select id="userType" name="userType" required>
            <option value="" disabled selected hidden></option>
            <option value="Admin">Admin</option>
            <option value="Student">Student</option>
          </select>
          <label for="userType">Select User Type</label>
        </div>

        <!-- Submit -->
        <button type="submit"
          class="w-full bg-gradient-to-r from-blue-700 to-blue-800 text-white font-semibold py-2 px-4 rounded-full shadow-md hover:from-blue-800 hover:to-blue-900 transform hover:scale-[1.02] transition-all duration-300">
          Register
        </button>
      </form>

      <p class="mt-6 text-gray-600 text-sm">
        Already have an account?
        <a href="student-login.php" class="text-blue-700 font-semibold hover:underline">Log in</a>
      </p>
    </div>
  </div>

  <script>
    // Password toggle
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirmPassword');

    togglePassword.addEventListener('click', () => {
      const type = passwordInput.type === 'password' ? 'text' : 'password';
      passwordInput.type = type;
      togglePassword.classList.toggle('text-blue-700');
    });

    toggleConfirmPassword.addEventListener('click', () => {
      const type = confirmInput.type === 'password' ? 'text' : 'password';
      confirmInput.type = type;
      toggleConfirmPassword.classList.toggle('text-blue-700');
    });

    // Auto-hide alert
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
