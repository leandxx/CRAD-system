<?php
session_start();
include('../includes/connection.php');

header('Content-Type: application/json');

try {
    if (!isset($_POST['date'])) {
        echo json_encode(['error' => 'Date required']);
        exit();
    }

    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $current_date = date('Y-m-d'); // Get current date
    $current_time = date('H:i:s'); // Get current time

    $rooms_query = "SELECT id, room_name, building, 50 as capacity FROM rooms ORDER BY building, room_name";
    $rooms_result = mysqli_query($conn, $rooms_query);

    if (!$rooms_result) {
        echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
        exit();
    }

    $rooms = [];
    while ($room = mysqli_fetch_assoc($rooms_result)) {
        $schedules_query = "SELECT ds.start_time, ds.end_time, g.name as group_name, g.program, c.cluster 
                           FROM defense_schedules ds 
                           JOIN groups g ON ds.group_id = g.id 
                           LEFT JOIN clusters c ON g.cluster_id = c.id
                           WHERE ds.room_id = '{$room['id']}' 
                           AND ds.defense_date = '$date' 
                           AND ds.status IN ('scheduled', 'completed')
                           ORDER BY ds.start_time";
        $schedules_result = mysqli_query($conn, $schedules_query);
        
        $schedules = [];
        if ($schedules_result && mysqli_num_rows($schedules_result) > 0) {
            while ($schedule = mysqli_fetch_assoc($schedules_result)) {
                // If the selected date is today, filter out past schedules
                // If the selected date is in the future, show all schedules
                if ($date == $current_date) {
                    // For today: only show schedules that haven't ended yet
                    if ($schedule['end_time'] > $current_time) {
                        $schedules[] = [
                            'start_time' => date('H:i', strtotime($schedule['start_time'])),
                            'end_time' => date('H:i', strtotime($schedule['end_time'])),
                            'group_name' => $schedule['group_name'],
                            'program' => $schedule['program'] ?: 'Not specified',
                            'cluster' => $schedule['cluster'] ?: 'Not specified'
                        ];
                    }
                } else {
                    // For future dates: show all schedules
                    $schedules[] = [
                        'start_time' => date('H:i', strtotime($schedule['start_time'])),
                        'end_time' => date('H:i', strtotime($schedule['end_time'])),
                        'group_name' => $schedule['group_name'],
                        'program' => $schedule['program'] ?: 'Not specified',
                        'cluster' => $schedule['cluster'] ?: 'Not specified'
                    ];
                }
            }
        }
        
        $room['schedules'] = $schedules;
        $room['is_available'] = empty($schedules);
        $rooms[] = $room;
    }

    echo json_encode($rooms);
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>