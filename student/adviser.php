<?php
include('../includes/connection.php'); // DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Adviser Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <?php include('../student/student-sidebar.php'); ?>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-3xl font-semibold mb-6">Adviser Information</h1>

                <!-- Adviser Info Card -->
                <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Assigned Adviser</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-sm mb-1">Adviser Name</label>
                            <p class="bg-gray-100 border border-gray-300 rounded px-4 py-2">Prof. Anna Reyes</p>
                        </div>

                        <div>
                            <label class="block font-medium text-sm mb-1">Department</label>
                            <p class="bg-gray-100 border border-gray-300 rounded px-4 py-2">Computer Studies</p>
                        </div>

                        <div>
                            <label class="block font-medium text-sm mb-1">Email</label>
                            <p class="bg-gray-100 border border-gray-300 rounded px-4 py-2">anna.reyes@school.edu</p>
                        </div>

                        <div>
                            <label class="block font-medium text-sm mb-1">Office Hours</label>
                            <p class="bg-gray-100 border border-gray-300 rounded px-4 py-2">Mon–Fri, 10:00 AM – 2:00 PM</p>
                        </div>
                    </div>
                </div>

                <!-- Message Section -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Message Adviser</h2>
                    <form action="#" method="POST">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1" for="message">Your Message</label>
                            <textarea id="message" name="message" rows="4" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200"></textarea>
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </main>

    </div>

</body>
</html>
