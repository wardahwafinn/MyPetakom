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


$conn = new mysqli("localhost", "root", "", "mypetakom");

$sql = "SELECT m.membershipID, m.studentID, s.studentName, m.appliedDate 
        FROM membership m 
        JOIN student s ON m.studentID = s.studentID
        WHERE m.memberstatus = 'pending'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="description" content="student Dashboard for myPetakom">
    <meta name="author" content="UMI MAISARAH BINTI MOHD AFENDI">
    <title>MyPetakom</title>
    <link rel="stylesheet" type="text/css" href="style/admin.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <meta charset="UTF-8">
    <title>Student Page</title>
</head>

<body class="background">

    <div class="sidebar">
        <li class="listyle"><a href="student.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a></li>
        <hr>

        <li class="listyle"><a href="#" class="nav-item">Profile</a>
        <a href="admin_manage_profile.php" class="nav-item">Manage Profile</a>
        </li>
        <hr>

        <li class="listyle"><a href="admin.php" class="nav-item">Dashboard</a></li>
        <hr>

        <li class="listyle"><a  class="active"  href="adminMember.php" class="nav-item">Manage Membership</a></li>
        <hr>

        <li class="listyle"><a href="#" class="nav-item">View Event</a></li>
        <hr>

    </div>

    

   <div class="top-right-bar">
        <a href="profile.html" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">HI, ADMIN
        </a>
        <a href="logout.php">
            <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
        </a>
    </div>
    <div class="h1text">
        <h1>MEMBERSHIP MANAGEMENT</h1>
    </div>


<div class="container2">
        <h2>Pending Membership Applications</h2>
        <input type="text" placeholder="search by name or student ID">

  <table id="membershipTable">
    <thead>
      <tr>
        <th>STUDENT ID</th>
        <th>NAME</th>
        <th>DATE APPLIED</th>
        <th>ACTIONS</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
      <tr id="row-<?= $row['membershipID'] ?>">
        <td><?= htmlspecialchars($row["studentID"]) ?></td>
        <td><?= htmlspecialchars($row["studentName"]) ?></td>
        <td><?= htmlspecialchars($row["appliedDate"]) ?></td>
        <td>
          <button class="action-btn accept" onclick="updateStatus(<?= $row['membershipID'] ?>, 'accept')">&#10004;</button>
          <button class="action-btn reject" onclick="updateStatus(<?= $row['membershipID'] ?>, 'reject')">&#10008;</button>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
function updateStatus(membershipID, action) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "update_status_membership.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function() {
    if (xhr.status === 200 && xhr.responseText === "success") {
      const row = document.getElementById("row-" + membershipID);
      row.style.display = "none";
    } else {
      alert("Failed to update status: " + xhr.responseText);
    }
  };

  xhr.send("membershipID=" + membershipID + "&action=" + action);
}
</script>



</body>
</html>
<?php $conn->close(); ?>
