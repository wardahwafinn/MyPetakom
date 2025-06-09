<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login/loginForm.php");
    exit;
}

// Check if user is student
if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'student') {
    header("Location: login/loginForm.php");
    exit;
}

// Database connection
$host = "localhost";
$user = "root";
$db_password = "";
$db = "mypetakom";

$data = mysqli_connect($host, $user, $db_password, $db);
if ($data === false) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get current student ID
$currentStudentID = $_SESSION['userID'];

// Get student name
$studentQuery = "SELECT studentName FROM student WHERE studentID = ?";
$studentStmt = $data->prepare($studentQuery);
$studentStmt->bind_param("s", $currentStudentID);
$studentStmt->execute();
$studentResult = $studentStmt->get_result();
$studentName = "STUDENT";
if ($studentResult->num_rows > 0) {
    $studentData = $studentResult->fetch_assoc();
    $studentName = strtoupper($studentData['studentName']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="description" content="MyPetakom Student Dashboard">
    <meta name="author" content="Wardah Wafin">
    <title>MyPetakom - Student Dashboard</title>
    <link rel="stylesheet" href="style/student_dash.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
</head>

<body class="background">
    <div class="sidebar">
        <a href="student_dash.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a>
        <hr>
        <a href="profile.php" class="nav-item">Profile</a>
        <hr>
        <a href="student_dash.php" class="nav-item">Apply<br>Membership</a>
        <hr>
        <a href="student_view_events.php" class="nav-item">View Event</a>
    </div>

    <div class="top-right-bar">
        <a href="profile.php" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">
            HI, <?php echo $studentName; ?>
        </a>
        <a href="login/logout.php">
            <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
        </a>
    </div>

    <div class="main-content">
        <h1>Welcome to MyPetakom</h1>
        
        <div class="welcome-container">
            <div class="welcome-card">
                <h2>Student Dashboard</h2>
                <p>Welcome to the MyPetakom student portal. Here you can manage your committee memberships and view event details.</p>
                
                <div class="dashboard-links">
                    <a href="profile.php" class="dashboard-link">
                        <div class="link-icon">👤</div>
                        <div class="link-content">
                            <h3>My Profile</h3>
                            <p>View and edit your personal information</p>
                        </div>
                    </a>
                    
                    <a href="student_view_events.php" class="dashboard-link">
                        <div class="link-icon">📅</div>
                        <div class="link-content">
                            <h3>My Events</h3>
                            <p>View events where you are a committee member</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .welcome-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 60vh;
        }

        .welcome-card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .welcome-card h2 {
            color: #333;
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .welcome-card p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .dashboard-links {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .dashboard-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            border: 2px solid transparent;
            min-width: 250px;
        }

        .dashboard-link:hover {
            background-color: #e9ecef;
            border-color: #a90000;
            transform: translateY(-2px);
        }

        .link-icon {
            font-size: 32px;
            flex-shrink: 0;
        }

        .link-content {
            text-align: left;
        }

        .link-content h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .link-content p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }

        @media (max-width: 768px) {
            .dashboard-links {
                flex-direction: column;
                align-items: center;
            }
            
            .dashboard-link {
                min-width: auto;
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</body>
</html>