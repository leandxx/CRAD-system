<?php
// Database connection settings
$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "crad_system";
$port = 3308; // Use 3306 if you're not using a custom port

// Connect to the database
$conn = mysqli_connect($servername, $username, $password, $database, $port);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
