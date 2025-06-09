<?php
$link = mysqli_connect("localhost", "root", "", "mypetakom");
if (!$link) die("Connection failed: " . mysqli_connect_error());

$eventID = $_GET['eventID'] ?? '';

$slotDate = '';
$slotTime = '';
$coordinate = '';

if ($eventID) {
    $query = "SELECT * FROM attendanceslot WHERE eventID = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "s", $eventID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $slotDate = $row['slotDate'];
        $slotTime = substr($row['slotTime'], 0, 5); // convert to HH:MM
        $coordinate = $row['coordinate'];
    }

    mysqli_stmt_close($stmt);
}
mysqli_close($link);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Update Event Slot</title>
  <meta name="description" content="Event Attendance">
   <meta name="author" content="HAZIRAH BINTI ERMON CHATIB">
   
  <link rel="website icon" type="png" href="image/Petakom.png">
  <link rel="stylesheet" href="style/project_3.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body style="background-image: url('image/bg.jpg'); background-size: cover; background-position: center;">
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
        <h1>Update Event Attendance Slot</h1>
        <div class="user-info">👤 Welcome, Hanis</div>
      </header>
      
	     <button onclick="window.history.back()" class="btn-back">Back</button>
	  
      <form class="form" action="updateEvent.php" method="POST">
        <input type="hidden" name="eventID" value="<?= $eventID ?>">

        <label>Date:
            <input type="date" name="slotDate" value="<?= $slotDate ?>" required>
        </label><br>

        <label>Time:
            <input type="time" name="slotTime" value="<?= $slotTime ?>" required>
        </label><br>

        <label>Coordinate:
            <input type="text" name="coordinate" value="<?= $coordinate ?>" required>
        </label><br>


        <div id="map" style="height: 300px; width: 100%; margin-bottom: 20px;"></div>

        <div class="form-buttons">
          <button type="submit" class="btn-submit">Update</button>
          <button type="reset" class="btn-reset">Reset</button>
        </div>
      </form>
    </main>
  </div>

  <script>
    let map, marker;

    // Initialize map
    document.addEventListener("DOMContentLoaded", function () {
      if (document.getElementById("map")) {
        map = L.map("map").setView([4.2105, 101.9758], 6); // Malaysia center

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: "© OpenStreetMap contributors"
        }).addTo(map);

        map.on("click", function (e) {
          let lat = e.latlng.lat.toFixed(6);
          let lng = e.latlng.lng.toFixed(6);
          document.getElementById("coordinateInput").value = lat + "," + lng;

          if (marker) map.removeLayer(marker);
          marker = L.marker([lat, lng]).addTo(map);
        });
      }
    });

    // Function to search the location based on the input
    function searchLocation() {
      let location = document.getElementById("locationInput").value;
      if (location.trim() === "") return;

      fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}`)
        .then(response => response.json())
        .then(data => {
          if (data.length > 0) {
            let lat = parseFloat(data[0].lat).toFixed(6);
            let lon = parseFloat(data[0].lon).toFixed(6);

            map.setView([lat, lon], 16);
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lon]).addTo(map);
            document.getElementById("coordinateInput").value = `${lat},${lon}`;
          } else {
            alert("Location not found. Please try a more specific name.");
          }
        })
        .catch(err => {
          alert("Failed to search location.");
          console.error(err);
        });
    }
  </script>

</body>
</html>

