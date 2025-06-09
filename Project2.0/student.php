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
    <link rel="stylesheet" type="text/css" href="style/studStyle.css">
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

        <li class="listyle"><a class="active" href="student.php" class="nav-item">Dashboard</a></li>
        <hr>

        <li class="listyle"><a href="studMembership.php" class="nav-item">Apply Membership</a></li>
        <hr>

        <li class="listyle"><a href="studEvent.php" class="nav-item">View Event</a></li>
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

    <div class="h1text">
        <h1>DASHBOARD</h1>
    </div>

    <div class="h2text">
        <h2>Recent Events</h2>
    </div>
    
    <div class="flex-container">
        <div class="container">
        <img src="images/wecare.jpg" alt="we care" class="image">
        <div class="overlay">
            <div class="text"><b>PETAKOM WE CARE FUND</b><br>Let's donate!</div>
        </div>
        </div>

        <div class="container">
            <img src="images/inova.jpg" alt="inova" class="image">
            <div class="overlay">
                <div class="text"><b>INOVA CHALLENGE</b><br>Inovatech challenge is back!</div>
            </div>
        </div>

        <div class="container">
            <img src="images/league.jpg" alt="league" class="image">
            <div class="overlay">
                <div class="text"><b>FKOM VS FIM</b><br>Let's support our FKOM athlete!</div>
            </div>
        </div>
    </div>

    <div class="h2text">
        <h2>Annoucement</h2>

    </div>
    <div>
        <ul>
            <li><b>BCS2322 SOFTWARE ENGINEERING IS NOW OPEN!</b><br>Let's register now!</li>
            <li><b>HONG LEONG FOUNDATION SCHOLARSHIP!</b><br>Let's register now!</li>
        </ul>
    </div>


</body>
</html>
