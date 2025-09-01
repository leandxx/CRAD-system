<?php
// Simple test for room availability
include('../includes/connection.php');

$date = date('Y-m-d');
echo "Testing room availability for date: $date<br><br>";

// Get all rooms
$rooms_query = "SELECT id, room_name, building, capacity FROM rooms ORDER BY building, room_name";
$rooms_result = mysqli_query($conn, $rooms_query);

if (!$rooms_result) {
    echo "Error fetching rooms: " . mysqli_error($conn);
    exit;
}

echo "Rooms found: " . mysqli_num_rows($rooms_result) . "<br><br>";

while ($room = mysqli_fetch_assoc($rooms_result)) {
    echo "Room: {$room['room_name']} - {$room['building']}<br>";
    
    // Get scheduled defenses for this room
    $schedules_query = "SELECT ds.start_time, ds.end_time, g.name as group_name 
                       FROM defense_schedules ds 
                       JOIN groups g ON ds.group_id = g.id 
                       WHERE ds.room_id = '{$room['id']}' 
                       AND ds.defense_date = '$date' 
                       AND ds.status IN ('scheduled', 'completed')
                       ORDER BY ds.start_time";
    $schedules_result = mysqli_query($conn, $schedules_query);
    
    if (mysqli_num_rows($schedules_result) > 0) {
        echo "  Scheduled defenses:<br>";
        while ($schedule = mysqli_fetch_assoc($schedules_result)) {
            echo "    {$schedule['start_time']} - {$schedule['end_time']} ({$schedule['group_name']})<br>";
        }
    } else {
        echo "  Available all day<br>";
    }
    echo "<br>";
}
?>