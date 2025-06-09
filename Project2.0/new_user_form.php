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
 ?>
<!DOCTYPE html>
<html>
<head>

 <meta name="description" content="add new user">
    <meta name="author" content="UMI MAISARAH BINTI MOHD AFENDI">
    <title>MyPetakom</title>
    <link rel="stylesheet" type="text/css" href="style/admin_manage_profile.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <meta charset="UTF-8">
    <title>Student Page</title>

    <title>Add New User</title>
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
        <li class="listyle"><a href="admin.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a></li>
        <hr>

        <li class="listyle"><a href="staffProfile.php" class="nav-item">Profile</a>
        <a class="active" href="admin_manage_profile.php" class="nav-item">Manage Profile</a>
        </li>
        <hr>

        <li class="listyle"><a href="admin.php" class="nav-item">Dashboard</a></li>
        <hr>

        <li class="listyle"><a href="adminMember.php" class="nav-item">Manage Membership</a></li>
        <hr>

        <li class="listyle"><a href="admin_view_event.php" class="nav-item">View Event</a></li>
        <a href="admin_manage_profile.php" class="nav-item">>Attendance</a>
        <hr>

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
        <h2>Add New User</h2>
        <form method="post" action="insert_user.php">
            <label>User Type:</label>
            <select name="user_type" required>
                <option value="student">Student</option>
                <option value="staff">Staff</option>
            </select>

            <label>ID:</label><br>
            <input type="text" name="id" required>
            <br>
            <label>Name:</label><br>
            <input type="text" name="name" required>
            <br>
            <label>Email:</label><br>
            <input type="email" name="email" required>
            <br>
            <label>Password:</label><br>
            <input type="password" name="password" required>
            <br>
            <label>Card Number (STUDENT) or Role (ADMIN/STAFF):</label><br>
            <input type="text" name="card_or_role" required>
            <br>
            <label>Phone Number (Only for Student):</label><br>
            <input type="text" name="phone">

            <button type="submit">Add User</button>
        </form>

        <a href="admin_manage_profile.php">← Back to User List</a>
    </div>
</body>
</html>
