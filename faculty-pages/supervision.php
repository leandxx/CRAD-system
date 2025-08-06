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
    .status-badge {
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
    }
    .document-link:hover {
      text-decoration: underline;
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
        <h1 class="text-2xl font-bold text-gray-800">Faculty Student Supervision</h1>
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
        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
              <input type="text" placeholder="Search students or groups..." 
                     class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <select class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option>Filter by status</option>
              <option>Proposal Phase</option>
              <option>Data Collection</option>
              <option>Analysis</option>
              <option>Final Defense</option>
              <option>Completed</option>
            </select>
            <select class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option>Filter by academic year</option>
              <option>2023-2024</option>
              <option>2022-2023</option>
              <option>2021-2022</option>
            </select>
          </div>
        </div>

        <!-- Student Groups Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- Group Card 1 -->
          <div class="bg-white rounded-lg shadow hover-card transition-all duration-300">
            <div class="p-5 border-b border-gray-200">
              <div class="flex justify-between items-start">
                <div>
                  <h2 class="text-lg font-semibold">Group 5 - AI Learning System</h2>
                  <p class="text-sm text-gray-500 mt-1">BSIT 4A Capstone Project</p>
                </div>
                <span class="status-badge bg-blue-100 text-blue-800 rounded-full">Active</span>
              </div>
            </div>
            <div class="p-5">
              <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Project Progress</h3>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div class="bg-blue-600 h-2.5 rounded-full progress-bar" style="width: 65%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                  <span>Chapter 3</span>
                  <span>65% complete</span>
                </div>
              </div>
              
              <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Members</h3>
                <div class="flex flex-wrap gap-2">
                  <span class="bg-gray-100 px-3 py-1 rounded-full text-sm">John Doe (Leader)</span>
                  <span class="bg-gray-100 px-3 py-1 rounded-full text-sm">Jane Smith</span>
                  <span class="bg-gray-100 px-3 py-1 rounded-full text-sm">Robert Johnson</span>
                </div>
              </div>
              
              <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Recent Documents</h3>
                <ul class="space-y-2">
                  <li class="flex items-center">
                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                    <a href="#" class="text-blue-600 document-link">Chapter_2_Literature_Review.pdf</a>
                    <span class="text-xs text-gray-500 ml-auto">3 days ago</span>
                  </li>
                  <li class="flex items-center">
                    <i class="fas fa-file-word text-blue-500 mr-2"></i>
                    <a href="#" class="text-blue-600 document-link">Research_Proposal.docx</a>
                    <span class="text-xs text-gray-500 ml-auto">1 week ago</span>
                  </li>
                </ul>
              </div>
              
              <div class="flex justify-between pt-4 border-t border-gray-200">
                <button class="text-sm bg-blue-50 text-blue-600 px-4 py-2 rounded-lg flex items-center hover:bg-blue-100">
                  <i class="fas fa-comment-alt mr-2"></i> Send Message
                </button>
                <button class="text-sm bg-green-50 text-green-600 px-4 py-2 rounded-lg flex items-center hover:bg-green-100">
                  <i class="fas fa-calendar-check mr-2"></i> Schedule Meeting
                </button>
                <button class="text-sm bg-purple-50 text-purple-600 px-4 py-2 rounded-lg flex items-center hover:bg-purple-100">
                  <i class="fas fa-eye mr-2"></i> View Details
                </button>
              </div>
            </div>
          </div>

          <!-- Group Card 2 -->
          <div class="bg-white rounded-lg shadow hover-card transition-all duration-300">
            <div class="p-5 border-b border-gray-200">
              <div class="flex justify-between items-start">
                <div>
                  <h2 class="text-lg font-semibold">Group 3 - Blockchain System</h2>
                  <p class="text-sm text-gray-500 mt-1">BSCS 4B Capstone Project</p>
                </div>
                <span class="status-badge bg-yellow-100 text-yellow-800 rounded-full">Needs Review</span>
              </div>
            </div>
            <div class="p-5">
              <!-- Similar content structure as Group Card 1 -->
              <!-- Progress, members, documents, etc. -->
            </div>
          </div>
        </div>

        <!-- Detailed Student Supervision Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
          <div class="p-5 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold">Student Supervision Details</h2>
            <button class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
              <i class="fas fa-plus mr-2"></i> Add New Note
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Meeting</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasks Completed</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pending Items</th>
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
                    <div class="text-sm text-gray-900">Group 5</div>
                    <div class="text-sm text-gray-500">AI Learning System</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">May 10, 2023</div>
                    <div class="text-sm text-gray-500">Advisory Meeting</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      Chapter 2 Approved
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                      Chapter 3 Review
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a href="#" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-comment-alt"></i></a>
                    <a href="#" class="text-green-600 hover:text-green-900 mr-3"><i class="fas fa-calendar-alt"></i></a>
                    <a href="#" class="text-purple-600 hover:text-purple-900"><i class="fas fa-file-upload"></i></a>
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

        <!-- Meeting Notes and Feedback Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Recent Meetings -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5 border-b border-gray-200">
              <h2 class="text-lg font-semibold flex items-center">
                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                Recent Advisory Meetings
              </h2>
            </div>
            <div class="divide-y divide-gray-200">
              <div class="p-4 hover:bg-gray-50 cursor-pointer">
                <div class="flex justify-between">
                  <div>
                    <h3 class="text-sm font-medium">Progress Review - Chapter 3</h3>
                    <p class="text-xs text-gray-500 mt-1">Group 5 - AI Learning System</p>
                  </div>
                  <span class="text-xs text-gray-500">May 10, 2023</span>
                </div>
                <div class="mt-3 text-sm text-gray-600">
                  <p>Discussed methodology section revisions. Students need to clarify data collection procedures.</p>
                </div>
                <div class="mt-3 flex justify-end">
                  <button class="text-xs bg-gray-50 text-gray-600 px-2 py-1 rounded">
                    View Notes
                  </button>
                </div>
              </div>
              <!-- More meeting items... -->
            </div>
            <div class="bg-gray-50 px-5 py-3 text-center">
              <a href="#" class="text-sm text-blue-600 font-medium">View all meetings</a>
            </div>
          </div>

          <!-- Feedback Given -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5 border-b border-gray-200">
              <h2 class="text-lg font-semibold flex items-center">
                <i class="fas fa-comment-dots text-green-500 mr-2"></i>
                Recent Feedback Given
              </h2>
            </div>
            <div class="divide-y divide-gray-200">
              <div class="p-4 hover:bg-gray-50 cursor-pointer">
                <div class="flex justify-between">
                  <div>
                    <h3 class="text-sm font-medium">Chapter 2 Review</h3>
                    <p class="text-xs text-gray-500 mt-1">Group 3 - Blockchain System</p>
                  </div>
                  <span class="text-xs text-gray-500">May 8, 2023</span>
                </div>
                <div class="mt-3 text-sm text-gray-600">
                  <p>Literature review needs more recent sources (past 3 years). Please reorganize by themes.</p>
                </div>
                <div class="mt-3 flex justify-between items-center">
                  <span class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded">Pending Revision</span>
                  <button class="text-xs bg-gray-50 text-gray-600 px-2 py-1 rounded">
                    View Details
                  </button>
                </div>
              </div>
              <!-- More feedback items... -->
            </div>
            <div class="bg-gray-50 px-5 py-3 text-center">
              <a href="#" class="text-sm text-blue-600 font-medium">View all feedback</a>
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

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  </script>
</body>
</html>