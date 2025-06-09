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

// Check if user is logged in and is a staff
if (!isset($_SESSION['userID']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'admin') {
    header("Location: LoginForm.php");
    exit();
}

// Get staff information from database
$staffID = $_SESSION['userID'];
$query = "SELECT * FROM staff WHERE staffID = ?";
$stmt = $data->prepare($query);
$stmt->bind_param("s", $staffID);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();
$stmt->close();

mysqli_close($data);

// Check if staff data exists
if (!$staff) {
    echo "Staff data not found.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="description" content="Staff profile for myPetakom">
    <meta name="author" content="UMI MAISARAH BINTI MOHD AFENDI">
    <title>MyPetakom</title>
    <link rel="stylesheet" type="text/css" href="style/profile.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <meta charset="UTF-8">
    <title>Staff Profile </title>
</head>
<body class="background">

    <div class="sidebar">
        <li class="listyle"><a href="student.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a></li>
        <hr>

        <li class="listyle"><a class="active" href="staffProfile.php" class="nav-item">Profile</a>
        <a href="admin_manage_profile.php" class="nav-item">Manage Profile</a>
        </li>
        <hr>

        <li class="listyle"><a  href="admin.php" class="nav-item">Dashboard</a></li>
        <hr>

        <li class="listyle"><a href="adminMember.php" class="nav-item">Manage Membership</a></li>
        <hr>

        <li class="listyle"><a href="#" class="nav-item">View Event</a></li>
        <hr>

    </div>
    <div class="top-right-bar">
        <a href="staffProfile.php" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">HI, STAFF
        </a>
        <a href="logout.php">
            <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
        </a>
    </div>
        
        <div class="profile-content">
            <div class="welcome-msg">
                <strong>Welcome, <?php echo htmlspecialchars($staff['staffName']); ?>!</strong>
                <br>You are logged in as: Staff (ID: <?php echo htmlspecialchars($_SESSION['userID']); ?>)
            </div>
        </div>
        
        <div>
            <img src="images/avatar.png" alt="Avatar" style=" height=150px; width:150px; border-radius:50%; justify-content: center; margin-left:50%">
        </div>
            
            <div class="profile-info">
            <div class="info-group">
                        <span class="info-label">Staff ID</span>
                        <div class="info-value"><?php echo htmlspecialchars($staff['staffID']); ?></div>
                </div>

                <div class="info-group">
                    <span class="info-label">Name</span>
                    <div class="info-value"><?php echo htmlspecialchars($staff['staffName']); ?></div>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Email</span>
                    <div class="info-value"><?php echo htmlspecialchars($staff['staffEmail']); ?></div>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Password</span>
                    <div class="info-value password-hidden">••••••••••••</div>
                </div>
                
                <div class="info-group">
                    <span class="info-label">Role</span>
                    <div class="info-value"><?php echo htmlspecialchars($staff['staffRole']); ?></div>
                </div>
            </div>
                

        </div>
    </div>
</body>
</html>