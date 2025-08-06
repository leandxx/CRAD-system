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
    .payment-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    .verified-badge {
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.7; }
      100% { opacity: 1; }
    }
    .unpaid-row {
      background-color: #FEF2F2;
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
        <h1 class="text-2xl font-bold text-gray-800">Oral Defense Payment Verification</h1>
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
        <!-- Payment Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Paid Defenses</h3>
                <p class="text-2xl font-bold mt-1">18</p>
              </div>
              <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Unpaid Defenses</h3>
                <p class="text-2xl font-bold mt-1">5</p>
              </div>
              <div class="bg-red-100 p-3 rounded-full">
                <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Pending Verification</h3>
                <p class="text-2xl font-bold mt-1">3</p>
              </div>
              <div class="bg-yellow-100 p-3 rounded-full">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-gray-500 text-sm font-medium">Total This Month</h3>
                <p class="text-2xl font-bold mt-1">26</p>
              </div>
              <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Verification Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
          <div class="p-5 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold">Defense Payment Records</h2>
            <div class="flex space-x-2">
              <div class="relative">
                <input type="text" placeholder="Search students..." 
                      class="pl-8 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
              </div>
              <select class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option>All Defenses</option>
                <option>Proposal Defense</option>
                <option>Final Defense</option>
              </select>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student/Group</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Defense Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt No.</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verification</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <!-- Paid Example -->
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-600">JD</span>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">John Doe</div>
                        <div class="text-sm text-gray-500">Group 5 - BSIT-4A</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">Proposal Defense</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">May 15, 2023</div>
                    <div class="text-sm text-gray-500">10:00 AM</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      Paid
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    May 10, 2023
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    OR-2023-00124
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="verified-badge px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                      <i class="fas fa-check-circle mr-1"></i> Verified
                    </span>
                  </td>
                </tr>
                
                <!-- Unpaid Example -->
                <tr class="unpaid-row">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                        <span class="text-gray-600">JS</span>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Jane Smith</div>
                        <div class="text-sm text-gray-500">Group 3 - BSCS-4B</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">Final Defense</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">May 18, 2023</div>
                    <div class="text-sm text-gray-500">1:30 PM</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                      Unpaid
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    -
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    -
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button class="text-xs bg-yellow-50 text-yellow-700 px-3 py-1 rounded hover:bg-yellow-100">
                      Send Reminder
                    </button>
                  </td>
                </tr>
                
                <!-- Pending Verification Example -->
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                        <span class="text-purple-600">RJ</span>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Robert Johnson</div>
                        <div class="text-sm text-gray-500">Group 7 - BSIT-4A</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">Proposal Defense</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">May 20, 2023</div>
                    <div class="text-sm text-gray-500">9:00 AM</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                      Paid
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    May 15, 2023
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    OR-2023-00135
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button class="text-xs bg-green-50 text-green-700 px-3 py-1 rounded hover:bg-green-100">
                      Verify Payment
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="bg-gray-50 px-5 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="text-sm text-gray-500">
              Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">26</span> records
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

        <!-- Payment Verification Modal (would appear when verifying a payment) -->
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden" id="verificationModal">
          <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-5 border-b border-gray-200">
              <h3 class="text-lg font-semibold">Verify Payment Receipt</h3>
            </div>
            <div class="p-5">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Student Name</label>
                <p class="text-sm bg-gray-50 p-2 rounded">Robert Johnson (Group 7 - BSIT-4A)</p>
              </div>
              
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Defense Details</label>
                <p class="text-sm bg-gray-50 p-2 rounded">Proposal Defense - May 20, 2023 at 9:00 AM</p>
              </div>
              
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Number</label>
                <input type="text" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="OR-2023-00135">
              </div>
              
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount</label>
                <input type="text" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="₱500.00">
              </div>
              
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
                <input type="date" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="2023-05-15">
              </div>
              
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Receipt Copy (Optional)</label>
                <div class="mt-1 flex items-center">
                  <input type="file" class="hidden" id="receiptUpload">
                  <label for="receiptUpload" class="cursor-pointer bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm font-medium text-gray-700">
                    <i class="fas fa-upload mr-2"></i> Choose File
                  </label>
                  <span class="ml-2 text-sm text-gray-500" id="fileName">No file chosen</span>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-5 py-3 flex justify-end space-x-3">
              <button class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                Cancel
              </button>
              <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Confirm Verification
              </button>
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

    // Payment verification modal functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Show modal when verify button is clicked
      const verifyButtons = document.querySelectorAll('button:contains("Verify Payment")');
      verifyButtons.forEach(button => {
        button.addEventListener('click', function() {
          document.getElementById('verificationModal').classList.remove('hidden');
        });
      });
      
      // Hide modal when cancel is clicked
      document.querySelector('#verificationModal button:contains("Cancel")').addEventListener('click', function() {
        document.getElementById('verificationModal').classList.add('hidden');
      });
      
      // File upload name display
      document.getElementById('receiptUpload').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
        document.getElementById('fileName').textContent = fileName;
      });
    });
  </script>
</body>
</html>