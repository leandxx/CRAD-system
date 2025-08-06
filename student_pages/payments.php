<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verification</title>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-y-auto h-screen">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-blue-700">Payments</h1>
                <p class="text-gray-600">Monitor your capstone-related payments and transactions</p>
            </div>

            <!-- Summary Card -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-gray-700">Outstanding Balance</h2>
                    <p class="text-2xl font-bold text-red-600 mt-2">₱1,500.00</p>
                    <p class="text-sm text-gray-500 mt-1">Due before final defense</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-gray-700">Last Payment</h2>
                    <p class="text-2xl font-bold text-green-600 mt-2">₱500.00</p>
                    <p class="text-sm text-gray-500 mt-1">Paid on August 2, 2025</p>
                </div>
            </div>

            <!-- Payment History Table -->
            <div class="bg-white p-4 rounded-lg shadow mb-6 overflow-auto">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Payment History</h2>
                <table class="min-w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <tr class="border-b">
                            <td class="px-4 py-2">August 2, 2025</td>
                            <td class="px-4 py-2">₱500.00</td>
                            <td class="px-4 py-2 text-green-600 font-medium">Approved</td>
                            <td class="px-4 py-2">Initial payment</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-2">July 18, 2025</td>
                            <td class="px-4 py-2">₱1,000.00</td>
                            <td class="px-4 py-2 text-yellow-600 font-medium">Pending</td>
                            <td class="px-4 py-2">Awaiting confirmation</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Upload Proof of Payment -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Upload Proof of Payment</h2>
                <form action="#" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select File (JPEG/PNG/PDF)</label>
                        <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Remarks (optional)</label>
                        <textarea name="remarks" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                                  placeholder="E.g. Payment for document processing..."></textarea>
                    </div>
                    <button type="submit"
                            class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition duration-200">
                        Submit Payment Proof
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>