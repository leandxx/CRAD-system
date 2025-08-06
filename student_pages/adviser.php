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
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
        }
        .availability-dot {
            width: 10px;
            height: 10px;
            display: inline-block;
            border-radius: 50%;
            margin-right: 6px;
        }
        .available { background-color: var(--success); }
        .busy { background-color: var(--danger); }
        .away { background-color: var(--warning); }
        
        .message-card {
            transition: all 0.3s ease;
            border-radius: 0.75rem;
        }
        .message-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .chat-container {
            height: 400px;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        
        .rating-star {
            color: #e5e7eb;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 2rem;
        }
        .rating-star:hover, 
        .rating-star.active {
            color: #f59e0b;
            transform: scale(1.1);
        }
        
        .communication-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .communication-card:hover {
            transform: translateX(5px);
            border-left-color: var(--primary);
            background: linear-gradient(to right, rgba(59, 130, 246, 0.05), transparent);
        }
        
        .schedule-card {
            transition: all 0.3s ease;
        }
        .schedule-card:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .floating-btn {
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
        }
        .floating-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        .pulse { animation: pulse 2s infinite; }
        
        .tab-content { display: none; }
        .tab-content.active { 
            display: block;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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
                    My Adviser
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

            <main class="flex-1 overflow-y-auto p-6">
                <!-- Adviser Profile Header -->
                <div class="profile-header rounded-xl text-white p-6 mb-6 relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute right-0 top-0 w-32 h-32 rounded-full bg-white opacity-20 transform translate-x-1/2 -translate-y-1/2"></div>
                        <div class="absolute right-0 bottom-0 w-64 h-64 rounded-full bg-white opacity-10 transform translate-x-1/2 translate-y-1/2"></div>
                    </div>
                    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center">
                        <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                            <div class="w-24 h-24 rounded-full bg-white flex items-center justify-center text-blue-600 text-4xl font-bold shadow-md">
                                AR
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold">Prof. Anna Reyes</h2>
                                    <p class="text-blue-100 mb-2">Professor of Computer Science</p>
                                    <div class="flex items-center">
                                        <span class="availability-dot available"></span>
                                        <span>Available for meetings</span>
                                    </div>
                                </div>
                                <div class="flex space-x-2 mt-4 md:mt-0">
                                    <button class="bg-white/90 hover:bg-white text-blue-600 px-4 py-2 rounded-lg font-medium flex items-center transition">
                                        <i class="fas fa-phone-alt mr-2"></i> Call
                                    </button>
                                    <button class="bg-white/90 hover:bg-white text-blue-600 px-4 py-2 rounded-lg font-medium flex items-center transition">
                                        <i class="fas fa-envelope mr-2"></i> Email
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex border-b border-gray-200 mb-6">
                    <button class="tab-btn px-6 py-3 font-medium text-blue-600 border-b-2 border-blue-600 flex items-center" data-tab="overview">
                        <i class="fas fa-home mr-2"></i> Overview
                    </button>
                    <button class="tab-btn px-6 py-3 font-medium text-gray-500 hover:text-blue-600 flex items-center" data-tab="meetings">
                        <i class="fas fa-calendar-alt mr-2"></i> Meetings
                    </button>
                    <button class="tab-btn px-6 py-3 font-medium text-gray-500 hover:text-blue-600 flex items-center" data-tab="messages">
                        <i class="fas fa-comments mr-2"></i> Messages
                    </button>
                    <button class="tab-btn px-6 py-3 font-medium text-gray-500 hover:text-blue-600 flex items-center" data-tab="documents">
                        <i class="fas fa-file-alt mr-2"></i> Documents
                    </button>
                </div>

                <!-- Overview Tab -->
                <div id="overview-tab" class="tab-content active">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Column -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Adviser Details Card -->
                            <div class="bg-white rounded-xl shadow-md p-6 schedule-card">
                                <div class="flex items-center mb-4">
                                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                                        <i class="fas fa-info-circle text-blue-600"></i>
                                    </div>
                                    <h2 class="text-xl font-bold">Adviser Details</h2>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Department</label>
                                        <p class="font-medium">Computer Science Department</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Specialization</label>
                                        <p class="font-medium">Artificial Intelligence, Machine Learning</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                                        <p class="font-medium">anna.reyes@school.edu</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Phone</label>
                                        <p class="font-medium">+1 (555) 123-4567</p>
                                    </div>
                                </div>
                                
                                <h3 class="text-lg font-medium mb-3 flex items-center">
                                    <i class="fas fa-clock text-blue-500 mr-2"></i> Availability Schedule
                                </h3>
                                <div class="space-y-3">
                                    <div class="flex items-center p-3 bg-blue-50 rounded-lg">
                                        <div class="w-24 font-medium">Monday</div>
                                        <div class="flex-1">10:00 AM - 2:00 PM</div>
                                        <button class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                    </div>
                                    <div class="flex items-center p-3 bg-blue-50 rounded-lg">
                                        <div class="w-24 font-medium">Wednesday</div>
                                        <div class="flex-1">1:00 PM - 4:00 PM</div>
                                        <button class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                    </div>
                                    <div class="flex items-center p-3 bg-blue-50 rounded-lg">
                                        <div class="w-24 font-medium">Friday</div>
                                        <div class="flex-1">9:00 AM - 12:00 PM</div>
                                        <button class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Upcoming Meetings -->
                            <div class="bg-white rounded-xl shadow-md p-6 schedule-card">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                                            <i class="fas fa-calendar-check text-blue-600"></i>
                                        </div>
                                        <h2 class="text-xl font-bold">Upcoming Meetings</h2>
                                    </div>
                                    <button class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                                        <i class="fas fa-plus mr-1"></i> New Meeting
                                    </button>
                                </div>
                                
                                <div class="space-y-4">
                                    <!-- Meeting 1 -->
                                    <div class="flex items-start p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition">
                                        <div class="flex-shrink-0 mt-1 mr-4">
                                            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-video"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-medium text-gray-900">Proposal Discussion</h3>
                                            <p class="text-sm text-gray-500 mt-1">
                                                <i class="far fa-clock mr-1"></i> Tomorrow, 10:30 AM - 11:00 AM
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                <i class="fas fa-video mr-1"></i> Zoom Meeting
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0 ml-4">
                                            <button class="p-2 text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Meeting 2 -->
                                    <div class="flex items-start p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition">
                                        <div class="flex-shrink-0 mt-1 mr-4">
                                            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-user-friends"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-medium text-gray-900">Chapter 1 Review</h3>
                                            <p class="text-sm text-gray-500 mt-1">
                                                <i class="far fa-clock mr-1"></i> Friday, September 15, 9:00 AM - 9:30 AM
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                <i class="fas fa-map-marker-alt mr-1"></i> CS Department, Room 205
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0 ml-4">
                                            <button class="p-2 text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Communication Panel -->
                            <div class="bg-white rounded-xl shadow-md p-6">
                                <div class="flex items-center mb-4">
                                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                                        <i class="fas fa-comments text-blue-600"></i>
                                    </div>
                                    <h2 class="text-xl font-bold">Quick Actions</h2>
                                </div>
                                
                                <div class="space-y-3">
                                    <div class="communication-card flex items-center p-4 bg-blue-50 rounded-lg cursor-pointer">
                                        <div class="p-3 bg-blue-100 text-blue-600 rounded-full mr-3">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium">Send Email</h3>
                                            <p class="text-sm text-gray-500">Direct email to adviser</p>
                                        </div>
                                    </div>
                                    
                                    <div class="communication-card flex items-center p-4 bg-blue-50 rounded-lg cursor-pointer">
                                        <div class="p-3 bg-blue-100 text-blue-600 rounded-full mr-3">
                                            <i class="fas fa-comment-dots"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium">Instant Message</h3>
                                            <p class="text-sm text-gray-500">Chat in real-time</p>
                                        </div>
                                    </div>
                                    
                                    <div class="communication-card flex items-center p-4 bg-blue-50 rounded-lg cursor-pointer">
                                        <div class="p-3 bg-blue-100 text-blue-600 rounded-full mr-3">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium">Document Review</h3>
                                            <p class="text-sm text-gray-500">Request feedback</p>
                                        </div>
                                    </div>
                                    
                                    <div class="communication-card flex items-center p-4 bg-blue-50 rounded-lg cursor-pointer">
                                        <div class="p-3 bg-blue-100 text-blue-600 rounded-full mr-3">
                                            <i class="fas fa-question-circle"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium">Ask a Question</h3>
                                            <p class="text-sm text-gray-500">Get quick advice</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Messages -->
                            <div class="bg-white rounded-xl shadow-md p-6">
                                <div class="flex items-center mb-4">
                                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                                        <i class="fas fa-exchange-alt text-blue-600"></i>
                                    </div>
                                    <h2 class="text-xl font-bold">Recent Messages</h2>
                                </div>
                                
                                <div class="chat-container mb-4 space-y-3">
                                    <!-- Message from adviser -->
                                    <div class="message-card bg-blue-50 rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="font-medium text-blue-600">Prof. Reyes</span>
                                            <span class="text-xs text-gray-500">2 hours ago</span>
                                        </div>
                                        <p class="text-gray-700">I've reviewed your proposal draft. The methodology section needs more detail about your data collection process.</p>
                                        <div class="mt-2 flex">
                                            <a href="#" class="text-xs text-blue-600 hover:underline mr-3">
                                                <i class="fas fa-paperclip mr-1"></i> Review_Notes.pdf
                                            </a>
                                            <a href="#" class="text-xs text-blue-600 hover:underline">
                                                <i class="fas fa-reply mr-1"></i> Reply
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Message from student -->
                                    <div class="message-card bg-gray-50 rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="font-medium text-gray-600">You</span>
                                            <span class="text-xs text-gray-500">1 day ago</span>
                                        </div>
                                        <p class="text-gray-700">Attached is the latest version of my proposal. Could you review when you have time?</p>
                                        <div class="mt-2">
                                            <a href="#" class="text-xs text-blue-600 hover:underline">
                                                <i class="fas fa-paperclip mr-1"></i> Research_Proposal_v2.docx
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="border-t pt-4">
                                    <h3 class="font-medium mb-2">Send Quick Message</h3>
                                    <div class="flex">
                                        <input type="text" placeholder="Type your message..." 
                                               class="flex-1 border border-gray-300 rounded-l-lg px-4 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-lg transition">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Meetings Tab -->
                <div id="meetings-tab" class="tab-content">
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <div class="bg-blue-100 p-3 rounded-full mr-4">
                                    <i class="fas fa-calendar-check text-blue-600"></i>
                                </div>
                                <h2 class="text-xl font-bold">Schedule a Meeting</h2>
                            </div>
                            <div class="flex items-center space-x-3">
                                <button class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                    <i class="fas fa-history mr-2"></i> View Past Meetings
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Meeting Date</label>
                                <input type="date" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Meeting Time</label>
                                <select class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option>10:00 AM - 10:30 AM</option>
                                    <option>10:30 AM - 11:00 AM</option>
                                    <option>11:00 AM - 11:30 AM</option>
                                    <option>1:00 PM - 1:30 PM</option>
                                    <option>1:30 PM - 2:00 PM</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2">Meeting Purpose</label>
                                <select class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option>Proposal Discussion</option>
                                    <option>Chapter Review</option>
                                    <option>Methodology Consultation</option>
                                    <option>Defense Preparation</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2">Meeting Type</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:border-blue-500">
                                        <input type="radio" name="meeting_type" class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                        <div class="ml-3">
                                            <i class="fas fa-video text-blue-500 text-xl mr-2"></i>
                                            <span>Virtual Meeting</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:border-blue-500">
                                        <input type="radio" name="meeting_type" class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                        <div class="ml-3">
                                            <i class="fas fa-user-friends text-blue-500 text-xl mr-2"></i>
                                            <span>In-Person</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2">Additional Notes</label>
                                <textarea rows="3" placeholder="Provide any additional details about your meeting request..." 
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end space-x-3">
                            <button class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                            <button class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:from-blue-600 hover:to-indigo-700 transition flex items-center shadow-md">
                                <i class="fas fa-paper-plane mr-2"></i> Request Meeting
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages Tab -->
                <div id="messages-tab" class="tab-content">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="p-5 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-xl font-bold flex items-center">
                                <i class="fas fa-comments text-blue-500 mr-3"></i>
                                Messages with Prof. Reyes
                            </h2>
                            <button class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                                <i class="fas fa-plus mr-1"></i> New Conversation
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-4">
                            <!-- Conversation List -->
                            <div class="border-r border-gray-200">
                                <div class="p-4 border-b border-gray-200">
                                    <input type="text" placeholder="Search messages..." 
                                           class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <i class="fas fa-search absolute left-7 top-7 text-gray-400"></i>
                                </div>
                                <div class="overflow-y-auto" style="height: 500px;">
                                    <!-- Conversation 1 -->
                                    <div class="p-4 border-b border-gray-200 hover:bg-blue-50 cursor-pointer">
                                        <div class="flex justify-between items-start mb-1">
                                            <h3 class="font-medium">Proposal Feedback</h3>
                                            <span class="text-xs text-gray-500">2h ago</span>
                                        </div>
                                        <p class="text-sm text-gray-500 truncate">Regarding the methodology section in your proposal...</p>
                                    </div>
                                    
                                    <!-- Conversation 2 -->
                                    <div class="p-4 border-b border-gray-200 bg-blue-100">
                                        <div class="flex justify-between items-start mb-1">
                                            <h3 class="font-medium">Meeting Schedule</h3>
                                            <span class="text-xs text-gray-500">1d ago</span>
                                        </div>
                                        <p class="text-sm text-gray-500 truncate">Let's schedule a meeting next week to discuss...</p>
                                    </div>
                                    
                                    <!-- Conversation 3 -->
                                    <div class="p-4 border-b border-gray-200 hover:bg-blue-50 cursor-pointer">
                                        <div class="flex justify-between items-start mb-1">
                                            <h3 class="font-medium">Research Materials</h3>
                                            <span class="text-xs text-gray-500">3d ago</span>
                                        </div>
                                        <p class="text-sm text-gray-500 truncate">Here are some references that might help with...</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Message Area -->
                            <div class="lg:col-span-3">
                                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                                    <h3 class="font-medium">Proposal Feedback</h3>
                                    <div class="flex space-x-2">
                                        <button class="p-2 text-gray-500 hover:text-blue-600">
                                            <i class="fas fa-phone-alt"></i>
                                        </button>
                                        <button class="p-2 text-gray-500 hover:text-blue-600">
                                            <i class="fas fa-video"></i>
                                        </button>
                                        <button class="p-2 text-gray-500 hover:text-blue-600">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="chat-container p-4" style="height: 400px;">
                                    <!-- Message from adviser -->
                                    <div class="flex mb-4">
                                        <div class="flex-shrink-0 mr-3">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                                AR
                                            </div>
                                        </div>
                                        <div>
                                            <div class="bg-blue-50 rounded-lg p-3">
                                                <p class="text-gray-800">I've reviewed your proposal draft. The methodology section needs more detail about your data collection process.</p>
                                                <div class="mt-2">
                                                    <a href="#" class="text-xs text-blue-600 hover:underline">
                                                        <i class="fas fa-paperclip mr-1"></i> Review_Notes.pdf
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">Prof. Reyes - 2 hours ago</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Message from student -->
                                    <div class="flex mb-4 justify-end">
                                        <div class="text-right">
                                            <div class="bg-gray-100 rounded-lg p-3">
                                                <p class="text-gray-800">Thank you for the feedback. I'll revise the methodology section and include more details about the data collection process.</p>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">You - 1 hour ago</div>
                                        </div>
                                        <div class="flex-shrink-0 ml-3">
                                            <div class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center">
                                                JD
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Message from adviser -->
                                    <div class="flex mb-4">
                                        <div class="flex-shrink-0 mr-3">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                                AR
                                            </div>
                                        </div>
                                        <div>
                                            <div class="bg-blue-50 rounded-lg p-3">
                                                <p class="text-gray-800">Great! Also, don't forget to include your sampling technique and ethical considerations.</p>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">Prof. Reyes - 30 minutes ago</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-4 border-t border-gray-200">
                                    <div class="flex items-center">
                                        <button class="p-2 text-gray-500 hover:text-blue-600 mr-2">
                                            <i class="fas fa-paperclip"></i>
                                        </button>
                                        <input type="text" placeholder="Type your message..." 
                                               class="flex-1 border border-gray-300 rounded-l-lg px-4 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-lg transition">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents Tab -->
                <div id="documents-tab" class="tab-content">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <div class="bg-blue-100 p-3 rounded-full mr-4">
                                    <i class="fas fa-file-alt text-blue-600"></i>
                                </div>
                                <h2 class="text-xl font-bold">Shared Documents</h2>
                            </div>
                            <button class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center">
                                <i class="fas fa-upload mr-2"></i> Upload Document
                            </button>
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
                                    <!-- Document 1 -->
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <i class="fas fa-file-pdf text-red-500 text-xl mr-3"></i>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Research_Proposal.pdf</div>
                                                    <div class="text-sm text-gray-500">v2.1 • 1.5 MB</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-                                            2 py-1 text-xs font-medium text-gray-700 bg-gray-200 rounded-full">PDF</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-green-500 font-medium">Approved</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-500">September 10, 2023</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800 ml-2">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Document 2 -->
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <i class="fas fa-file-word text-blue-500 text-xl mr-3"></i>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Literature_Review.docx</div>
                                                    <div class="text-sm text-gray-500">v1.0 • 2.3 MB</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-200 rounded-full">DOCX</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-yellow-500 font-medium">Pending Review</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-500">September 12, 2023</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800 ml-2">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Document 3 -->
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <i class="fas fa-file-excel text-green-500 text-xl mr-3"></i>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Data_Analysis.xlsx</div>
                                                    <div class="text-sm text-gray-500">v1.2 • 3.1 MB</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-200 rounded-full">XLSX</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-green-500 font-medium">Approved</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-500">September 14, 2023</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800 ml-2">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Tab functionality
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetTab = button.getAttribute('data-tab');

                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('text-blue-600', 'border-blue-600'));
                tabContents.forEach(content => content.classList.remove('active'));

                // Add active class to the clicked button and corresponding content
                button.classList.add('text-blue-600', 'border-blue-600');
                document.getElementById(`${targetTab}-tab`).classList.add('active');
            });
        });
    </script>
</body>
</html>
