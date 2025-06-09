

<!DOCTYPE html>
<html>
<head>

   <meta name="description" content="Event Attendance">
   <meta name="author" content="HAZIRAH BINTI ERMON CHATIB">

  <title>Manage Attendance</title>
  <link rel="website icon" type="png" href="image/Petakom.png">
  <link rel="stylesheet" href="style/project_3.css">
  
   <style>
      body {
      margin: 0;
      font-family: Arial, sans-seriff;
      background-color: #fefefe;
    }

    .container {
      display: flex;
      height: 100vh;
    }

   .sidebar {
  width: 230px;
  height: 100vh;
  background: linear-gradient(to bottom, #ba7373, #5b2c2c);
  color: white;
  padding: 20px;
  box-sizing: border-box;
  position: fixed;
  top: 0;
  left: 0;
  overflow-y: auto;
}

.sidebar .logo {
  display: block;
  width: 120px;
  height: auto;
  margin: 0 auto 20px;
}

.sidebar hr {
  border: none;
  border-top: 1px solid rgba(255, 255, 255, 0.3);
  margin: 15px 0;
}

.sidebar .nav-item,
.sidebar .submenu a {
  display: block;
  color: white;
  text-decoration: none;
  font-size: 15px;
  padding: 10px 15px;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.sidebar .nav-item:hover,
.sidebar .submenu a:hover {
  background-color: rgba(255, 255, 255, 0.15);
}

.sidebar .event-title {
  font-weight: bold;
  font-size: 16px;
  padding: 10px 15px;
  color: white;
}

.sidebar .submenu {
  padding-left: 10px;
}

.sidebar .submenu a {
  font-size: 14px;
  padding-left: 25px;
  display: block;
}

    .main-content {
      flex: 1;
    
      padding: 2rem;
      overflow-y: auto;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header h1 {
      margin: 0;
      font-size: 2rem;
    }

    .main-content {
      flex-grow: 1;
      padding: 30px 50px;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header h1 {
      font-size: 28px;
      font-weight: bold;
    }

    .user-info {
      background: white;
      padding: 10px 20px;
      border-radius: 25px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .card {
      background: white;
      padding: 25px;
      border-radius: 10px;
      margin-top: 30px;
      max-width: 600px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .card h3 {
      color: blue;
    }

    .card p {
      margin: 5px 0;
    }

    .qr-container {
      margin-top: 20px;
      text-align: center;
    }

    .qr-container img {
      width: 200px;
      height: 200px;
    }

    .qr-buttons {
      margin-top: 15px;
    }

    .qr-buttons a {
      margin: 0 10px;
      padding: 10px 15px;
      border-radius: 5px;
      text-decoration: none;
      color: white;
    }

    .print-btn {
      background-color: #4285f4;
    }

    .download-btn {
      background-color: #999;
    }

  </style>
 
</head>
<body style="background-image: url('image/bg.jpg'); background-size: cover; background-position: center;">

<?php
$link = mysqli_connect("localhost", "root", "", "mypetakom");

// Step 1: Make sure QRCode is provided
$QRCode = $_GET['QRCode'] ?? "";  // Use null coalescing to avoid warning

// Step 2: Initialize fallback values
$eventID = $slotDate = $slotTime = $coordinate = $qrURL = "";

// Step 3: Only query if QRCode is present
if ($QRCode !== "") {
    $query = "SELECT * FROM attendanceslot WHERE QRCode = '$QRCode'";
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $eventID = $row["eventID"];
        $slotDate = $row["slotDate"];
        $slotTime = $row["slotTime"];
        $coordinate = $row["coordinate"];
    }
}

// Step 4: Generate QR code
$checkinURL = "http://10.66.40.75/BCS2243/WE/Project2.0/checkIn.php?QRCode=$QRCode";
$qrURL = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($checkinURL);
?>

  <div class="container">
    <div class="sidebar">
        <a href="advisor_dash.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a>
        <hr>
        <a href="advisor_dash.php" class="nav-item">Home</a>
        <hr>
        <span class="nav-item event-title">Event</span>
        <div class="submenu">
            <a href="event_list.php">&gt; Event List</a>
            <a href="committee.php">&gt; Committee</a>
            <a href="event_registration.php">&gt; Registration</a>
            <a href="Project.php">&gt; Attendance</a>
        </div>
    </div>

    <main class="main-content">
      <header class="header">
        <h1>Event Attendance</h1>
        <div class="user-info">👤 Welcome, Hanis</div>
      </header>
       <button onclick="window.history.back()" class="btn-back">Back</button>
	  <div class="details-box">
        <h3>Details:</h3>
        <p><strong>Event:</strong> <?= $eventID ?></p>
        <p><strong>Date:</strong> <?= $slotDate ?></p>
        <p><strong>Time:</strong> <?= $slotTime ?></p>
        <p><strong>Geolocation:</strong> <?= $coordinate ?></p>
      </div>
	
	<div class="qr-buttons">
  <a href="<?= $qrURL ?>" target="_blank"> Print QR Code</a>
  <a href="<?= $qrURL ?>" download class="download-btn">⬇️Download QR Code</a>
</div>

	 </main> 
  </div>

    
</body>
</html>