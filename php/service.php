<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'nc_homecare';

$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_service'])) {
    $service_name = trim($_POST['service_name']);
    $service_description = trim($_POST['service_description']);
    $service_price = $_POST['service_price'];

    if (empty($service_name) || empty($service_description) || empty($service_price)) {
        echo "<p>Error: All fields are required.</p>";
    } elseif (!is_numeric($service_price)) {
        echo "<p>Error: Service price must be a valid number.</p>";
    } else {
        $stmt = $connection->prepare("INSERT INTO service (service_name, service_description, service_price) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $service_name, $service_description, $service_price);

        if ($stmt->execute()) {
            echo "<p>Service successfully added!</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}
$connection->close();
