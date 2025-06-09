<?php
session_start();

$host = "localhost";
$user = "root";
$db_password = "";
$db = "mypetakom";

// Connect to the database
$data = mysqli_connect($host, $user, $db_password, $db);
if ($data === false) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['userID'], $_POST['password'], $_POST['userType'])) {
        echo "<h1>Required fields are missing</h1>";
        exit();
    }

    $userID = $_POST['userID'];
    $password = md5($_POST['password']);  // Password should be hashed (same as in DB)
    $userType = $_POST['userType'];
    
    $query = "";
    $email_field = "";
    
    // Determine which table to query based on user type
    if ($userType == "student") {
        $query = "SELECT * FROM student WHERE studentID = ? AND password = ?";
    } else {
        // For both admin and advisor (both in staff table)
        $query = "SELECT * FROM staff WHERE staffID = ? AND staffPassword = ? AND staffRole = ?";
    }
    
    $stmt = $data->prepare($query);
    
    // Bind parameters differently based on user type
    if ($userType == "student") {
        $stmt->bind_param("ss", $userID, $password);
    } else {
        $stmt->bind_param("sss", $userID, $password, $userType);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // Set session variables
        $_SESSION['userID'] = $userID;
        $_SESSION['userType'] = $userType;
        $_SESSION['userEmail'] = $row[$email_field];

        // Redirect based on userType
        if ($userType == "student") {
            header("Location: ../student_dash.php");
            exit();
        } else if ($userType == "admin") {
            header("Location: ../admin.php");
            exit();
        } else if ($userType == "advisor") {
            header("Location: ../advisor_dash.php");
            exit();
        }
    } else {
        echo "<h1>Invalid ID or password</h1>";
        echo "<p><a href='LoginForm.php'>Please log in again</a></p>";
        exit();
    }
}
?>
