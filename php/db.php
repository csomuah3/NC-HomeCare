<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$database = "webtech_fall2024_chelsea_somuah";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
