<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Tracker</title>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800">

    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-y-auto h-screen">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-blue-700">Documents Manager</h1>
                <p class="text-gray-600">Upload or manage your capstone-related documents</p>
            </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <p class="text-gray-600 mb-4">
                        Here you can upload and view required documents related to your capstone project such as:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 mb-4">
                        <li>Capstone Approval Form</li>
                        <li>MOA (Memorandum of Agreement)</li>
                        <li>Adviser Assignment Form</li>
                        <li>Ethical Clearance</li>
                        <li>Progress Reports</li>
                    </ul>

                    <!-- File Upload Section -->
                    <form action="#" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="document">Upload Document</label>
                            <input type="file" id="document" name="document" class="block w-full border border-gray-300 rounded-md p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="doc_type">Document Type</label>
                            <select id="doc_type" name="doc_type" class="block w-full border border-gray-300 rounded-md p-2 text-sm">
                                <option value="approval">Capstone Approval Form</option>
                                <option value="moa">MOA</option>
                                <option value="adviser_form">Adviser Assignment Form</option>
                                <option value="ethics">Ethical Clearance</option>
                                <option value="progress">Progress Report</option>
                            </select>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                            Upload
                        </button>
                    </form>
                </div>

                <!-- Uploaded Documents Table -->
                <div class="mt-10 bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-medium mb-4">Submitted Documents</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border border-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 border text-left text-sm font-medium text-gray-600">Document Name</th>
                                    <th class="px-4 py-2 border text-left text-sm font-medium text-gray-600">Type</th>
                                    <th class="px-4 py-2 border text-left text-sm font-medium text-gray-600">Date Uploaded</th>
                                    <th class="px-4 py-2 border text-center text-sm font-medium text-gray-600">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Example row -->
                                <tr>
                                    <td class="px-4 py-2 border text-sm text-gray-700">MOA_Batch1.pdf</td>
                                    <td class="px-4 py-2 border text-sm text-gray-700">MOA</td>
                                    <td class="px-4 py-2 border text-sm text-gray-700">August 5, 2025</td>
                                    <td class="px-4 py-2 border text-center">
                                        <a href="#" class="text-blue-600 hover:underline text-sm">View</a>
                                    </td>
                                </tr>
                                <!-- More rows will go here -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>

    </div>

</body>

</html>