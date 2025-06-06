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

$type = $_POST['type'];
$id = $_POST['id'];
$name = $_POST['name'];
$email = $_POST['email'];
$extra = $_POST['extra'];

if ($type == 'student') {
    $phone = $_POST['phone'];
    $stmt = $conn->prepare("UPDATE student SET studentName=?, studentEmail=?, studentCard=?, studentPhoneNum=? WHERE studentID=?");
    $stmt->bind_param("sssss", $name, $email, $extra, $phone, $id);
} else {
    $stmt = $conn->prepare("UPDATE staff SET staffName=?, staffEmail=?, staffRole=? WHERE staffID=?");
    $stmt->bind_param("ssss", $name, $email, $extra, $id);
}

if ($stmt->execute()) {
header("Location: admin_manage_profile.php?status=updated");
} else {
    echo "Update failed: " . $stmt->error;
}
$conn->close();
?>
