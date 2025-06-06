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
    $result = $conn->query("SELECT * FROM student WHERE studentID = '$id'");
    $row = $result->fetch_assoc();
    $form = "<form method='post' action='update_user.php'>
        <input type='hidden' name='type' value='student'>
        <input type='hidden' name='id' value='{$row['studentID']}'>
        Name: <input type='text' name='name' value='{$row['studentName']}'><br>
        Email: <input type='email' name='email' value='{$row['studentEmail']}'><br>
        Card: <input type='text' name='extra' value='{$row['studentCard']}'><br>
        Phone: <input type='text' name='phone' value='{$row['studentPhoneNum']}'><br>
        <button type='submit'>Update</button>
    </form>";
} else {
    $result = $conn->query("SELECT * FROM staff WHERE staffID = '$id'");
    $row = $result->fetch_assoc();
    $form = "<form method='post' action='update_user.php'>
        <input type='hidden' name='type' value='staff'>
        <input type='hidden' name='id' value='{$row['staffID']}'>
        Name: <input type='text' name='name' value='{$row['staffName']}'><br>
        Email: <input type='email' name='email' value='{$row['staffEmail']}'><br>
        Role: <input type='text' name='extra' value='{$row['staffRole']}'><br>
        <button type='submit'>Update</button>
    </form>";
}

echo $form;
?>
