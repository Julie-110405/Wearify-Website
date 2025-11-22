<?php
// DATABASE CONNECTION FILE

// Change these only if you changed the XAMPP default settings
$servername = "localhost";
$username = "root";
$password = ""; // default: empty
$database = "wearify_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// If you see no error, connection is successful
?>