<?php
session_start();
require_once('../includes/connection.php');

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/admin-login.php");
    exit();
}

// Create a simple HTML to PDF solution using browser's print functionality
// This approach doesn't require external libraries and works well for basic reports

// Get all clusters with their assigned faculty
$query = "SELECT c.*, f.fullname AS adviser_name, f.department, f.expertise
          FROM clusters c 
          LEFT JOIN faculty f ON c.faculty_id = f.id 
          ORDER BY c.program, c.cluster";
$result = mysqli_query($conn, $query);

// Get statistics
$total_clusters = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clusters"))[0];
$assigned_clusters = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clusters WHERE status = 'assigned'"))[0];
$total_faculty = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM faculty"))[0];
$assigned_faculty = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(DISTINCT faculty_id) FROM clusters WHERE faculty_id IS NOT NULL"))[0];

// Group clusters by program
$clusters_by_program = [];
while ($row = mysqli_fetch_assoc($result)) {
    $clusters_by_program[$row['program']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cluster Assignment Report - CRAD System</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4A6CF7;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #4A6CF7;
            margin: 0;
            font-size: 28px;
        }
        
        .header h2 {
            color: #666;
            margin: 5px 0;
            font-size: 18px;
            font-weight: normal;
        }
        
        .stats-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #4A6CF7;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #4A6CF7;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .program-section {
            margin-bottom: 40px;
            break-inside: avoid;
        }
        
        .program-header {
            background: linear-gradient(135deg, #4A6CF7, #3b82f6);
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .clusters-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 0 0 8px 8px;
            overflow: hidden;
        }
        
        .clusters-table th {
            background: #f1f3f4;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .clusters-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }
        
        .clusters-table tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-assigned {
            background: #d4edda;
            color: #155724;
        }
        
        .status-unassigned {
            background: #fff3cd;
            color: #856404;
        }
        
        .no-adviser {
            color: #dc3545;
            font-style: italic;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #e0e0e0;
            padding-top: 20px;
        }
        
        .print-btn {
            background: #4A6CF7;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .print-btn:hover {
            background: #3b82f6;
        }
        
        .capacity-info {
            font-size: 12px;
            color: #666;
        }
        
        .program-stats {
            background: rgba(255,255,255,0.9);
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        🖨️ Print / Save as PDF
    </button>
    
    <div class="header">
        <h1>Center for Research and Development</h1>
        <h2>Cluster Assignment Report</h2>
        <p style="margin: 10px 0; color: #666;">Generated on <?= date('F j, Y \a\t g:i A') ?></p>
    </div>
    
    <div class="stats-section">
        <h3 style="margin: 0 0 15px 0; color: #333;">📊 Overall Statistics</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?= $total_clusters ?></div>
                <div class="stat-label">Total Clusters</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= $assigned_clusters ?></div>
                <div class="stat-label">Assigned Clusters</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= $total_faculty ?></div>
                <div class="stat-label">Total Faculty</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= $assigned_faculty ?></div>
                <div class="stat-label">Active Advisers</div>
            </div>
        </div>
    </div>
    
    <?php foreach ($clusters_by_program as $program => $clusters): ?>
        <?php 
        $program_total = count($clusters);
        $program_assigned = count(array_filter($clusters, function($c) { return $c['status'] == 'assigned'; }));
        ?>
        
        <div class="program-section">
            <h3 class="program-header">
                📚 <?= htmlspecialchars($program) ?> 
                <span style="float: right; font-size: 14px; opacity: 0.9;">
                    <?= $program_assigned ?>/<?= $program_total ?> Assigned
                </span>
            </h3>
            
            <table class="clusters-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Cluster</th>
                        <th style="width: 15%;">School Year</th>
                        <th style="width: 25%;">Thesis Adviser</th>
                        <th style="width: 20%;">Department</th>
                        <th style="width: 15%;">Capacity</th>
                        <th style="width: 10%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clusters as $cluster): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($cluster['cluster']) ?></strong></td>
                        <td><?= htmlspecialchars($cluster['school_year']) ?></td>
                        <td>
                            <?php if ($cluster['adviser_name']): ?>
                                <strong><?= htmlspecialchars($cluster['adviser_name']) ?></strong>
                                <?php if ($cluster['expertise']): ?>
                                    <br><small style="color: #666;"><?= htmlspecialchars($cluster['expertise']) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="no-adviser">Not Assigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $cluster['department'] ? htmlspecialchars($cluster['department']) : '-' ?>
                        </td>
                        <td>
                            <strong><?= $cluster['student_count'] ?> / <?= $cluster['capacity'] ?></strong>
                            <div class="capacity-info">
                                <?= round(($cluster['student_count'] / $cluster['capacity']) * 100) ?>% Full
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?= $cluster['status'] == 'assigned' ? 'status-assigned' : 'status-unassigned' ?>">
                                <?= ucfirst($cluster['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($clusters_by_program)): ?>
    <div style="text-align: center; padding: 40px; color: #666;">
        <h3>No clusters found</h3>
        <p>No cluster data available to generate report.</p>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <p><strong>Center for Research and Development (CRAD) System</strong></p>
        <p>This report contains <?= $total_clusters ?> clusters across <?= count($clusters_by_program) ?> programs</p>
        <p>Report generated on <?= date('F j, Y \a\t g:i A') ?> by <?= htmlspecialchars($_SESSION['username'] ?? 'System Administrator') ?></p>
    </div>
    
    <script>
        // Auto-focus print dialog when page loads (optional)
        // window.onload = function() { window.print(); };
        
        // Add keyboard shortcut for printing
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>