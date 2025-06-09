<?php
$link = mysqli_connect("localhost", "root", "", "mypetakom");

//  Step 1: Get QRCode from URL
$QRCode = $_GET['QRCode'] ?? '';

if (!$QRCode) {
    die("QRCode not found in URL.");
}

// Step 2: Get slotID based on QRCode
$getSlot = mysqli_query($link, "SELECT slotID FROM attendanceslot WHERE QRCode = '$QRCode'");
if (!$getSlot || mysqli_num_rows($getSlot) == 0) {
    die("Invalid QRCode.");
}
$slotData = mysqli_fetch_assoc($getSlot);
$slotID = $slotData['slotID'];

$search = $_GET['search'] ?? '';
$geo = $_GET['geo'] ?? '';
$time = $_GET['time'] ?? '';


// Step 3: Filter attendance by slotID only
$query = "SELECT a.*, s.studentName 
          FROM attendancelist a 
          JOIN student s ON a.studentID = s.studentID 
          WHERE a.slotID = '$slotID'";

if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($link, $search);
    $query .= " AND (a.studentID LIKE '%$safeSearch%' OR s.studentName LIKE '%$safeSearch%')";
}

if (!empty($geo)) {
    $safeGeo = mysqli_real_escape_string($link, $geo);
    $query .= " AND a.geolocation = '$safeGeo'";
}

if (!empty($time)) {
    $safeTime = mysqli_real_escape_string($link, $time);
    $query .= " AND TIME(a.checkInTime) = '$safeTime'";
}


$query .= " ORDER BY a.checkInTime ASC";


$result = mysqli_query($link, $query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="description" content="Event Attendance">
   <meta name="author" content="HAZIRAH BINTI ERMON CHATIB">
  <title>Manage Attendance</title>
  <link rel="website icon" type="png" href="image/Petakom.png">
  <link rel="stylesheet" href="style/project_3.css">
  <style>
     .table-container {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      max-width: 1000px;
      margin: auto;
    }
	.main-content {
  flex: 1;
  padding: 2rem;
  padding-top: 3rem; 
  overflow-y: auto;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  margin-top: 20px; 
}

.user-info {
  background-color: white;
  padding: 0.5rem 1rem;
  border-radius: 10px;
  font-weight: bold;
  margin-top: 10px;
}

  </style>
</head>
<body style="background-image: url('image/bg.jpg'); background-size: cover; background-position: center;">
<?php if (isset($_GET['success'])): ?>
  <p style="text-align:center; color: green; font-weight:bold; font-size: 18px;">
    Attendance approved successfully!
  </p>
<?php endif; ?>
<script>
  setTimeout(function() {
    const msg = document.querySelector('p');
    if (msg) msg.style.display = 'none';
  }, 3000); // hides after 3 seconds
</script>


 <div class="container">
    <div class="sidebar">
    <img src="image/Petakom.png" alt="PETAKOM Logo" class="logo">
    <nav>
      <a href="advisor_dash.php" class="nav-item">Home</a>
      <hr>
      <a href="profile.php" class="nav-item">Profile</a>
      <hr>
      <span class="nav-item event-title">View Event</span>
      <div class="submenu nav-itemhover">
        <a href="event_list.php">&gt; Committee</a>
        <a href="event_registration.php">&gt; Registration</a>
        <a href="Project.php">&gt; Attendance</a>
      </div>
    </nav>
  </div>
	
	<main class="main-content">
      <header class="header">
        <h1>Event Attendance</h1>
        
      </header>

    
	  
<div class="table-container">

  <form method="GET" action="manageAttendance.php" class="top-bar">
  <input type="hidden" name="QRCode" value="<?= $_GET['QRCode'] ?? '' ?>">
  <input type="text" name="search" placeholder="Search by name or ID.." value="<?= $_GET['search'] ?? '' ?>">
  <select name="geo">
    <option value="">All</option>
    <option value="Matched" <?= ($_GET['geo'] ?? '') === 'Matched' ? 'selected' : '' ?>>Matched</option>
    <option value="Not Matched" <?= ($_GET['geo'] ?? '') === 'Not Matched' ? 'selected' : '' ?>>Not Matched</option>
  </select>
  <input type="time" name="time" value="<?= $_GET['time'] ?? '' ?>">
  <button type="submit">SEARCH</button>
</form>


  <table>
    <thead>
      <tr>
        <th>Student ID</th>
        <th>Name</th>
        <th>Check-in Time</th>
        <th>Location</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
      <tr>
        <td><?= $row['studentID'] ?></td>
        <td><?= strtoupper($row['studentName']) ?></td>
        <td><?= date("g:i A", strtotime($row['checkInTime'])) ?></td>
        <td><?= $row['geolocation'] == 'Not Matched' ? 'Not Matched' : 'Matched' ?></td>
        <td>
          <?php if ($row['listStatus'] == 0): ?>
            <span class="status-pending">Pending</span>
          <?php else: ?>
            <span class="status-present">Present</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($row['listStatus'] == 0): ?>
            <form method="post" action="approveAttendance.php?QRCode=<?= urlencode($QRCode) ?>">
              <input type="hidden" name="listID" value="<?= $row['listID'] ?>">
              <button type="submit" name="approve" class="approve-btn">Approve</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
<button onclick="window.history.back()" class="btn-back">Back</button>

</body>
</html>