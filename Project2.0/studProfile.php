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

// Check if user is logged in and is a student
if (!isset($_SESSION['userID']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'student') {
    header("Location: LoginForm.php");
    exit();
}

// Get student information from database
$studentID = $_SESSION['userID'];
$query = "SELECT * FROM student WHERE studentID = ?";
$stmt = $data->prepare($query);
$stmt->bind_param("s", $studentID);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

mysqli_close($data);

// Check if student data exists
if (!$student) {
    echo "Student data not found.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="description" content="student Dashboard for myPetakom">
    <meta name="author" content="UMI MAISARAH BINTI MOHD AFENDI">
    <title>MyPetakom</title>
    <link rel="stylesheet" type="text/css" href="style/profile.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <meta charset="UTF-8">
    <title>Student Profile </title>
</head>
<body class="background">

    <div class="sidebar">
        <li class="listyle"><a href="student.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a></li>
        <hr>

        <li class="listyle"><a class="active" href="studProfile.php" class="nav-item">Profile</a></li>
        <hr>

        <li class="listyle"><a  href="student.php" class="nav-item">Dashboard</a></li>
        <hr>

        <li class="listyle"><a href="studMembership.php" class="nav-item">Apply Membership</a></li>
        <hr>

        <li class="listyle"><a href="student_view_events.php" class="nav-item">View Event</a></li>
        <hr>

    </div>
    <div class="top-right-bar">
        <a href="studProfile.php" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">HI, STUDENT
        </a>
        <a href="logout.php">
            <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
        </a>
    </div>
        
        <div class="profile-content">
            <div class="welcome-msg">
                <strong>Welcome, <?php echo htmlspecialchars($student['studentName']); ?>!</strong>
                <br>You are logged in as: Student (ID: <?php echo htmlspecialchars($_SESSION['userID']); ?>)
            </div>
        </div>
        
        <div>
            <img src="images/avatar.png" alt="Avatar" style=" height=150px; width:150px; border-radius:50%; justify-content: center; margin-left:50%">
        </div>
            
            <div class="profile-info">
            <div class="info-group">
                        <span class="info-label">Student ID</span>
                        <div class="info-value"><?php echo htmlspecialchars($student['studentID']); ?></div>
                </div>

                <div class="info-group">
                    <span class="info-label">Name</span>
                    <div class="info-value"><?php echo htmlspecialchars($student['studentName']); ?></div>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Email</span>
                    <div class="info-value"><?php echo htmlspecialchars($student['studentEmail']); ?></div>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Password</span>
                    <div class="info-value password-hidden">••••••••••••</div>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Card Number</span>
                    <div class="info-value"><?php echo htmlspecialchars($student['studentCard']); ?></div>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Phone Number</span>
                    <div class="info-value"><?php echo htmlspecialchars($student['studentPhoneNum']); ?></div>
                </div>
            </div>
                

        </div>
    </div>
</body>
</html>