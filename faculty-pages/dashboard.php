<?php
include('../includes/connection.php'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
  <title>CRAD Faculty Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/faculty.css">
  <style>
    .progress-bar {
      transition: width 0.6s ease;
    }
    .hover-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    .notification-dot {
      animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.5; }
      100% { opacity: 1; }
    }
  </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/faculty-sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Bar -->
      <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-3">
        <h1 class="text-2xl font-bold text-gray-800">Faculty Dashboard</h1>
        <div class="flex items-center space-x-4">
          <button class="p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 relative">
            <i class="fas fa-bell"></i>
            <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500 notification-dot"></span>
          </button>
          <button class="flex items-center space-x-2 focus:outline-none">
            <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600">
              <i class="fas fa-user"></i>
            </div>
            <span class="hidden md:inline">John D. Researcher</span>
          </button>
        </div>
      </header>

      <!-- Dashboard Content -->
      <main class="flex-1 overflow-y-auto p-6 bg-gray-100">
        <!-- Quick Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow p-6 hover-card transition-all duration-300">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Assigned Students</h3>
                <p class="text-2xl font-bold mt-1">12</p>
              </div>
              <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-users text-blue-600 text-xl"></i>
              </div>
            </div>
            <div class="mt-4">
              <a href="#" class="text-blue-600 text-sm font-medium flex items-center">
                View all <i class="fas fa-chevron-right ml-1 text-xs"></i>
              </a>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6 hover-card transition-all duration-300">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Pending Reviews</h3>
                <p class="text-2xl font-bold mt-1">5</p>
              </div>
              <div class="bg-yellow-100 p-3 rounded-full">
                <i class="fas fa-file-alt text-yellow-600 text-xl"></i>
              </div>
            </div>
            <div class="mt-4">
              <a href="#" class="text-blue-600 text-sm font-medium flex items-center">
                Review now <i class="fas fa-chevron-right ml-1 text-xs"></i>
              </a>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6 hover-card transition-all duration-300">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Upcoming Defenses</h3>
                <p class="text-2xl font-bold mt-1">3</p>
              </div>
              <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-calendar-check text-green-600 text-xl"></i>
              </div>
            </div>
            <div class="mt-4">
              <a href="#" class="text-blue-600 text-sm font-medium flex items-center">
                View schedule <i class="fas fa-chevron-right ml-1 text-xs"></i>
              </a>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6 hover-card transition-all duration-300">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Overdue Tasks</h3>
                <p class="text-2xl font-bold mt-1">2</p>
              </div>
              <div class="bg-red-100 p-3 rounded-full">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
              </div>
            </div>
            <div class="mt-4">
              <a href="#" class="text-blue-600 text-sm font-medium flex items-center">
                Take action <i class="fas fa-chevron-right ml-1 text-xs"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Assigned Students Section -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="p-5 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold">Assigned Students</h2>
                <div class="flex space-x-2">
                  <div class="relative">
                    <input type="text" placeholder="Search students..." class="pl-8 pr-4 py-2 border rounded-lg text-sm">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                  </div>
                  <select class="border rounded-lg px-3 py-2 text-sm">
                    <option>Filter by status</option>
                    <option>Proposal Phase</option>
                    <option>Data Collection</option>
                    <option>Final Defense</option>
                    <option>Completed</option>
                  </select>
                </div>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Title</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Submission</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-600">JD</span>
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">John Doe</div>
                            <div class="text-sm text-gray-500">BSIT-4A</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">AI-Based Learning System</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                          Chapter 3 Review
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        2 days ago
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        <a href="#" class="text-yellow-600 hover:text-yellow-900">Review</a>
                      </td>
                    </tr>
                    <!-- More student rows... -->
                  </tbody>
                </table>
              </div>
              <div class="bg-gray-50 px-5 py-3 flex items-center justify-between border-t border-gray-200">
                <div class="text-sm text-gray-500">
                  Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">12</span> students
                </div>
                <div class="flex space-x-2">
                  <button class="px-3 py-1 border rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                  </button>
                  <button class="px-3 py-1 border rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Next
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Sidebar - Quick Actions -->
          <div class="space-y-6">
            <!-- Pending Reviews Card -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="p-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold flex items-center">
                  <i class="fas fa-file-alt text-yellow-500 mr-2"></i>
                  Pending Reviews
                </h2>
              </div>
              <div class="divide-y divide-gray-200">
                <div class="p-4 hover:bg-gray-50 cursor-pointer">
                  <div class="flex justify-between">
                    <div>
                      <h3 class="text-sm font-medium">Chapter 2 - Literature Review</h3>
                      <p class="text-xs text-gray-500 mt-1">Group 5 - AI Learning System</p>
                    </div>
                    <span class="text-xs text-yellow-600 font-medium">3 days pending</span>
                  </div>
                  <div class="mt-3 flex justify-between items-center">
                    <button class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded flex items-center">
                      <i class="fas fa-robot mr-1"></i> AI Feedback
                    </button>
                    <button class="text-xs bg-green-50 text-green-600 px-2 py-1 rounded">
                      Review Now
                    </button>
                  </div>
                </div>
                <!-- More pending review items... -->
              </div>
              <div class="bg-gray-50 px-5 py-3 text-center">
                <a href="#" class="text-sm text-blue-600 font-medium">View all pending reviews</a>
              </div>
            </div>

            <!-- Defense Schedule Card -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="p-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold flex items-center">
                  <i class="fas fa-calendar-check text-green-500 mr-2"></i>
                  Upcoming Defenses
                </h2>
              </div>
              <div class="divide-y divide-gray-200">
                <div class="p-4">
                  <div class="flex justify-between">
                    <div>
                      <h3 class="text-sm font-medium">Proposal Defense</h3>
                      <p class="text-xs text-gray-500 mt-1">Group 3 - Blockchain System</p>
                    </div>
                    <span class="text-xs text-blue-600 font-medium">May 15, 10:00 AM</span>
                  </div>
                  <div class="mt-3 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Room 302</span>
                    <div class="flex space-x-2">
                      <button class="text-xs bg-green-50 text-green-600 px-2 py-1 rounded">
                        Confirm
                      </button>
                      <button class="text-xs bg-gray-50 text-gray-600 px-2 py-1 rounded">
                        Reschedule
                      </button>
                    </div>
                  </div>
                </div>
                <!-- More defense schedule items... -->
              </div>
              <div class="bg-gray-50 px-5 py-3 text-center">
                <a href="#" class="text-sm text-blue-600 font-medium">View full calendar</a>
              </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="p-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Quick Actions</h2>
              </div>
              <div class="grid grid-cols-2 gap-4 p-4">
                <a href="#" class="p-3 border rounded-lg text-center hover:bg-blue-50 transition-colors">
                  <i class="fas fa-upload text-blue-500 text-xl mb-2"></i>
                  <p class="text-sm font-medium">Upload Feedback</p>
                </a>
                <a href="#" class="p-3 border rounded-lg text-center hover:bg-green-50 transition-colors">
                  <i class="fas fa-download text-green-500 text-xl mb-2"></i>
                  <p class="text-sm font-medium">Download Files</p>
                </a>
                <a href="#" class="p-3 border rounded-lg text-center hover:bg-purple-50 transition-colors">
                  <i class="fas fa-calendar-plus text-purple-500 text-xl mb-2"></i>
                  <p class="text-sm font-medium">Schedule Defense</p>
                </a>
                <a href="#" class="p-3 border rounded-lg text-center hover:bg-yellow-50 transition-colors">
                  <i class="fas fa-robot text-yellow-500 text-xl mb-2"></i>
                  <p class="text-sm font-medium">AI Review Tool</p>
                </a>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- JS Toggle Function -->
  <script>
    let isCollapsed = false;

    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const title = document.getElementById('sidebar-title');
      const profile = document.getElementById('sidebar-profile');
      const textItems = document.querySelectorAll('.sidebar-text');
      const sections = document.querySelectorAll('.sidebar-section');

      isCollapsed = !isCollapsed;

      if (isCollapsed) {
        sidebar.classList.remove('w-64');
        sidebar.classList.add('w-20');
        title.classList.add('hidden');
        profile.classList.add('hidden');
        textItems.forEach(el => el.classList.add('hidden'));
        sections.forEach(el => el.classList.add('hidden'));
      } else {
        sidebar.classList.remove('w-20');
        sidebar.classList.add('w-64');
        title.classList.remove('hidden');
        profile.classList.remove('hidden');
        textItems.forEach(el => el.classList.remove('hidden'));
        sections.forEach(el => el.classList.remove('hidden'));
      }
    }

    // Notification dropdown
    document.addEventListener('DOMContentLoaded', function() {
      const notificationBtn = document.querySelector('.fa-bell').parentElement;
      const notificationDot = document.querySelector('.notification-dot');
      
      notificationBtn.addEventListener('click', function() {
        notificationDot.classList.add('hidden');
      });
    });
  </script>
</body>
</html>