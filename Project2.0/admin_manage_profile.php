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
    <meta name="description" content="Profile management for admin">
    <meta name="author" content="UMI MAISARAH BINTI MOHD AFENDI">
    <title>MyPetakom</title>
    <link rel="stylesheet" type="text/css" href="style/admin_manage_profile.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <meta charset="UTF-8">
</head>

<body class="background">

    <div class="sidebar">
        <li class="listyle"><a href="student.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a></li>
        <hr>

        <li class="listyle"><a href="staffProfile.php" class="nav-item">Profile</a></li>

        <li class="listyle"><a class="active" href="admin_manage_profile.php" class="nav-item">Manage Profile</a>
        </li>
        <hr>

        <li class="listyle"><a  href="admin.php" class="nav-item">Dashboard</a></li>
        <hr>

        <li class="listyle"><a href="adminMember.php" class="nav-item">Manage Membership</a></li>
        <hr>

        <li class="listyle"><a href="#" class="nav-item">View Event</a></li>
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

   <div class="container">
        <h2>User Management</h2>

        <a href="new_user_form.php">
            <button style="padding: 10px 20px; background-color: #8B0000; color: white; border: none; border-radius: 8px; cursor: pointer;">
                + Add New User
            </button>
        </a>

  
        <hr>
        <input type="text" id="userSearch" placeholder="Search by name, email, role..." style="margin-bottom: 15px; padding: 8px; width: 50%; border-radius: 6px; border: 1px solid #ccc;">


        <!-- User Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $studentQuery = "SELECT studentID AS ID, studentName AS name, studentEmail AS email, 'STUDENT' AS role FROM student";
                $staffQuery = "SELECT staffID AS ID, staffName AS name, staffEmail AS email, staffRole AS role FROM staff";

                $students = $conn->query($studentQuery);
                $staffs = $conn->query($staffQuery);

                function renderRow($id, $name, $email, $role, $type) {
                    echo "<tr>
                        <td>$id</td>
                        <td>$name</td>
                        <td>$email</td>
                        <td>$role</td>
                        <td>
                            <a href='edit_user.php?id=$id&type=$type'>✏️</a>
                            <a href='delete_user.php?id=$id&type=$type' onclick='return confirm(\"Are you sure?\")'>👤</a>
                        </td>
                    </tr>";
                }

                if ($students) {
                    while($row = $students->fetch_assoc()) {
                        renderRow($row["ID"], $row["name"], $row["email"], $row["role"], "student");
                    }
                }

                if ($staffs) {
                    while($row = $staffs->fetch_assoc()) {
                        renderRow($row["ID"], $row["name"], $row["email"], $row["role"], "staff");
                    }
                }

                $conn->close();
                ?>
            </tbody>
        </table>

    <div id="toast" style="
    visibility: hidden;
    min-width: 250px;
    margin-left: -125px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 8px;
    padding: 16px;
    position: fixed;
    z-index: 1;
    left: 50%;
    bottom: 30px;
    font-size: 16px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
">
  <span id="toast-msg"></span>
</div>

<script>
function showToast(message) {
    const toast = document.getElementById("toast");
    document.getElementById("toast-msg").innerText = message;
    toast.style.visibility = "visible";
    setTimeout(() => {
        toast.style.visibility = "hidden";
        window.history.replaceState({}, document.title, window.location.pathname); // remove ?status=
    }, 3000);
}

// Check URL for ?status=
const urlParams = new URLSearchParams(window.location.search);
const status = urlParams.get("status");

if (status) {
    let message = "";
    if (status === "added") message = "User added successfully!";
    else if (status === "updated") message = "User updated successfully!";
    else if (status === "deleted") message = "User deleted successfully!";
    showToast(message);
}
</script>

<script>
document.getElementById("userSearch").addEventListener("keyup", function () {
    const search = this.value.toLowerCase();
    const rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(search) ? "" : "none";
    });
});
</script>


</body>
</html>