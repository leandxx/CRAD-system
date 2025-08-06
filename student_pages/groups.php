<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Groups</title>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <!-- Section Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-blue-700">Capstone 41006 - Cluster 6</h1>
                <p class="text-gray-600">Below are the capstone groups in your assigned section.</p>
            </div>

            <!-- Groups Container -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Group Card -->
                <div class="bg-white shadow-md rounded-xl p-5 border-l-4 border-blue-600">
                    <h2 class="text-xl font-semibold text-gray-800">Group 1</h2>
                    <p class="text-gray-600 text-sm">Capstone Title: <span class="font-medium">Smart Attendance System</span></p>
                    <p class="text-gray-600 text-sm">Status: <span class="text-green-600 font-bold">Approved</span></p>
                    <p class="text-gray-600 text-sm mt-2">Members:</p>
                    <ul class="list-disc pl-5 text-sm text-gray-700">
                        <li>John Marvic Giray</li>
                        <li>Leandro Reyes</li>
                        <li>Erico Santos</li>
                        <li>Angelo Cruz</li>
                    </ul>
                </div>

                <!-- Group Card 2 -->
                <div class="bg-white shadow-md rounded-xl p-5 border-l-4 border-yellow-500">
                    <h2 class="text-xl font-semibold text-gray-800">Group 2</h2>
                    <p class="text-gray-600 text-sm">Capstone Title: <span class="font-medium">AI Chatbot for Student Queries</span></p>
                    <p class="text-gray-600 text-sm">Status: <span class="text-yellow-600 font-bold">Pending</span></p>
                    <p class="text-gray-600 text-sm mt-2">Members:</p>
                    <ul class="list-disc pl-5 text-sm text-gray-700">
                        <li>Alexa Cruz</li>
                        <li>Bernard Dela Peña</li>
                        <li>Carlos Lim</li>
                        <li>Daisy Fernando</li>
                    </ul>
                </div>

                <!-- Add more group cards as needed -->

            </div>
        </main>
    </div>

</body>
</html>