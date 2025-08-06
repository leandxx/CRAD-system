<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CRAD Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .sidebar {
      transition: all 0.3s ease;
    }
    .sidebar-item:hover {
      background-color: #1e40af;
    }
    .active-sidebar-item {
      background-color: #1e40af;
      border-left: 4px solid white;
    }
    .card-hover:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body class="bg-gray-50">
  <div class="flex h-screen">
    <!-- Simple Sidebar -->
    <div class="sidebar bg-blue-900 text-white w-64 flex-shrink-0">
      <div class="p-4 border-b border-blue-800 flex items-center space-x-2">
        <i class="fas fa-flask text-2xl"></i>
        <span class="text-xl font-bold">CRAD Admin</span>
      </div>
      
      <nav class="p-4 space-y-1">
        <a href="#" class="sidebar-item active-sidebar-item flex items-center space-x-3 p-3 rounded-lg">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
        
        <a href="#" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg">
          <i class="fas fa-users"></i>
          <span>User Management</span>
        </a>
        
        <a href="#" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg">
          <i class="fas fa-file-alt"></i>
          <span>Research Proposals</span>
        </a>
        
        <a href="#" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg">
          <i class="fas fa-user-tie"></i>
          <span>Advisers & Panels</span>
        </a>
        
        <a href="#" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg">
          <i class="fas fa-money-bill-wave"></i>
          <span>Research Grants</span>
        </a>
        
        <a href="#" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg">
          <i class="fas fa-book"></i>
          <span>Publications</span>
        </a>
        
        <a href="#" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg">
          <i class="fas fa-calendar-check"></i>
          <span>Defense Scheduling</span>
        </a>
        
        <a href="#" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg">
          <i class="fas fa-cog"></i>
          <span>Settings</span>
        </a>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Bar -->
      <header class="bg-white shadow-sm p-4 flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-800">Dashboard Overview</h1>
        <div class="flex items-center space-x-4">
          <button class="p-2 text-gray-500 hover:text-gray-700 relative">
            <i class="fas fa-bell text-xl"></i>
            <span class="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500"></span>
          </button>
          <div class="flex items-center space-x-2">
            <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Admin">
            <span class="font-medium">Admin</span>
          </div>
        </div>
      </header>

      <!-- Main Content Area -->
      <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <!-- Welcome Message -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome back!</h2>
          <p class="text-gray-600">Here's what's happening with your research administration today.</p>
        </div>
        
        <!-- Key Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow p-6 card-hover transition">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-500">Total Users</p>
                <h3 class="text-2xl font-bold text-gray-800">1,248</h3>
              </div>
              <div class="bg-blue-100 p-3 rounded-full text-blue-600">
                <i class="fas fa-users"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6 card-hover transition">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-500">Pending Proposals</p>
                <h3 class="text-2xl font-bold text-gray-800">42</h3>
              </div>
              <div class="bg-yellow-100 p-3 rounded-full text-yellow-600">
                <i class="fas fa-file-alt"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6 card-hover transition">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-500">Active Research</p>
                <h3 class="text-2xl font-bold text-gray-800">186</h3>
              </div>
              <div class="bg-green-100 p-3 rounded-full text-green-600">
                <i class="fas fa-flask"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6 card-hover transition">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-500">Upcoming Defenses</p>
                <h3 class="text-2xl font-bold text-gray-800">15</h3>
              </div>
              <div class="bg-purple-100 p-3 rounded-full text-purple-600">
                <i class="fas fa-calendar-check"></i>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Recent Activity</h3>
            <a href="#" class="text-blue-600 text-sm">View All</a>
          </div>
          
          <div class="space-y-4">
            <div class="flex items-start">
              <div class="bg-blue-100 p-2 rounded-full mr-3">
                <i class="fas fa-user-plus text-blue-600"></i>
              </div>
              <div>
                <p class="font-medium">New faculty member added</p>
                <p class="text-gray-500 text-sm">Dr. Sarah Johnson was added to the system</p>
                <p class="text-gray-400 text-xs mt-1">2 hours ago</p>
              </div>
            </div>
            
            <div class="flex items-start">
              <div class="bg-green-100 p-2 rounded-full mr-3">
                <i class="fas fa-check-circle text-green-600"></i>
              </div>
              <div>
                <p class="font-medium">Proposal approved</p>
                <p class="text-gray-500 text-sm">"AI in Education" by Jane Doe</p>
                <p class="text-gray-400 text-xs mt-1">5 hours ago</p>
              </div>
            </div>
            
            <div class="flex items-start">
              <div class="bg-yellow-100 p-2 rounded-full mr-3">
                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
              </div>
              <div>
                <p class="font-medium">Deadline reminder</p>
                <p class="text-gray-500 text-sm">Proposal submission in 3 days</p>
                <p class="text-gray-400 text-xs mt-1">1 day ago</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
          
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="#" class="bg-blue-50 p-4 rounded-lg text-center hover:bg-blue-100 transition">
              <div class="bg-blue-100 p-3 rounded-full inline-block mb-2">
                <i class="fas fa-user-plus text-blue-600"></i>
              </div>
              <p class="font-medium">Add User</p>
            </a>
            
            <a href="#" class="bg-green-50 p-4 rounded-lg text-center hover:bg-green-100 transition">
              <div class="bg-green-100 p-3 rounded-full inline-block mb-2">
                <i class="fas fa-file-import text-green-600"></i>
              </div>
              <p class="font-medium">Review Proposal</p>
            </a>
            
            <a href="#" class="bg-purple-50 p-4 rounded-lg text-center hover:bg-purple-100 transition">
              <div class="bg-purple-100 p-3 rounded-full inline-block mb-2">
                <i class="fas fa-user-tie text-purple-600"></i>
              </div>
              <p class="font-medium">Assign Panel</p>
            </a>
            
            <a href="#" class="bg-yellow-50 p-4 rounded-lg text-center hover:bg-yellow-100 transition">
              <div class="bg-yellow-100 p-3 rounded-full inline-block mb-2">
                <i class="fas fa-money-bill-wave text-yellow-600"></i>
              </div>
              <p class="font-medium">Process Grant</p>
            </a>
          </div>
        </div>
      </main>
    </div>
  </div>
</body>
</html>