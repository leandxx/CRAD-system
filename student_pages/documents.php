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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-light: #93c5fd;
            --secondary: #7c3aed;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        
        .document-card {
            transition: all 0.3s ease;
        }
        .document-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            transition: width 0.6s ease;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        .drag-area {
            border: 2px dashed #cbd5e0;
            transition: all 0.3s ease;
        }
        .drag-area.active {
            border-color: var(--primary);
            background-color: #f0f7ff;
        }
        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .tab-content.active {
            display: block;
        }
        .floating-btn {
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
        }
        .floating-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        .checklist-item {
            transition: all 0.2s ease;
        }
        .checklist-item:hover {
            transform: translateX(5px);
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
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

<body class="bg-gray-50 text-gray-800 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden h-screen">
            <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4 shadow-sm">
                <div class="flex items-center">
                  <h1 class="text-2xl md:text-3xl font-bold text-primary flex items-center">
                    Document Tracker
                </h1>   
              </div>
                <div class="flex items-center space-x-4">
                    <button class="p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 relative transition-all hover:scale-105">
                        <i class="fas fa-bell"></i>
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500 notification-dot pulse"></span>
                    </button>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <span class="hidden md:inline font-medium"><?php echo htmlspecialchars($full_name); ?></span>
                            <i class="fas fa-chevron-down text-xs opacity-70 group-hover:opacity-100 transition"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user-circle mr-2"></i>Profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-cog mr-2"></i>Settings</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <!-- Completion Progress -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-l-4 border-blue-500 transform hover:shadow-xl transition-shadow">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                        <h2 class="text-xl font-semibold flex items-center">
                            <i class="fas fa-tasks text-blue-500 mr-3"></i>
                            Document Completion Progress
                        </h2>
                        <div class="mt-2 md:mt-0">
                            <span class="text-sm font-medium text-gray-600">60% Complete</span>
                        </div>
                    </div>
                    
                    <div class="w-full bg-gray-200 progress-bar mb-4">
                        <div class="progress-fill" style="width: 60%"></div>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                        <div class="flex items-center p-2 rounded-lg bg-green-50">
                            <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                            <span class="font-medium">Approval Form</span>
                            <i class="fas fa-check-circle text-green-500 ml-auto"></i>
                        </div>
                        <div class="flex items-center p-2 rounded-lg bg-green-50">
                            <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                            <span class="font-medium">MOA</span>
                            <i class="fas fa-check-circle text-green-500 ml-auto"></i>
                        </div>
                        <div class="flex items-center p-2 rounded-lg bg-green-50">
                            <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                            <span class="font-medium">Adviser Form</span>
                            <i class="fas fa-check-circle text-green-500 ml-auto"></i>
                        </div>
                        <div class="flex items-center p-2 rounded-lg bg-yellow-50">
                            <span class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></span>
                            <span class="font-medium">Ethics Clearance</span>
                            <i class="fas fa-hourglass-half text-yellow-500 ml-auto"></i>
                        </div>
                        <div class="flex items-center p-2 rounded-lg bg-gray-100">
                            <span class="w-3 h-3 rounded-full bg-gray-400 mr-2"></span>
                            <span class="font-medium text-gray-600">Progress Reports</span>
                            <i class="fas fa-lock text-gray-400 ml-auto"></i>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex border-b border-gray-200 mb-6">
                    <button class="tab-btn px-6 py-3 font-medium text-blue-600 border-b-2 border-blue-600 flex items-center" data-tab="upload">
                        <i class="fas fa-upload mr-2"></i> Upload
                    </button>
                    <button class="tab-btn px-6 py-3 font-medium text-gray-500 hover:text-blue-600 flex items-center" data-tab="submitted">
                        <i class="fas fa-folder mr-2"></i> My Documents
                    </button>
                    <button class="tab-btn px-6 py-3 font-medium text-gray-500 hover:text-blue-600 flex items-center" data-tab="required">
                        <i class="fas fa-clipboard-list mr-2"></i> Requirements
                    </button>
                </div>

                <!-- Upload Tab -->
                <div id="upload-tab" class="tab-content active">
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                        <div class="flex items-center mb-6">
                            <div class="bg-blue-100 p-3 rounded-full mr-4">
                                <i class="fas fa-cloud-upload-alt text-blue-600 text-xl"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Upload New Document</h2>
                        </div>
                        
                        <!-- Drag & Drop Area -->
                        <div id="drag-area" class="drag-area rounded-xl p-8 text-center mb-6 cursor-pointer transition-all hover:border-blue-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-cloud-upload-alt text-5xl text-blue-400 mb-4"></i>
                                <p class="font-medium text-lg">Drag & drop files here</p>
                                <p class="text-sm text-gray-500 mt-2">or click to browse files</p>
                                <input type="file" id="file-input" class="hidden">
                            </div>
                        </div>
                        
                        <form action="#" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                        <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                                        Document Type
                                    </label>
                                    <select name="doc_type" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-blue-500 focus:border-blue-500 transition">
                                        <option value="">Select document type</option>
                                        <option value="approval">Capstone Approval Form</option>
                                        <option value="moa">Memorandum of Agreement (MOA)</option>
                                        <option value="adviser_form">Adviser Assignment Form</option>
                                        <option value="ethics">Ethical Clearance</option>
                                        <option value="progress">Progress Report</option>
                                        <option value="proposal">Research Proposal</option>
                                        <option value="final">Final Paper</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                        <i class="fas fa-code-branch text-blue-500 mr-2"></i>
                                        Version
                                    </label>
                                    <input type="text" name="version" placeholder="e.g. Draft 1, Final" 
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-blue-500 focus:border-blue-500 transition">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                    <i class="fas fa-sticky-note text-blue-500 mr-2"></i>
                                    Notes (Optional)
                                </label>
                                <textarea name="notes" rows="3" placeholder="Add any additional notes..."
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-blue-500 focus:border-blue-500 transition"></textarea>
                            </div>
                            
                            <div class="flex justify-end space-x-4">
                                <button type="reset" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center">
                                    <i class="fas fa-times mr-2"></i> Cancel
                                </button>
                                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:from-blue-600 hover:to-indigo-700 transition flex items-center shadow-md">
                                    <i class="fas fa-upload mr-2"></i> Upload Document
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Submitted Documents Tab -->
                <div id="submitted-tab" class="tab-content">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                        <div class="p-5 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between">
                            <h2 class="text-xl font-bold flex items-center mb-3 md:mb-0">
                                <i class="fas fa-folder-open text-blue-500 mr-3"></i>
                                My Submitted Documents
                            </h2>
                            <div class="relative w-full md:w-64">
                                <input type="text" placeholder="Search documents..." 
                                       class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Approved Document -->
                                    <tr class="document-card hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">MOA_Batch1.pdf</div>
                                                    <div class="text-sm text-gray-500">v1.0 • 2.4 MB</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">MOA</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="bg-green-100 text-green-800 status-badge rounded-full flex items-center">
                                                    <i class="fas fa-check-circle mr-1"></i> Approved
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <i class="far fa-clock mr-1"></i> Aug 5, 2025
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                <a href="#" class="text-blue-600 hover:text-blue-800 tooltip" data-tooltip="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="#" class="text-green-600 hover:text-green-800 tooltip" data-tooltip="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="#" class="text-gray-600 hover:text-gray-800 tooltip" data-tooltip="Share">
                                                    <i class="fas fa-share-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Pending Review Document -->
                                    <tr class="document-card hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-file-word text-blue-500 text-2xl"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Ethics_Clearance.docx</div>
                                                    <div class="text-sm text-gray-500">v2.1 • 1.8 MB</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">Ethics</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="bg-yellow-100 text-yellow-800 status-badge rounded-full flex items-center">
                                                    <i class="fas fa-hourglass-half mr-1"></i> Pending Review
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <i class="far fa-clock mr-1"></i> Sep 12, 2025
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                <a href="#" class="text-blue-600 hover:text-blue-800 tooltip" data-tooltip="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="#" class="text-green-600 hover:text-green-800 tooltip" data-tooltip="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="#" class="text-red-600 hover:text-red-800 tooltip" data-tooltip="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Rejected Document -->
                                    <tr class="document-card hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-file-excel text-green-500 text-2xl"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Progress_Report_Q3.xlsx</div>
                                                    <div class="text-sm text-gray-500">v1.2 • 3.1 MB</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Progress</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="bg-red-100 text-red-800 status-badge rounded-full flex items-center mb-1">
                                                    <i class="fas fa-exclamation-circle mr-1"></i> Revisions Required
                                                </span>
                                                <a href="#" class="text-xs text-blue-600 hover:underline">View feedback</a>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <i class="far fa-clock mr-1"></i> Jul 28, 2025
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                <a href="#" class="text-blue-600 hover:text-blue-800 tooltip" data-tooltip="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="#" class="text-green-600 hover:text-green-800 tooltip" data-tooltip="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="#" class="text-purple-600 hover:text-purple-800 tooltip" data-tooltip="Revise">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="bg-gray-50 px-6 py-4 flex flex-col md:flex-row items-center justify-between border-t border-gray-200">
                            <div class="text-sm text-gray-500 mb-2 md:mb-0">
                                Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">8</span> documents
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition flex items-center">
                                    <i class="fas fa-chevron-left mr-1"></i> Previous
                                </button>
                                <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition flex items-center">
                                    Next <i class="fas fa-chevron-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Required Documents Tab -->
                <div id="required-tab" class="tab-content">
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                        <div class="flex items-center mb-6">
                            <div class="bg-blue-100 p-3 rounded-full mr-4">
                                <i class="fas fa-clipboard-check text-blue-600 text-xl"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Required Documents Checklist</h2>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Document Requirement 1 -->
                            <div class="checklist-item flex items-start p-4 border border-gray-200 rounded-lg hover:border-blue-200 hover:bg-blue-50/50">
                                <div class="flex-shrink-0 mt-1 mr-3">
                                    <input type="checkbox" id="req1" checked class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label for="req1" class="block text-sm font-medium text-gray-900 cursor-pointer">Capstone Approval Form</label>
                                    <p class="text-sm text-gray-500 mt-1">Signed by department head and research coordinator</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Submitted
                                        </span>
                                        <a href="#" class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-file-pdf mr-1 text-red-500"></i> Download Template
                                        </a>
                                        <a href="#" class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-history mr-1"></i> View Submission History
                                        </a>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <button class="p-2 text-gray-400 hover:text-blue-600 transition">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Document Requirement 2 -->
                            <div class="checklist-item flex items-start p-4 border border-gray-200 rounded-lg hover:border-blue-200 hover:bg-blue-50/50">
                                <div class="flex-shrink-0 mt-1 mr-3">
                                    <input type="checkbox" id="req2" checked class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label for="req2" class="block text-sm font-medium text-gray-900 cursor-pointer">Memorandum of Agreement (MOA)</label>
                                    <p class="text-sm text-gray-500 mt-1">Required if working with external organizations</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Submitted
                                        </span>
                                        <a href="#" class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-file-word mr-1 text-blue-500"></i> Download Template
                                        </a>
                                        <a href="#" class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-history mr-1"></i> View Submission History
                                        </a>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <button class="p-2 text-gray-400 hover:text-blue-600 transition">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Document Requirement 3 -->
                            <div class="checklist-item flex items-start p-4 border border-gray-200 rounded-lg hover:border-blue-200 hover:bg-blue-50/50">
                                <div class="flex-shrink-0 mt-1 mr-3">
                                    <input type="checkbox" id="req3" class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label for="req3" class="block text-sm font-medium text-gray-900 cursor-pointer">Ethical Clearance</label>
                                    <p class="text-sm text-gray-500 mt-1">Required for studies involving human participants</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-exclamation-circle mr-1"></i> Pending Review
                                        </span>
                                        <a href="#" class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-file-alt mr-1 text-gray-500"></i> View Guidelines
                                        </a>
                                        <a href="#" class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-upload mr-1"></i> Upload Now
                                        </a>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <button class="p-2 text-gray-400 hover:text-blue-600 transition">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Document Requirement 4 -->
                            <div class="checklist-item flex items-start p-4 border border-gray-200 rounded-lg hover:border-blue-200 hover:bg-blue-50/50">
                                <div class="flex-shrink-0 mt-1 mr-3">
                                    <input type="checkbox" id="req4" disabled class="h-5 w-5 text-gray-400 border-gray-300 rounded">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label for="req4" class="block text-sm font-medium text-gray-500 cursor-not-allowed">Final Research Paper</label>
                                    <p class="text-sm text-gray-400 mt-1">To be submitted after defense completion</p>
                                    <div class="mt-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-lock mr-1"></i> Not Available Yet
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <button class="p-2 text-gray-300 cursor-not-allowed">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fixed bottom-8 right-8 z-10">
        <button class="floating-btn w-14 h-14 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center">
            <i class="fas fa-plus text-xl"></i>
        </button>
    </div>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all tabs and buttons
                document.querySelectorAll('.tab-btn').forEach(t => {
                    t.classList.remove('text-blue-600', 'border-blue-600');
                    t.classList.add('text-gray-500', 'hover:text-blue-600');
                });
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.remove('text-gray-500', 'hover:text-blue-600');
                this.classList.add('text-blue-600', 'border-blue-600');
                
                // Show corresponding tab content
                const tabId = this.getAttribute('data-tab') + '-tab';
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Drag and drop functionality
        const dragArea = document.getElementById('drag-area');
        const fileInput = document.getElementById('file-input');
        
        dragArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', function() {
            if (this.files.length) {
                handleFiles(this.files);
            }
        });
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dragArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dragArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dragArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dragArea.classList.add('active');
        }
        
        function unhighlight() {
            dragArea.classList.remove('active');
        }
        
        dragArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }
        
        function handleFiles(files) {
            const fileName = files[0].name;
            const fileSize = formatFileSize(files[0].size);
            const fileType = fileName.split('.').pop().toUpperCase();
            
            dragArea.innerHTML = `
                <div class="flex items-center justify-center p-4">
                    <div class="flex-shrink-0 mr-4">
                        <i class="fas fa-file-${getFileIcon(fileType)} text-3xl text-${getFileColor(fileType)}"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 truncate max-w-xs">${fileName}</p>
                        <p class="text-sm text-gray-500">${fileSize} • ${fileType} File</p>
                    </div>
                </div>
            `;
        }
        
        function getFileIcon(ext) {
            const icons = {
                'PDF': 'pdf',
                'DOC': 'word',
                'DOCX': 'word',
                'XLS': 'excel',
                'XLSX': 'excel',
                'PPT': 'powerpoint',
                'PPTX': 'powerpoint',
                'ZIP': 'archive',
                'RAR': 'archive'
            };
            return icons[ext] || 'alt';
        }
        
        function getFileColor(ext) {
            const colors = {
                'PDF': 'red',
                'DOC': 'blue',
                'DOCX': 'blue',
                'XLS': 'green',
                'XLSX': 'green',
                'PPT': 'orange',
                'PPTX': 'orange',
                'ZIP': 'gray',
                'RAR': 'gray'
            };
            return colors[ext] || 'blue';
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1) + ' ' + sizes[i]);
        }

        // Floating button to scroll to upload form
        document.querySelector('.floating-btn').addEventListener('click', function() {
            document.querySelector('.tab-btn[data-tab="upload"]').click();
            document.getElementById('upload-tab').scrollIntoView({ behavior: 'smooth' });
        });
    </script>
</body>
</html>