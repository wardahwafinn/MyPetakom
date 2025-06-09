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
    <meta name="description" content="MyPetakom">
    <meta name="author" content="Wardah Wafin">
    <title>MyPetakom</title>
    <link rel="stylesheet" type="text/css" href="style/anteknya.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
</head>

<body class="background">

    <div class="sidebar">
        <a href="homepage.html"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a>
        <hr>

        <a href="home.html" class="nav-item">Home</a>
        <hr>

        <a href="profile.html" class="nav-item">Profile</a>
        <hr>

        <span class="nav-item event-title">Event</span>
        <div class="submenu">
            <a href="committee.html">&gt; Committee</a>
            <a href="registration.html">&gt; Registration</a>
            <a href="attendance.html">&gt; Attendance</a>
        </div>
    </div>

   <div class="top-right-bar">
    <a href="profile.html" class="profilename">
        <img src="images/user.png" alt="User" class="profile-icon">HI, ADVISOR
    </a>
    <a href="logout.php">
        <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
    </a>
    </div>




    <div>
        <button class="button button4">+ New Event</button>
    </div>

    <div class="metrics-container">
        <!-- Total Events Card -->
        <div class="metric-card">
            <div class="metric-title">Total Events</div>
            <div class="metric-value">24</div>
            <div class="metric-change positive">
                12% from last semester
            </div>
        </div>

        <!-- Upcoming Events Card -->
        <div class="metric-card">
            <div class="metric-title">Upcoming Events</div>
            <div class="metric-value">8</div>
            <div class="metric-change negative">
                3 less than last month
            </div>
        </div>

        <!-- Students Participation Card -->
        <div class="metric-card">
            <div class="metric-title">Students Participation</div>
            <div class="metric-value">842</div>
            <div class="metric-change positive">
                15% increase
            </div>
        </div>

        <!-- Merit Points Card -->
        <div class="metric-card">
            <div class="metric-title">Merit Points Awarded</div>
            <div class="metric-value">3540</div>
            <div class="metric-change positive">
                20% increase
            </div>
        </div>
    </div>

    <div class="graph-container">
        <img src="images/graph1.png" class="graph" />
    </div>

    <div class="graph-container2">
        <img src="images/graph2.png" class="graph2" />
    </div>

    <!--
    <footer>
        <p class="bottom">&copy; 2022 FK. All rights reserved.</p>
    </footer>
    -->

</body>

</html>
