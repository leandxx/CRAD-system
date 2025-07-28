<?php
// Database connection settings
$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "crad_system";
$port = 3308; 

// Connect to the database
$conn = mysqli_connect($servername, $username, $password, $database, $port);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
