<?php
$servername = "localhost";  
$username = "root"; 
$password = ""; 
$dbname = "nc_homecare"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
$lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$phonenumber = mysqli_real_escape_string($conn, $_POST['phonenumber']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO user (first_name, last_name, email, phone, password)
        VALUES ('$firstname', '$lastname', '$email', '$phonenumber', '$hashed_password')";

if ($conn->query($sql) === TRUE) {
    $user_id = $conn->insert_id; 
    $sql = "SELECT user_id, first_name, email FROM user WHERE user_id = $user_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];

        header("Location: ../pages/admin/useradmin.php");
        exit();
    }
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
