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
        <?php include '../includes/student-sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-6 py-4">
                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <p class="mb-4">Welcome to the CRAD Student Portal!</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-4 rounded shadow">
                        <h2 class="font-bold text-lg">Proposal Submission</h2>
                        <p>Submit your research proposals here.</p>
                        <a href="#" class="text-blue-500 hover:underline">Go to Proposal Submission</a>
                    </div>
                    <div class="bg-white p-4 rounded shadow">
                        <h2 class="font-bold text-lg">Adviser Assignment</h2>
                        <p>View your assigned adviser.</p>
                        <a href="#" class="text-blue-500 hover:underline">View Adviser Assignment</a>
                    </div>
                    <div class="bg-white p-4 rounded shadow">
                        <h2 class="font-bold text-lg">Defense Scheduling</h2>
                        <p>Schedule your defense here.</p>
                        <a href="#" class="text-blue-500 hover:underline">Go to Defense Scheduling</a>
                    </div>
                    <div class="bg-white p-4 rounded shadow">
                        <h2 class="font-bold text-lg">Document Tracker</h2>
                        <p>Track your submitted documents.</p>
                        <a href="#" class="text-blue-500 hover:underline">Go to Document Tracker</a>
                    </div>
                    <div class="bg-white p-4 rounded shadow">
                        <h2 class="font-bold text-lg">Payment Verification</h2>
                        <p>Verify your payment status.</p>
                        <a href="#" class="text-blue-500 hover:underline">Go to Payment Verification</a>
                    </div>
                    <div class="bg-white p-4 rounded shadow">
                        <h2 class="font-bold text-lg">Research Facilities</h2>
                        <p>Explore available research facilities.</p>
                        <a href="#" class="text-blue-500 hover:underline">View Research Facilities</a>
                    </div>
                    <div class="bg-white p-4 rounded shadow">
                        <h2 class="font-bold text-lg">Analytics</h2>
                        <p>View your academic analytics.</p>
                        <a href="#" class="text-blue-500 hover:underline">Go to Analytics</a>
                    </div>
                    <div class="bg-white p-4 rounded shadow">
                        <h2 class="font-bold text-lg">Seminars/Festivals</h2>
                        <p>Check upcoming seminars and festivals.</p>
                        <a href="#" class="text-blue-500 hover:underline">View Seminars/Festivals</a>
                    </div>
                    <div class="bg-white p-4 rounded shadow">
                        <h2 class="font-bold text-lg">Research Groups</h2>
                        <p>Join or view research groups.</p>
                        <a href="#" class="text-blue-500 hover:underline">View Research Groups</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Toggle sidebar visibility
        document.getElementById("toggleSidebar")?.addEventListener("click", () => {
            const sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("hidden");
        });
    </script>
</body>
</html>
