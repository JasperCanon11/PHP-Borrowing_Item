<?php
// Database configuration
$servername = "localhost";   // Usually 'localhost' sa XAMPP
$username = "root";          // Default XAMPP username
$password = "";              // Default XAMPP password
$dbname = "science_lab";     // Pangalan ng database mo

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
