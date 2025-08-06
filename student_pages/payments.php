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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .notification-dot {
            position: absolute;
            top: 0;
            right: 0;
            height: 8px;
            width: 8px;
            border-radius: 50%;
            background-color: red;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(0.95); opacity: 1; }
        }
        
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background: #e0e0e0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            transition: width 0.5s ease-in-out;
        }
        
        .payment-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .payment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .payment-card.approved {
            border-left-color: #10b981;
        }
        
        .payment-card.pending {
            border-left-color: #f59e0b;
        }
        
        .file-upload {
            border: 2px dashed #d1d5db;
            transition: all 0.3s ease;
        }
        
        .file-upload:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }
        
        .file-upload.dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        
        .floating-notification {
            animation: floatUp 0.5s ease-out forwards;
        }
        
        @keyframes floatUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Added scrollable content area */
        .content-area {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 2rem;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        secondary: '#7c3aed',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden h-screen">
            <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4 shadow-sm sticky top-0 z-10">
                <h1 class="text-2xl md:text-3xl font-bold text-primary flex items-center">
                    My Payments
                </h1>
                <div class="flex items-center space-x-4">
                    <button class="p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 relative" id="notificationBtn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-dot"></span>
                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-md shadow-lg py-1 z-20">
                            <div class="px-4 py-2 border-b border-gray-200 bg-blue-50 rounded-t-md">
                                <p class="text-sm font-medium text-blue-700">Notifications</p>
                            </div>
                            <div class="max-h-60 overflow-y-auto">
                                <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-100">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 bg-blue-100 p-2 rounded-full">
                                            <i class="fas fa-money-bill-wave text-blue-500 text-xs"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="font-medium">Payment Approved</p>
                                            <p class="text-xs text-gray-500 mt-1">Your payment of ₱500.00 has been approved</p>
                                            <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 bg-yellow-100 p-2 rounded-full">
                                            <i class="fas fa-clock text-yellow-500 text-xs"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="font-medium">Payment Pending</p>
                                            <p class="text-xs text-gray-500 mt-1">Your recent payment is under review</p>
                                            <p class="text-xs text-gray-400 mt-1">1 day ago</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="px-4 py-2 border-t border-gray-200 bg-gray-50 rounded-b-md">
                                <a href="#" class="text-xs text-blue-600 hover:text-blue-800">View all notifications</a>
                            </div>
                        </div>
                    </button>
                    <div class="relative" id="profileDropdown">
                        <button class="flex items-center space-x-2 focus:outline-none group">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <span class="hidden md:inline font-medium group-hover:text-blue-600 transition">John D. Researcher</span>
                            <i class="fas fa-chevron-down text-xs text-gray-500 group-hover:text-blue-600 transition"></i>
                        </button>
                        <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user-circle mr-2"></i> Profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-cog mr-2"></i> Settings</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content Area -->
            <div class="content-area">
                <!-- Summary Cards -->
                <div class="px-6 mb-6 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white p-5 rounded-xl shadow-sm hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Outstanding Balance</p>
                                    <p class="text-2xl font-bold text-red-600 mt-1">₱1,500.00</p>
                                </div>
                                <div class="bg-red-100 p-3 rounded-full">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Due before final defense</span>
                                    <span>Aug 30, 2025</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 60%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white p-5 rounded-xl shadow-sm hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Last Payment</p>
                                    <p class="text-2xl font-bold text-green-600 mt-1">₱500.00</p>
                                </div>
                                <div class="bg-green-100 p-3 rounded-full">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Paid on</span>
                                    <span>Aug 2, 2025</span>
                                </div>
                                <div class="text-xs bg-green-50 text-green-600 px-2 py-1 rounded inline-block">
                                    <i class="fas fa-check mr-1"></i> Approved
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white p-5 rounded-xl shadow-sm hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Next Payment Due</p>
                                    <p class="text-2xl font-bold text-blue-600 mt-1">₱1,000.00</p>
                                </div>
                                <div class="bg-blue-100 p-3 rounded-full">
                                    <i class="fas fa-calendar-day text-blue-500"></i>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Due by</span>
                                    <span>Sep 15, 2025</span>
                                </div>
                                <div class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded inline-block">
                                    <i class="fas fa-clock mr-1"></i> Upcoming
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="px-6 mb-6">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-history mr-2 text-blue-500"></i>
                                Payment History
                            </h2>
                            <div class="relative">
                                <button class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                            </div>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <!-- Payment Item 1 -->
                            <div class="payment-card approved px-6 py-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="bg-green-100 p-3 rounded-full mr-4">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium">Initial Payment</p>
                                            <p class="text-sm text-gray-500">Aug 2, 2025 • 10:30 AM</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-green-600">+₱500.00</p>
                                        <p class="text-xs text-gray-500">Approved</p>
                                    </div>
                                </div>
                                <div class="mt-3 ml-16">
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-receipt mr-1 text-blue-400"></i> Receipt #PAY-2025-082-001
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Payment Item 2 -->
                            <div class="payment-card pending px-6 py-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="bg-yellow-100 p-3 rounded-full mr-4">
                                            <i class="fas fa-clock text-yellow-500"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium">Second Payment</p>
                                            <p class="text-sm text-gray-500">July 18, 2025 • 2:15 PM</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-yellow-600">+₱1,000.00</p>
                                        <p class="text-xs text-gray-500">Pending review</p>
                                    </div>
                                </div>
                                <div class="mt-3 ml-16">
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-receipt mr-1 text-blue-400"></i> Receipt #PAY-2025-071-002
                                    </p>
                                    <button class="text-xs text-blue-600 hover:text-blue-800 mt-1 inline-flex items-center">
                                        <i class="fas fa-info-circle mr-1"></i> View submission details
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-3 bg-gray-50 text-center">
                            <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                <i class="fas fa-chevron-down mr-1"></i> Show all payments
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Upload Proof of Payment -->
                <div class="px-6 mb-6">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-cloud-upload-alt mr-2 text-blue-500"></i>
                                Submit New Payment
                            </h2>
                        </div>
                        <div class="p-6">
                            <form action="#" method="POST" enctype="multipart/form-data" class="space-y-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment For</label>
                                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                                        <option>Select payment purpose</option>
                                        <option>Thesis Defense Fee</option>
                                        <option>Document Processing</option>
                                        <option>Library Clearance</option>
                                        <option>Other Fees</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₱</span>
                                        <input type="number" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent" placeholder="0.00">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Proof of Payment</label>
                                    <div class="file-upload rounded-lg p-6 text-center cursor-pointer" id="dropZone">
                                        <input type="file" id="fileInput" name="proof" accept=".jpg,.jpeg,.png,.pdf" class="hidden">
                                        <div class="mb-3">
                                            <i class="fas fa-file-upload text-4xl text-blue-400"></i>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-1">Drag & drop your file here or click to browse</p>
                                        <p class="text-xs text-gray-500">JPEG, PNG, or PDF (Max 5MB)</p>
                                        <div id="fileNameDisplay" class="mt-3 text-sm font-medium text-blue-600 hidden">
                                            <i class="fas fa-paperclip mr-1"></i>
                                            <span id="fileName"></span>
                                            <button type="button" class="ml-2 text-red-500 hover:text-red-700" id="removeFile">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes (optional)</label>
                                    <textarea name="remarks" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent" placeholder="Any special instructions or details about this payment..."></textarea>
                                </div>
                                
                                <div class="pt-2">
                                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300 shadow-md hover:shadow-lg flex items-center justify-center">
                                        <i class="fas fa-paper-plane mr-2"></i> Submit Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div> <!-- End of scrollable content area -->
        </div>
    </div>

    <!-- Floating Notification (example) -->
    <div id="floatingNotification" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center floating-notification hidden">
        <i class="fas fa-check-circle mr-2"></i>
        <span>Payment submitted successfully!</span>
    </div>

    <script>
        // Toggle notification dropdown
        document.getElementById('notificationBtn').addEventListener('click', function() {
            document.getElementById('notificationDropdown').classList.toggle('hidden');
        });
        
        // Toggle profile dropdown
        document.getElementById('profileDropdown').addEventListener('click', function(e) {
            if (e.target.closest('button')) {
                const dropdown = this.querySelector('div.hidden');
                dropdown.classList.toggle('hidden');
            }
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#notificationBtn')) {
                document.getElementById('notificationDropdown').classList.add('hidden');
            }
            if (!e.target.closest('#profileDropdown')) {
                const profileDropdown = document.querySelector('#profileDropdown div.hidden');
                if (profileDropdown) profileDropdown.classList.add('hidden');
            }
        });
        
        // File upload interaction
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const fileName = document.getElementById('fileName');
        const removeFile = document.getElementById('removeFile');
        
        dropZone.addEventListener('click', () => fileInput.click());
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) {
                fileName.textContent = e.target.files[0].name;
                fileNameDisplay.classList.remove('hidden');
                dropZone.classList.remove('file-upload');
                dropZone.classList.add('bg-blue-50', 'border-blue-200');
            }
        });
        
        removeFile.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.value = '';
            fileNameDisplay.classList.add('hidden');
            dropZone.classList.add('file-upload');
            dropZone.classList.remove('bg-blue-50', 'border-blue-200');
        });
        
        // Drag and drop effects
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dropZone.classList.add('dragover');
        }
        
        function unhighlight() {
            dropZone.classList.remove('dragover');
        }
        
        dropZone.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            if (files.length) {
                fileName.textContent = files[0].name;
                fileNameDisplay.classList.remove('hidden');
                dropZone.classList.remove('file-upload');
                dropZone.classList.add('bg-blue-50', 'border-blue-200');
            }
        }
        
        // Example form submission feedback
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Show floating notification
            const notification = document.getElementById('floatingNotification');
            notification.classList.remove('hidden');
            
            // Hide after 5 seconds
            setTimeout(() => {
                notification.classList.add('hidden');
            }, 5000);
        });
    </script>
</body>
</html>