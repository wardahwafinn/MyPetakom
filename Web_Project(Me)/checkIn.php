<?php
$link = mysqli_connect("localhost", "root", "", "mypetakom");

// Step 1: Make sure QRCode is provided
$QRCode = $_GET['QRCode'] ?? "";  // Use null coalescing to avoid warning

// Step 2: Initialize fallback values
$eventName = $description = $slotDate = $slotTime = $coordinate = $qrURL = "";

// Step 3: Only query if QRCode is present
if ($QRCode !== "") {
    $query = "
        SELECT e.eventName, e.description, a.slotDate, a.slotTime, a.coordinate 
        FROM attendanceslot a
        JOIN event e ON a.eventID = e.eventID
        WHERE a.QRCode = '$QRCode'
    ";
    
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $eventName = $row["eventName"];
        $description = $row["description"];
        $slotDate = $row["slotDate"];
        $slotTime = $row["slotTime"];
        $coordinate = $row["coordinate"];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta name="description" content="Event Attendance">
   <meta name="author" content="HAZIRAH BINTI ERMON CHATIB">
  <title>MyPetakom Event Check-In</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: url('image/fkom.png') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .logo {
      margin-top: 20px;
    }

    .container {
      background-color: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      width: 400px;
      margin-top: 20px;
    }

    h1 {
      text-align: center;
      font-size: 24px;
      font-weight: bold;
      margin-top: 10px;
    }

    .section-box {
      border: 1px solid #ccc;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 10px;
    }

    .section-box h3 {
      color: blue;
      margin-top: 0;
    }

    label {
      display: block;
      margin-top: 10px;
      font-weight: bold;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      margin-bottom: 15px;
      border-radius: 5px;
      border: 1px solid #aaa;
      box-sizing: border-box;
    }

    .verify-button {
      width: 100%;
      padding: 10px;
      background-color: #4285f4;
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .verify-button:hover {
      background-color: #3367d6;
    }

    .note {
      font-size: 0.9rem;
      color: #555;
      margin-bottom: 10px;
    }
  </style>
</head>

<body style="background-image: url('image/fkom.png'); background-size: cover; background-position: center;">

  <h1>MyPetakom Event Check-In</h1>

  <div class="container">
   
    <div class="section-box">
       <h3>Details:</h3>
        <p><strong>Event Name:</strong> <?= $eventName ?></p>
         <p><strong>Description:</strong> <?= $description ?></p>
        <p><strong>Date:</strong> <?= $slotDate ?></p>
        <p><strong>Time:</strong> <?= $slotTime ?></p>
        <p><strong>Geolocation:</strong> <?= $coordinate ?></p>
    </div>

   
    <div class="section-box">
      <h3 style="color: blue;">Verify your attendance:</h3>
      <p class="note">Please enter your student ID and password for attendance</p>
	  <form method="post" action="checkin_process.php?QRCode=<?= $QRCode ?>">
        <label for="studentID">Student ID:</label>
        <input type="text" id="studentID" name="studentID" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="verify-button">VERIFY</button>
      </form>
    </div>
  </div>

</body>
</html>

