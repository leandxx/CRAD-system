<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seminars & Festivals (Viva Pit Senior)</title>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-purple-700">Capstone Seminars</h1>
                <p class="text-gray-600">Browse upcoming capstone-related seminars, webinars, and training sessions.</p>
            </div>

            <!-- Seminar List -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Seminar Card Example -->
                <div class="bg-white shadow-md rounded-lg p-5 hover:shadow-xl transition-shadow">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Capstone Proposal Writing</h2>
                    <p class="text-sm text-gray-600 mb-3">Learn how to write a strong and effective capstone proposal.</p>
                    <p class="text-sm text-gray-500 mb-1"><strong>Date:</strong> August 12, 2025</p>
                    <p class="text-sm text-gray-500 mb-1"><strong>Time:</strong> 10:00 AM - 12:00 PM</p>
                    <p class="text-sm text-gray-500 mb-1"><strong>Speaker:</strong> Dr. Maria Santos</p>
                    <button class="mt-3 w-full bg-purple-600 text-white py-2 rounded hover:bg-purple-700">Register</button>
                </div>

                <!-- Another Seminar -->
                <div class="bg-white shadow-md rounded-lg p-5 hover:shadow-xl transition-shadow">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Thesis Defense Preparation</h2>
                    <p class="text-sm text-gray-600 mb-3">Tips and tricks on how to confidently present and defend your capstone.</p>
                    <p class="text-sm text-gray-500 mb-1"><strong>Date:</strong> August 15, 2025</p>
                    <p class="text-sm text-gray-500 mb-1"><strong>Time:</strong> 1:00 PM - 3:00 PM</p>
                    <p class="text-sm text-gray-500 mb-1"><strong>Speaker:</strong> Engr. Ronald Dela Cruz</p>
                    <button class="mt-3 w-full bg-purple-600 text-white py-2 rounded hover:bg-purple-700">Register</button>
                </div>

                <!-- You can duplicate more seminar cards here -->
            </div>

            <!-- Footer -->
            <div class="mt-10 text-center text-sm text-gray-400">
                <p>&copy; <?= date("Y") ?> CRAD Student Portal — Bestlink College of the Philippines</p>
            </div>
        </main>
    </div>
</body>
</html>