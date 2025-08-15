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

        function toggleModal() {
            const modal = document.getElementById('proposalModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <div class="min-h-screen flex">

        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>
        

  <!-- Main Content Area -->
  <div class="flex-1 flex flex-col overflow-hidden h-screen">
            <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4 shadow-sm">
                <div class="flex items-center">
                    <h1 class="text-2xl md:text-3xl font-bold text-primary flex items-center">
                        Dashboard
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
                    <option>Draft</option>
                    <option>Submitted</option>
                    <option>Under Review</option>
                    <option>Approved</option>
                    <option>Rejected</option>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-3 text-xs text-gray-500 pointer-events-none"></i>
            </div>
        </div>

        <!-- Proposal Cards -->
        <div class="space-y-4">
            <!-- Empty State -->
            <div id="empty-state" class="text-center py-12">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-file-alt text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-700">No proposals submitted yet</h3>
                <p class="mt-1 text-gray-500">Get started by submitting your first proposal</p>
                <button onclick="toggleModal()" class="mt-4 bg-gradient-to-r from-primary to-secondary text-white px-6 py-2.5 rounded-lg hover:shadow-md transition-all duration-300 flex items-center mx-auto">
                    <i class="fas fa-paper-plane mr-2"></i> Submit Proposal
                </button>
            </div>

            <!-- Proposal 1 - Draft -->
            <div class="border rounded-xl overflow-hidden transition-all hover:shadow-md hidden" id="proposal-card">
                <div class="bg-gray-50 px-5 py-4 border-b flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800 flex items-center">
                            SCHOOL MANAGEMENT SYSTEM
                            <span class="ml-3 text-xs px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 font-medium flex items-center">
                                <i class="fas fa-edit mr-1"></i> Draft
                            </span>
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="far fa-clock mr-1"></i> Last saved: August 5, 2025
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="text-primary hover:text-secondary transition">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-500 hover:text-red-700 transition">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Proposal 2 - Submitted -->
            <div class="border rounded-xl overflow-hidden transition-all hover:shadow-md hidden" id="proposal-card">
                <div class="bg-gray-50 px-5 py-4 border-b flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800 flex items-center">
                            E-COMMERCE PLATFORM FOR LOCAL BUSINESSES
                            <span class="ml-3 text-xs px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium flex items-center">
                                <i class="fas fa-paper-plane mr-1"></i> Submitted
                            </span>
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="far fa-clock mr-1"></i> Submitted on: July 28, 2025
                            <span class="mx-2">•</span>
                            <i class="fas fa-user-tie mr-1"></i> Adviser: Dr. Smith
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="text-primary hover:text-secondary transition">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="text-gray-500 hover:text-gray-700 transition">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Proposal 3 - Under Review -->
            <div class="border rounded-xl overflow-hidden transition-all hover:shadow-md hidden" id="proposal-card">
                <div class="bg-gray-50 px-5 py-4 border-b flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800 flex items-center">
                            AI-BASED LEARNING PLATFORM
                            <span class="ml-3 text-xs px-2.5 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium flex items-center">
                                <i class="fas fa-hourglass-half mr-1"></i> Under Review
                            </span>
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="far fa-clock mr-1"></i> Submitted on: August 1, 2025
                            <span class="mx-2">•</span>
                            <i class="fas fa-user-tie mr-1"></i> Adviser: Prof. Johnson
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="text-primary hover:text-secondary transition">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="text-gray-500 hover:text-gray-700 transition">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Proposal 4 - Approved -->
            <div class="border rounded-xl overflow-hidden transition-all hover:shadow-md hidden" id="proposal-card">
                <div class="bg-gray-50 px-5 py-4 border-b flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800 flex items-center">
                            SMART ATTENDANCE SYSTEM
                            <span class="ml-3 text-xs px-2.5 py-0.5 rounded-full bg-green-100 text-green-700 font-medium flex items-center">
                                <i class="fas fa-check-circle mr-1"></i> Approved
                            </span>
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="far fa-clock mr-1"></i> Submitted on: June 15, 2025
                            <span class="mx-2">•</span>
                            <i class="fas fa-user-tie mr-1"></i> Adviser: Dr. Williams
                            <span class="mx-2">•</span>
                            <i class="fas fa-calendar-check mr-1"></i> Approved on: June 20, 2025
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="text-primary hover:text-secondary transition">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="text-gray-500 hover:text-gray-700 transition">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal for New Proposal Submission -->
<div id="proposalModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2 p-6 max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Submit New Proposal</h2>
            <button onclick="toggleModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="proposalForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                        <i class="fas fa-heading mr-2 text-primary/70"></i>
                        Project Title *
                    </label>
                    <input type="text" required placeholder="Enter your project title..." 
                           class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:ring-primary focus:border-primary transition duration-200 px-4 py-2 border hover:border-primary/50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                        <i class="fas fa-tag mr-2 text-primary/70"></i>
                        Project Type *
                    </label>
                    <select required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:ring-primary focus:border-primary transition duration-200 px-4 py-2 border hover:border-primary/50">
                        <option value="">Select project type</option>
                        <option value="capstone">Capstone Project</option>
                        <option value="thesis">Thesis</option>
                        <option value="research">Research Paper</option>
                        <option value="design">Design Project</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                        <i class="fas fa-user-tie mr-2 text-primary/70"></i>
                        Faculty Adviser *
                    </label>
                    <select required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:ring-primary focus:border-primary transition duration-200 px-4 py-2 border hover:border-primary/50">
                        <option value="">Select faculty adviser</option>
                        <option value="1">Dr. Smith</option>
                        <option value="2">Prof. Johnson</option>
                        <option value="3">Dr. Williams</option>
                        <option value="4">Prof. Brown</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                        <i class="fas fa-align-left mr-2 text-primary/70"></i>
                        Project Description *
                    </label>
                    <textarea required rows="5" placeholder="Describe your project in detail..." 
                              class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:ring-primary focus:border-primary transition duration-200 px-4 py-2 border hover:border-primary/50"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                        <i class="fas fa-paperclip mr-2 text-primary/70"></i>
                        Attachments *
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-primary/50 transition duration-200">
                        <div class="space-y-1 text-center">
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none">
                                    <span>Upload files</span>
                                    <input type="file" required class="sr-only" multiple accept=".pdf,.doc,.docx">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PDF, DOC, DOCX up to 10MB</p>
                            <div id="file-list" class="text-xs text-left mt-2 hidden">
                                <!-- Files will be listed here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition flex items-center">
                    <i class="fas fa-save mr-2"></i> Save Draft
                </button>
                <div class="flex space-x-4">
                    <button type="button" onclick="toggleModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </button>
                    <button type="submit" class="bg-gradient-to-r from-primary to-secondary text-white px-6 py-2.5 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 flex items-center">
                        <i class="fas fa-paper-plane mr-2"></i> Submit Proposal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Floating Action Button -->
<div class="fixed bottom-6 right-6">
    <button onclick="toggleModal()" class="w-14 h-14 rounded-full bg-gradient-to-br from-primary to-secondary text-white shadow-lg hover:shadow-xl transition-all flex items-center justify-center transform hover:scale-110">
        <i class="fas fa-plus text-xl"></i>
    </button>
</div>

<script>
    // Toggle modal visibility
    function toggleModal() {
        document.getElementById('proposalModal').classList.toggle('hidden');
        document.getElementById('proposalModal').classList.toggle('flex');
    }

    // Handle file upload display
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
        const fileList = document.getElementById('file-list');
        fileList.innerHTML = '';
        
        if (this.files.length > 0) {
            fileList.classList.remove('hidden');
            for (let i = 0; i < this.files.length; i++) {
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center py-1';
                fileItem.innerHTML = `
                    <i class="fas fa-file-alt mr-2 text-gray-500"></i>
                    <span class="truncate flex-1">${this.files[i].name}</span>
                    <span class="text-gray-500 text-xs">${formatFileSize(this.files[i].size)}</span>
                `;
                fileList.appendChild(fileItem);
            }
        } else {
            fileList.classList.add('hidden');
        }
    });

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Form submission
    document.getElementById('proposalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Here you would handle the form submission via AJAX or similar
        alert('Proposal submitted successfully!');
        toggleModal();
        // Hide empty state and show proposal cards (for demo purposes)
        document.getElementById('empty-state').classList.add('hidden');
        document.querySelectorAll('#proposal-card').forEach(card => card.classList.remove('hidden'));
    });
</script>

</body>
</html>