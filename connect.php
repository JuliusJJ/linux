<?php
// Create connection
$servername = "sql152.main-hosting.eu";
$username = "u398366948_root";
$password = "julius123";
$database= "u398366948_bni";

$conn = new mysqli($servername, $username, $password, $database);
    
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}