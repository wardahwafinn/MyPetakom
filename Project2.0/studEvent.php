<?php 
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: loginForm.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="description" content="student Dashboard for myPetakom">
    <meta name="author" content="UMI MAISARAH BINTI MOHD AFENDI">
    <title>MyPetakom</title>
    <link rel="stylesheet" type="text/css" href="style/member.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <meta charset="UTF-8">
    <title>Student Page</title>
</head>

<body class="background">

    <div class="sidebar">
        <li class="listyle"><a href="student.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a></li>
        <hr>

        <li class="listyle"><a href="studProfile.php" class="nav-item">Profile</a></li>
        <hr>

        <li class="listyle"><a href="student.php" class="nav-item">Dashboard</a></li>
        <hr>

        <li class="listyle"><a href="studMembership.php" class="nav-item">Apply Membership</a></li>
        <hr>

        <li class="listyle"><a class="active" href="studEvent.php" class="nav-item">View Event</a></li>
        <hr>

    </div>

    <div class="top-right-bar">
        <a href="profile.html" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">HI, MAISARAH
        </a>
        <a href="logout.php">
            <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
        </a>
    </div>

<div class="h1text">
        <h1>EVENTS</h1>
    </div>



</body>
</html>