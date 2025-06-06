<?php
$servername = "localhost";
$username = "root"; // update if needed
$password = "";     // update if needed
$dbname = "mypetakom";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$type = $_GET['type'];

if ($type == 'student') {
    $conn->query("DELETE FROM student WHERE studentID = '$id'");
} else {
    $conn->query("DELETE FROM staff WHERE staffID = '$id'");
}

header("Location: admin_manage_profile.php?status=deleted");
?>
