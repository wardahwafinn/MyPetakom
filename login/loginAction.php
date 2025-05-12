<?php

    session_start();

$host = "localhost";
$user = "root";
$password = "";
$db = "web_project";

// Create connection
$data = mysqli_connect($host, $user, $password, $db);
if ($data === false) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST['username'], $_POST['password'], $_POST['userType'])) {
        echo "<h1>Required fields are missing</h1>";
        exit();
    }

    $username = $_POST['username'];
    $password = $_POST['password'];
    $userType = $_POST['userType'];

    // Use prepared statement to prevent SQL injection
    $stmt = $data->prepare("SELECT * FROM login WHERE username = ? AND password = ? AND userType = ?");
    $stmt->bind_param("sss", $username, $password, $userType);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    if ($row) {
        if ($row["userType"] == "student") {
            header("Location: student.php");
            exit();
        } else if ($row["userType"] == "admin") {
            header("Location: admin.php");
            exit();
        } else if ($row["userType"] == "advisor") {
            header("Location: advisor.php");
            exit();
        }
    } else {
        echo "<h1>Invalid username or password</h1>";
        echo "<p><a href='LoginForm.php'>Please log in again</a></p>";
        exit();
    }
}
?>
