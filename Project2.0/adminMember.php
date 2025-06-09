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

$sql = "SELECT m.membershipID, m.studentID, s.studentName, s.studentCardID, m.appliedDate, m.memberstatus 
        FROM membership m 
        JOIN student s ON m.studentID = s.studentID";


$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="description" content="membership management for admin">
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

        <li class="listyle"><a href="staffProfile.php" class="nav-item">Profile</a>
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
        <a href="staffProfile.php" class="profilename">
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
       <input type="text" id="userSearch" placeholder="Search by name, student ID..." style="margin-bottom: 15px; padding: 8px; width: 50%; border-radius: 6px; border: 1px solid #ccc;">

  <table id="membershipTable">
    <thead>
  <tr>
    <th>STUDENT ID</th>
    <th>NAME</th>
    <th>DATE APPLIED</th>
    <th>STATUS</th>
    <th>ACTIONS</th>
  </tr>
</thead>

    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
          <tr id="row-<?= $row['membershipID'] ?>">
      <td><?= htmlspecialchars($row["studentID"]) ?></td>
      <td><?= htmlspecialchars($row["studentName"]) ?></td>
      <td><?= htmlspecialchars($row["appliedDate"]) ?></td>
      <td id="status-<?= $row['membershipID'] ?>"><?= ucfirst(htmlspecialchars($row["memberstatus"])) ?></td>
      <td>
        <?php if ($row["memberstatus"] === 'pending'): ?>
          <button class="action-btn accept" onclick="updateStatus(<?= $row['membershipID'] ?>, 'accept')">&#10004;</button>
          <button class="action-btn reject" onclick="updateStatus(<?= $row['membershipID'] ?>, 'reject')">&#10008;</button>
        <?php endif; ?>
        <button class="action-btn view-card" onclick="showCard('<?= base64_encode($row['studentCardID']) ?>')">🪪 View Card</button>
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
    if (xhr.status === 200) {
      const response = xhr.responseText.trim(); // trimming whitespace just in case
      const statusCell = document.getElementById("status-" + membershipID);
      if (response === "accepted" || response === "rejected") {
        statusCell.innerText = response.charAt(0).toUpperCase() + response.slice(1);
        const row = document.getElementById("row-" + membershipID);
        const buttons = row.querySelectorAll(".accept, .reject");
        buttons.forEach(btn => btn.remove());
      } else {
        alert("Unexpected response: " + response);
      }
    } else {
      alert("Failed to update status: " + xhr.responseText);
    }
  };

  xhr.send("membershipID=" + membershipID + "&action=" + action);
}
</script>

<script>
function showCard(base64Image) {
  const modal = document.getElementById("cardModal");
  const img = document.getElementById("cardImage");
  img.src = "data:image/jpeg;base64," + base64Image;
  modal.style.display = "flex";
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

<div id="cardModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:9999;">
  <div style="background:white; padding:20px; border-radius:10px; max-width:90%; max-height:90%;">
    <span style="float:right; cursor:pointer; font-size:20px;" onclick="document.getElementById('cardModal').style.display='none'">✖</span>
    <h3>Student Card Preview</h3>
    <img id="cardImage" src="" alt="Student Card" style="max-width:100%; max-height:80vh; border:1px solid #ccc; border-radius:10px;">
  </div>
</div>


</body>
</html>
<?php $conn->close(); ?>
