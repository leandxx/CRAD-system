<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Submission</title>
    <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

    <div class="min-h-screen flex">

        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden h-screen">
            <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4 shadow-sm">
                <h1 class="text-2xl md:text-3xl font-bold text-primary flex items-center">
                    Proposal Submission
                </h1>
                <div class="flex items-center space-x-4">
                    <button class="p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 relative transition-all hover:scale-105">
                        <i class="fas fa-bell"></i>
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500 notification-dot animate-pulse"></span>
                    </button>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="hidden md:inline font-medium">John D. Researcher</span>
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

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Proposal Submission Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border-l-4 border-primary transform hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center mb-4">
                        <div class="bg-primary/10 p-3 rounded-full mr-4">
                            <i class="fas fa-paper-plane text-primary text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Submit New Proposal</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-heading mr-2 text-primary/70"></i>
                                Project Title
                            </label>
                            <input type="text" placeholder="Enter your project title..." 
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:ring-primary focus:border-primary transition duration-200 px-4 py-2 border hover:border-primary/50">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-tag mr-2 text-primary/70"></i>
                                Project Type
                            </label>
                            <select class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:ring-primary focus:border-primary transition duration-200 px-4 py-2 border hover:border-primary/50">
                                <option value="">Select project type</option>
                                <option value="capstone">Capstone Project</option>
                                <option value="thesis">Thesis</option>
                                <option value="research">Research Paper</option>
                                <option value="design">Design Project</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-align-left mr-2 text-primary/70"></i>
                                Project Description
                            </label>
                            <textarea rows="5" placeholder="Describe your project in detail..." 
                                      class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:ring-primary focus:border-primary transition duration-200 px-4 py-2 border hover:border-primary/50"></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-paperclip mr-2 text-primary/70"></i>
                                Attachments
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-primary/50 transition duration-200">
                                <div class="space-y-1 text-center">
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none">
                                            <span>Upload files</span>
                                            <input type="file" class="sr-only" multiple>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PDF, DOCX up to 10MB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button class="bg-gradient-to-r from-primary to-secondary text-white px-6 py-2.5 rounded-lg hover:shadow-md transition-all duration-300 flex items-center">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Proposal
                        </button>
                    </div>
                </div>

                <!-- Submitted Proposals Section -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="bg-primary/10 p-3 rounded-full mr-4">
                                <i class="fas fa-folder-open text-primary text-xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800">Your Submitted Proposals</h2>
                        </div>
                        <div class="relative">
                            <select class="appearance-none bg-gray-100 border border-gray-200 rounded-lg px-4 py-2 pr-8 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                                <option>All Status</option>
                                <option>Pending</option>
                                <option>Approved</option>
                                <option>Needs Revision</option>
                                <option>Rejected</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-3 text-xs text-gray-500 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Proposal Cards -->
                    <div class="space-y-4">
                        <!-- Proposal 1 - Needs Revision -->
                        <div class="border rounded-xl overflow-hidden transition-all hover:shadow-md">
                            <div class="bg-gray-50 px-5 py-4 border-b flex justify-between items-center">
                                <div>
                                    <h3 class="font-bold text-lg text-gray-800 flex items-center">
                                        SCHOOL MANAGEMENT SYSTEM
                                        <span class="ml-3 text-xs px-2.5 py-0.5 rounded-full bg-warning/10 text-warning font-medium flex items-center">
                                            <i class="fas fa-exclamation-circle mr-1"></i> Needs Revision
                                        </span>
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <i class="far fa-clock mr-1"></i> Submitted on: August 5, 2025
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-user-tie mr-1"></i> Adviser: Dr. Smith
                                    </p>
                                </div>
                                <button class="text-primary hover:text-secondary transition">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </div>
                            
                            <!-- Feedback Section -->
                            <div class="p-5 bg-gradient-to-r from-gray-50 to-white">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-medium text-gray-900">Adviser Feedback</h4>
                                            <span class="text-xs text-gray-500">
                                                <i class="far fa-clock mr-1"></i> August 6, 2025
                                            </span>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-700 bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                                            <p>The project concept is solid but needs more specific technical details. Please expand on:</p>
                                            <ul class="list-disc pl-5 mt-2 space-y-1">
                                                <li>Database architecture diagram</li>
                                                <li>User authentication flow</li>
                                                <li>Testing methodology</li>
                                            </ul>
                                            <p class="mt-2">Resubmit by <span class="font-medium">August 15, 2025</span>.</p>
                                        </div>
                                        <div class="mt-3 flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning/10 text-warning">
                                                <i class="fas fa-exclamation-circle mr-1"></i> Requires Revision
                                            </span>
                                            <button class="ml-auto bg-primary/10 hover:bg-primary/20 text-primary text-sm px-3 py-1 rounded-lg transition flex items-center">
                                                <i class="fas fa-edit mr-1"></i> Revise Proposal
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Proposal 2 - Approved -->
                        <div class="border rounded-xl overflow-hidden transition-all hover:shadow-md">
                            <div class="bg-gray-50 px-5 py-4 border-b flex justify-between items-center">
                                <div>
                                    <h3 class="font-bold text-lg text-gray-800 flex items-center">
                                        E-COMMERCE PLATFORM FOR LOCAL BUSINESSES
                                        <span class="ml-3 text-xs px-2.5 py-0.5 rounded-full bg-success/10 text-success font-medium flex items-center">
                                            <i class="fas fa-check-circle mr-1"></i> Approved
                                        </span>
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <i class="far fa-clock mr-1"></i> Submitted on: July 28, 2025
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-user-tie mr-1"></i> Adviser: Dr. Smith
                                    </p>
                                </div>
                                <button class="text-primary hover:text-secondary transition">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </div>
                            
                            <div class="p-5">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-success/10 flex items-center justify-center text-success">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-medium text-gray-900">Adviser Feedback</h4>
                                            <span class="text-xs text-gray-500">
                                                <i class="far fa-clock mr-1"></i> July 30, 2025
                                            </span>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-700 bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                                            <p>Excellent proposal! The project has:</p>
                                            <ul class="list-disc pl-5 mt-2 space-y-1">
                                                <li>Clear objectives and scope</li>
                                                <li>Well-defined methodology</li>
                                                <li>Realistic timeline</li>
                                                <li>Thorough budget breakdown</li>
                                            </ul>
                                            <p class="mt-2 font-medium text-success">Approved for implementation. Good work!</p>
                                        </div>
                                        <div class="mt-3 flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success/10 text-success">
                                                <i class="fas fa-check-circle mr-1"></i> Approved
                                            </span>
                                            <div class="ml-auto flex space-x-2">
                                                <button class="bg-primary/10 hover:bg-primary/20 text-primary text-sm px-3 py-1 rounded-lg transition flex items-center">
                                                    <i class="fas fa-download mr-1"></i> Download
                                                </button>
                                                <button class="bg-success/10 hover:bg-success/20 text-success text-sm px-3 py-1 rounded-lg transition flex items-center">
                                                    <i class="fas fa-tasks mr-1"></i> Next Steps
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State (commented out) -->
                    <!-- <div class="text-center py-12">
                        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-folder-open text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">No proposals submitted yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by submitting your first research proposal.</p>
                        <button class="mt-4 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition">
                            Create New Proposal
                        </button>
                    </div> -->
                </div>
            </main>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fixed bottom-6 right-6">
        <button class="w-14 h-14 rounded-full bg-gradient-to-br from-primary to-secondary text-white shadow-lg hover:shadow-xl transition-all flex items-center justify-center transform hover:scale-110">
            <i class="fas fa-plus text-xl"></i>
        </button>
    </div>

</body>
</html>