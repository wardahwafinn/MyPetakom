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

$type = $_POST['user_type'];
$id = $_POST['id'];
$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$card_or_role = $_POST['card_or_role'];
$phone = $_POST['phone'];

// Hash the password before storing
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

if ($type == 'student') {
    $stmt = $conn->prepare("INSERT INTO student (studentID, studentName, studentEmail, studentCard, studentPhoneNum, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $id, $name, $email, $card_or_role, $phone, $hashed_password);
} else {
    $stmt = $conn->prepare("INSERT INTO staff (staffID, staffName, staffEmail, staffRole, staffPassword) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $id, $name, $email, $card_or_role, $hashed_password);
}

if ($stmt->execute()) {
    header("Location: admin_manage_profile.php?status=added");
    exit(); // Important: stop execution after redirect
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
