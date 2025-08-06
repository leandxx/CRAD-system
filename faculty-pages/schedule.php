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
    .calendar-day {
      min-height: 100px;
    }
    .calendar-day.today {
      background-color: #EFF6FF;
    }
    .calendar-day.weekend {
      background-color: #F9FAFB;
    }
    .event-card {
      transition: all 0.2s ease;
    }
    .event-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .time-slot:hover {
      background-color: #EFF6FF;
    }
    .fc-event {
      cursor: pointer;
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
        <h1 class="text-2xl font-bold text-gray-800">Faculty Panel Scheduling</h1>
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
        <!-- Scheduling Dashboard -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
          <!-- Stats Cards -->
          <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Upcoming Panels</h3>
                <p class="text-2xl font-bold mt-1">5</p>
              </div>
              <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Pending Confirmations</h3>
                <p class="text-2xl font-bold mt-1">2</p>
              </div>
              <div class="bg-yellow-100 p-3 rounded-full">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Reschedule Requests</h3>
                <p class="text-2xl font-bold mt-1">1</p>
              </div>
              <div class="bg-red-100 p-3 rounded-full">
                <i class="fas fa-calendar-times text-red-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Completed This Month</h3>
                <p class="text-2xl font-bold mt-1">3</p>
              </div>
              <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Calendar and Scheduling Interface -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Calendar View -->
          <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5 border-b border-gray-200 flex justify-between items-center">
              <h2 class="text-lg font-semibold">Defense Calendar</h2>
              <div class="flex space-x-2">
                <button class="text-sm bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200">
                  <i class="fas fa-chevron-left mr-1"></i>
                </button>
                <span class="text-sm font-medium px-4 py-2">May 2023</span>
                <button class="text-sm bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200">
                  <i class="fas fa-chevron-right mr-1"></i>
                </button>
                <button class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                  Today
                </button>
              </div>
            </div>
            <div class="p-4">
              <!-- Calendar Header -->
              <div class="grid grid-cols-7 gap-1 mb-2 text-center text-sm font-medium text-gray-500">
                <div class="p-2">Sun</div>
                <div class="p-2">Mon</div>
                <div class="p-2">Tue</div>
                <div class="p-2">Wed</div>
                <div class="p-2">Thu</div>
                <div class="p-2">Fri</div>
                <div class="p-2">Sat</div>
              </div>
              
              <!-- Calendar Body -->
              <div class="grid grid-cols-7 gap-1">
                <!-- Calendar days would be generated here -->
                <!-- Example day cell -->
                <div class="border border-gray-200 calendar-day p-2">
                  <div class="text-right text-sm mb-1">14</div>
                  <div class="space-y-1">
                    <div class="event-card bg-blue-100 text-blue-800 text-xs p-1 rounded truncate">
                      <i class="fas fa-user-graduate mr-1"></i> Group 5 Defense
                    </div>
                  </div>
                </div>
                <!-- More day cells... -->
              </div>
            </div>
          </div>

          <!-- Upcoming Panels -->
          <div class="space-y-6">
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="p-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Upcoming Defenses</h2>
              </div>
              <div class="divide-y divide-gray-200">
                <div class="p-4 hover:bg-gray-50 cursor-pointer">
                  <div class="flex justify-between">
                    <div>
                      <h3 class="text-sm font-medium">Group 5 - Proposal Defense</h3>
                      <p class="text-xs text-gray-500 mt-1">AI Learning System</p>
                    </div>
                    <span class="text-xs text-blue-600 font-medium">May 15, 10:00 AM</span>
                  </div>
                  <div class="mt-3 flex items-center text-xs text-gray-500">
                    <i class="fas fa-map-marker-alt mr-2"></i> Room 302
                  </div>
                  <div class="mt-2 flex justify-between">
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Confirmed</span>
                    <button class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded hover:bg-gray-200">
                      Details
                    </button>
                  </div>
                </div>
                <!-- More defense items... -->
              </div>
              <div class="bg-gray-50 px-5 py-3 text-center">
                <a href="#" class="text-sm text-blue-600 font-medium">View all scheduled defenses</a>
              </div>
            </div>

            <!-- Schedule New Panel -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="p-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Schedule New Panel</h2>
              </div>
              <div class="p-4">
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Select Group</label>
                  <select class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option>Choose a group...</option>
                    <option>Group 3 - Blockchain System</option>
                    <option>Group 5 - AI Learning System</option>
                    <option>Group 7 - IoT Monitoring</option>
                  </select>
                </div>
                
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Defense Type</label>
                  <select class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option>Proposal Defense</option>
                    <option>Final Defense</option>
                    <option>Progress Review</option>
                  </select>
                </div>
                
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Date & Time</label>
                  <div class="grid grid-cols-2 gap-2">
                    <input type="date" class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <input type="time" class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </div>
                </div>
                
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                  <select class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option>1 hour</option>
                    <option>1.5 hours</option>
                    <option>2 hours</option>
                  </select>
                </div>
                
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
                  <select class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option>Room 302</option>
                    <option>Room 305</option>
                    <option>Room 401</option>
                    <option>Room 402</option>
                  </select>
                </div>
                
                <button class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                  Schedule Defense
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Panelist Availability Section -->
        <div class="bg-white rounded-lg shadow overflow-hidden mt-6">
          <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Panelist Availability</h2>
          </div>
          <div class="p-4">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thu</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fri</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                          <i class="fas fa-user-tie text-gray-600"></i>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">Dr. Smith</div>
                          <div class="text-sm text-gray-500">Computer Science</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        9AM-12PM
                      </span>
                    </td>
                    <!-- More availability cells... -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <button class="text-blue-600 hover:text-blue-900">Request</button>
                    </td>
                  </tr>
                  <!-- More faculty rows... -->
                </tbody>
              </table>
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

    // Calendar navigation would be implemented here
    document.addEventListener('DOMContentLoaded', function() {
      // This would be where calendar functionality is initialized
      // For a real implementation, you might use a library like FullCalendar
      
      // Simple example of making time slots clickable
      const timeSlots = document.querySelectorAll('.time-slot');
      timeSlots.forEach(slot => {
        slot.addEventListener('click', function() {
          alert('Time slot selected: ' + this.dataset.time);
        });
      });
    });
  </script>
</body>
</html>