<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$db = "mypetakom";

// Connect to the database
$data = mysqli_connect($host, $user, $password, $db);
if ($data === false) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['userID'], $_POST['password'], $_POST['userType'])) {
        echo "<h1>Required fields are missing</h1>";
        exit();
    }

    $userID = $_POST['userID'];
    $entered_password = $_POST['password'];  // Keep original password for verification
    $userType = $_POST['userType'];
    
    $query = "";
    $email_field = "";
    
    // Determine which table to query based on user type
    if ($userType == "student") {
        $query = "SELECT * FROM student WHERE studentID = ?";
        $email_field = "studentEmail";
    } else {
        // For both admin and advisor (both in staff table)
        $query = "SELECT * FROM staff WHERE staffID = ? AND staffRole = ?";
        $email_field = "staffEmail";
    }
    
    $stmt = $data->prepare($query);
    
    // Bind parameters differently based on user type
    if ($userType == "student") {
        $stmt->bind_param("s", $userID);
    } else {
        $stmt->bind_param("ss", $userID, $userType);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // Get the stored hashed password from database
        $stored_password = "";
        if ($userType == "student") {
            $stored_password = $row['password'];
        } else {
            $stored_password = $row['staffPassword'];
        }
        
        // Verify the entered password against the stored hash
        if (password_verify($entered_password, $stored_password)) {
            // Password is correct - set session variables
            $_SESSION['userID'] = $userID;
            $_SESSION['userType'] = $userType;
            $_SESSION['userEmail'] = $row[$email_field];

            // Redirect based on userType
            if ($userType == "student") {
                header("Location: student.php");
                exit();
            } else if ($userType == "admin") {
                header("Location: admin.php");
                exit();
            } else if ($userType == "advisor") {
                header("Location: advisor_dash.php");
                exit();
            }
        } else {
            // Password is incorrect
            echo "<h1>Invalid ID or password</h1>";
            echo "<p><a href='LoginForm.php'>Please log in again</a></p>";
            exit();
        }
    } else {
        // User not found
        echo "<h1>Invalid ID or password</h1>";
        echo "<p><a href='LoginForm.php'>Please log in again</a></p>";
        exit();
    }
    
    $stmt->close();
}

mysqli_close($data);
?>