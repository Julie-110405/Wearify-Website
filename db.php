<?php
// DATABASE CONNECTION FILE
$servername = "localhost";
$username = "root";
$password = ""; // default XAMPP
$database = "wearify_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
