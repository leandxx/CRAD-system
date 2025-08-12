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
    .document-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .status-badge {
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
    }
    .annotation-toolbar {
      transition: all 0.3s ease;
    }
    .progress-bar {
      transition: width 0.6s ease;
    }
    .ai-suggestion {
      border-left: 3px solid #3B82F6;
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
        <h1 class="text-2xl font-bold text-gray-800">Faculty Proposal Review</h1>
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
        <!-- Filter and Search Section -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="relative">
              <input type="text" placeholder="Search proposals..." 
                     class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <select class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option>Filter by status</option>
              <option>Pending Review</option>
              <option>Needs Revision</option>
              <option>Approved</option>
              <option>Rejected</option>
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

        <!-- Proposal Review Dashboard -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
          <!-- Stats Cards -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Pending Review</h3>
                <p class="text-2xl font-bold mt-1">7</p>
              </div>
              <div class="bg-yellow-100 p-3 rounded-full">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Awaiting Revision</h3>
                <p class="text-2xl font-bold mt-1">3</p>
              </div>
              <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-edit text-blue-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Completed Reviews</h3>
                <p class="text-2xl font-bold mt-1">12</p>
              </div>
              <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Proposals List -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
          <div class="p-5 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold">Proposals for Review</h2>
            <div class="flex space-x-2">
              <button class="text-sm bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
              </button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group/Student</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proposal Title</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Your Action</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AI Analysis</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-600">G5</span>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Group 5</div>
                        <div class="text-sm text-gray-500">BSIT-4A</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">AI-Based Learning System</div>
                    <div class="text-sm text-gray-500">Chapter 1-3</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    May 12, 2023
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                      Pending Review
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                      Start Review
                    </button>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      Available
                    </span>
                  </td>
                </tr>
                <!-- More proposal rows... -->
              </tbody>
            </table>
          </div>
          <div class="bg-gray-50 px-5 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="text-sm text-gray-500">
              Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">12</span> proposals
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

        <!-- Review Interface (shown when a proposal is selected) -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
          <div class="p-5 border-b border-gray-200 bg-blue-50">
            <div class="flex justify-between items-center">
              <div>
                <h2 class="text-lg font-semibold">Reviewing: AI-Based Learning System</h2>
                <p class="text-sm text-gray-600">Group 5 - BSIT-4A | Submitted: May 12, 2023</p>
              </div>
              <div class="flex space-x-2">
                <button class="text-sm bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200">
                  <i class="fas fa-download mr-2"></i> Download
                </button>
                <button class="text-sm bg-green-100 text-green-700 px-4 py-2 rounded-lg hover:bg-green-200">
                  <i class="fas fa-print mr-2"></i> Print
                </button>
              </div>
            </div>
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-4">
            <!-- Document Viewer -->
            <div class="lg:col-span-3 p-4 border-r border-gray-200">
              <div class="document-viewer border rounded-lg p-4 h-[600px] overflow-y-auto">
                <h3 class="text-xl font-bold mb-4">AI-Based Learning System</h3>
                <h4 class="text-lg font-semibold mb-2">Chapter 1: Introduction</h4>
                <p class="mb-4">This proposal outlines the development of an AI-based learning system that will...</p>
                <!-- More document content would appear here -->
              </div>
              
              <!-- Annotation Toolbar -->
              <div class="annotation-toolbar bg-gray-50 p-3 rounded-lg mt-4 flex flex-wrap gap-2">
                <button class="p-2 bg-blue-100 text-blue-700 rounded hover:bg-blue-200" title="Highlight">
                  <i class="fas fa-highlighter"></i>
                </button>
                <button class="p-2 bg-red-100 text-red-700 rounded hover:bg-red-200" title="Add Comment">
                  <i class="fas fa-comment"></i>
                </button>
                <button class="p-2 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200" title="Suggest Revision">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="p-2 bg-green-100 text-green-700 rounded hover:bg-green-200" title="Approve Section">
                  <i class="fas fa-check"></i>
                </button>
                <div class="ml-auto flex space-x-2">
                  <button class="p-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200" title="Previous Page">
                    <i class="fas fa-arrow-left"></i>
                  </button>
                  <span class="p-2 text-sm">Page 1 of 12</span>
                  <button class="p-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200" title="Next Page">
                    <i class="fas fa-arrow-right"></i>
                  </button>
                </div>
              </div>
            </div>
            
            <!-- Review Panel -->
            <div class="p-4">
              <div class="mb-6">
                <h3 class="font-semibold mb-2">Review Actions</h3>
                <div class="space-y-2">
                  <button class="w-full text-left p-2 bg-blue-50 text-blue-700 rounded hover:bg-blue-100">
                    <i class="fas fa-check-circle mr-2"></i> Approve Proposal
                  </button>
                  <button class="w-full text-left p-2 bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100">
                    <i class="fas fa-edit mr-2"></i> Request Revisions
                  </button>
                  <button class="w-full text-left p-2 bg-red-50 text-red-700 rounded hover:bg-red-100">
                    <i class="fas fa-times-circle mr-2"></i> Reject Proposal
                  </button>
                </div>
              </div>
              
              <div class="mb-6">
                <h3 class="font-semibold mb-2">AI Suggestions</h3>
                <div class="ai-suggestion bg-blue-50 p-3 rounded-lg mb-3">
                  <p class="text-sm"><strong>Methodology Section:</strong> Consider adding more details about data collection procedures.</p>
                </div>
                <div class="ai-suggestion bg-blue-50 p-3 rounded-lg">
                  <p class="text-sm"><strong>Literature Review:</strong> Include more recent studies (past 3 years) on AI in education.</p>
                </div>
                <button class="w-full mt-2 text-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                  Generate More Suggestions
                </button>
              </div>
              
              <div>
                <h3 class="font-semibold mb-2">Your Feedback</h3>
                <textarea class="w-full border rounded-lg p-3 h-32" placeholder="Enter your detailed feedback here..."></textarea>
                <button class="w-full mt-3 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                  Submit Review
                </button>
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

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
      // This would initialize any tooltips if using a library like Bootstrap
      // For this example, we'll just add a simple hover effect
      const annotationButtons = document.querySelectorAll('.annotation-toolbar button');
      annotationButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
          this.style.transform = 'scale(1.1)';
        });
        button.addEventListener('mouseleave', function() {
          this.style.transform = 'scale(1)';
        });
      });
    });
  </script>
</body>
</html>