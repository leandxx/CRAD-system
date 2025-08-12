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
    .progress-track {
      height: 8px;
    }
    .milestone-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    .timeline-item:not(:last-child)::after {
      content: '';
      position: absolute;
      left: 19px;
      top: 30px;
      height: calc(100% - 30px);
      width: 2px;
      background-color: #E5E7EB;
    }
    .gantt-bar {
      transition: all 0.3s ease;
    }
    .hover-details {
      opacity: 0;
      transition: opacity 0.2s ease;
    }
    .hover-parent:hover .hover-details {
      opacity: 1;
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
        <h1 class="text-2xl font-bold text-gray-800">Faculty Progress Tracking</h1>
        <div class="flex items-center space-x-4">
          <button class="p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 relative">
            <i class="fas fa-bell"></i>
            <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
          </button>
          <button class="flex items-center space-x-2 focus:outline-none">
            <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600">
              <i class="fas fa-user"></i>
            </div>
            <span class="hidden md:inline">John D. Researcher</span>
          </button>
        </div>
      </header>

      <!-- Main Content Area -->
      <main class="flex-1 overflow-y-auto p-6 bg-gray-100">
        <!-- Progress Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Active Projects</h3>
                <p class="text-2xl font-bold mt-1">8</p>
              </div>
              <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-project-diagram text-blue-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">On Track</h3>
                <p class="text-2xl font-bold mt-1">5</p>
              </div>
              <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Needs Attention</h3>
                <p class="text-2xl font-bold mt-1">2</p>
              </div>
              <div class="bg-yellow-100 p-3 rounded-full">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Behind Schedule</h3>
                <p class="text-2xl font-bold mt-1">1</p>
              </div>
              <div class="bg-red-100 p-3 rounded-full">
                <i class="fas fa-clock text-red-600 text-xl"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter and Search Section -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="relative">
              <input type="text" placeholder="Search projects..." 
                     class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <select class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option>Filter by status</option>
              <option>On Track</option>
              <option>Needs Attention</option>
              <option>Behind Schedule</option>
            </select>
            <select class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option>Filter by program</option>
              <option>BSIT</option>
              <option>BSCS</option>
              <option>BSIS</option>
            </select>
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
              <i class="fas fa-filter mr-2"></i> Apply Filters
            </button>
          </div>
        </div>

        <!-- Project Progress Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- Project Card 1 -->
          <div class="bg-white rounded-lg shadow overflow-hidden milestone-card transition-all duration-300">
            <div class="p-5 border-b border-gray-200 flex justify-between items-center">
              <div>
                <h2 class="text-lg font-semibold">AI Learning System</h2>
                <p class="text-sm text-gray-500">Group 5 - BSIT 4A</p>
              </div>
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                On Track
              </span>
            </div>
            <div class="p-5">
              <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-500 mb-1">
                  <span>Project Progress</span>
                  <span>65% complete</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div class="bg-blue-600 h-2.5 rounded-full progress-track" style="width: 65%"></div>
                </div>
              </div>
              
              <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Current Phase</h3>
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-3">
                    <i class="fas fa-flask"></i>
                  </div>
                  <div>
                    <p class="font-medium">Implementation Phase</p>
                    <p class="text-xs text-gray-500">Started May 1, 2023</p>
                  </div>
                </div>
              </div>
              
              <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Next Milestone</h3>
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mr-3">
                    <i class="fas fa-check"></i>
                  </div>
                  <div>
                    <p class="font-medium">System Testing</p>
                    <p class="text-xs text-gray-500">Due June 15, 2023</p>
                  </div>
                </div>
              </div>
              
              <div class="flex justify-between pt-4 border-t border-gray-200">
                <button class="text-sm bg-blue-50 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-100">
                  <i class="fas fa-eye mr-2"></i> View Details
                </button>
                <button class="text-sm bg-green-50 text-green-600 px-4 py-2 rounded-lg hover:bg-green-100">
                  <i class="fas fa-comment mr-2"></i> Send Feedback
                </button>
              </div>
            </div>
          </div>
          
          <!-- Project Card 2 -->
          <div class="bg-white rounded-lg shadow overflow-hidden milestone-card transition-all duration-300">
            <div class="p-5 border-b border-gray-200 flex justify-between items-center">
              <div>
                <h2 class="text-lg font-semibold">Blockchain Academic Records</h2>
                <p class="text-sm text-gray-500">Group 3 - BSCS 4B</p>
              </div>
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                Needs Attention
              </span>
            </div>
            <div class="p-5">
              <!-- Similar structure as Project Card 1 -->
              <!-- Progress bar, current phase, next milestone, etc. -->
            </div>
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

    // Initialize hover effects for Gantt chart
    document.addEventListener('DOMContentLoaded', function() {
      const ganttBars = document.querySelectorAll('.gantt-bar');
      ganttBars.forEach(bar => {
        bar.addEventListener('mouseenter', function() {
          this.style.height = '4px';
          this.style.top = '2px';
        });
        bar.addEventListener('mouseleave', function() {
          this.style.height = '2px';
          this.style.top = '3px';
        });
      });
    });
  </script>
</body>
</html>