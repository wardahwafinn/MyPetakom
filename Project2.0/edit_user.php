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

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="description" content="User Edit Page for MyPetakom">
    <meta name="author" content="UMI MAISARAH BINTI MOHD AFENDI">
    <title>Edit User</title>
    <link rel="stylesheet" type="text/css" href="style/admin_manage_profile.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 30px;
        }
        .form-container {
            background: #fff;
            padding: 50px;
            border-radius: 12px;
            width: 450px;
            margin: auto;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding-top: 20px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            background-color: #8B0000;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
        }
        a {
            display: inline-block;
            margin-top: 16px;
            text-align: center;
            color: #555;
            text-decoration: none;
        }
    </style>
</head>
<body class="background">

    <div class="sidebar">
        <li class="listyle"><a href="student.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a></li>
        <hr>
        <li class="listyle"><a href="staffProfile.php" class="nav-item">Profile</a></li>
        <li class="listyle"><a class="active" href="admin_manage_profile.php" class="nav-item">Manage Profile</a></li>
        <hr>
        <li class="listyle"><a href="admin.php" class="nav-item">Dashboard</a></li>
        <hr>
        <li class="listyle"><a href="adminMember.php" class="nav-item">Manage Membership</a></li>
        <hr>
        <li class="listyle"><a href="#" class="nav-item">View Event</a></li>
        <hr>
    </div>

    </div>

        <div class="h1text">
        <h1>PROFILE MANAGEMENT</h1>
    </div>

    <div class="top-right-bar">
        <a href="staffProfile.php" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">HI, ADMIN
        </a>
        <a href="logout.php">
            <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
        </a>
    </div>

    <div class="form-container">
        <h2>Edit User</h2>

        <?php
        if ($type == 'student') {
            $result = $conn->query("SELECT * FROM student WHERE studentID = '$id'");
            $row = $result->fetch_assoc();
            echo "<form method='post' action='update_user.php'>
                <input type='hidden' name='type' value='student'>
                <input type='hidden' name='id' value='{$row['studentID']}'>
                Name: <br><input type='text' name='name' value='{$row['studentName']}'><br>
                Email: <br><input type='email' name='email' value='{$row['studentEmail']}'><br>
                Card: <br><input type='text' name='extra' value='{$row['studentCard']}'><br>
                Phone: <br><input type='text' name='phone' value='{$row['studentPhoneNum']}'><br>
                <button type='submit'>Update</button>
            </form>";
        } else {
            $result = $conn->query("SELECT * FROM staff WHERE staffID = '$id'");
            $row = $result->fetch_assoc();
            echo "<form method='post' action='update_user.php'>
                <input type='hidden' name='type' value='staff'>
                <input type='hidden' name='id' value='{$row['staffID']}'><br>
                Name: <br><input type='text' name='name' value='{$row['staffName']}'><br>
                Email: <br><input type='email' name='email' value='{$row['staffEmail']}'><br>
                Role: <br><input type='text' name='extra' value='{$row['staffRole']}'><br>
                <button type='submit'>Update</button>
            </form>";
        }
        ?>

        <a href="admin_manage_profile.php">← Back to User List</a>
    </div>

</body>
</html>

<?php $conn->close(); ?>
