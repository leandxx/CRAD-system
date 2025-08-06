<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <title>CRAD Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-collapse {
            transition: all 0.3s ease;
        }
        .active-nav-item {
            background-color: #3b82f6;
            color: white;
        }
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background-color: #e5e7eb;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background-color: #3b82f6;
        }

        /* Hide scrollbar but keep scroll functionality */
        .hide-scrollbar {
            scrollbar-width: none;         /* Firefox */
            -ms-overflow-style: none;      /* Internet Explorer 10+ */
        } 

        .hide-scrollbar::-webkit-scrollbar {
            display: none;                 /* Chrome, Safari, Edge */
       }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen overflow-hidden">
        <?php include '../student/student-sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-6 py-4">
                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <!-- Add your dashboard content here -->
                <p>Welcome to the CRAD Student Portal!</p>
            </main>
        </div>
    </div>

    <script>
        // Toggle sidebar visibility
        document.getElementById("toggleSidebar")?.addEventListener("click", () => {
            const sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("hidden");
        });
    
</body>
</html>
