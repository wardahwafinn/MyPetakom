<?php
$link = mysqli_connect("localhost", "root", "", "mypetakom");

// Initialize options
$eventOptions = "";

// Fetch event IDs from the `event` table
$eventQuery = mysqli_query($link, "SELECT eventID FROM event");
while ($row = mysqli_fetch_assoc($eventQuery)) {
    $eventID = $row['eventID'];
    $eventOptions .= "<option value='$eventID'>$eventID</option>";
}
?>


<!DOCTYPE html>
<html>
<head>

   <meta name="description" content="Event Attendance">
   <meta name="author" content="HAZIRAH BINTI ERMON CHATIB">

  <title>Event Attendance</title>
  <link rel="website icon" type="png" href="image/Petakom.png">
  <link rel="stylesheet" href="style/project_3.css">
   <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body style="background-image: url('image/bg.jpg'); background-size: cover; background-position: center;">
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

      <div class="tabs">
        <button class="tablinks" onclick="openTab(event, 'createTab')">Create attendance slot</button>
        <button class="tablinks" onclick="openTab(event, 'manageTab')">Manage attendance</button>
      </div>

   
     <div id="createTab" class="tabcontent">
        <form class="form" action="action.php" method="post">
		  
		  <label for="eventID" style="font-weight: bold; display: block; margin-top: 10px;">Event ID:</label>
         <select name="eventID" id="eventID" required
         style="width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #aaa; box-sizing: border-box;">
         <option value="">-- Select Event ID --</option>
          <?= $eventOptions ?>
          </select>


          <label>Slot Event Date:
            <input type="date" name="slotDate" required />
          </label>
          <label>Slot Event Time:
            <input type="time" name="slotTime" required />
          </label>
        
         <label>Location Search:
  <input type="text" id="locationInput" placeholder="Enter a location name" />
  <button type="button" onclick="searchLocation()">Search</button>
</label>

    <label>Coordinate:
     <input type="text" name="coordinate" id="coordinateInput" placeholder="Latitude,Longitude" readonly required />
    </label>
     <div id="map" style="height: 300px; width: 100%; margin-bottom: 20px;"></div>

          <div class="form-buttons">
            <button type="submit" class="btn-submit">SAVE</button>
            <button type="reset" class="btn-reset">RESET</button>
          </div>
        </form>
      </div>

      
      <div id="manageTab" class="tabcontent">
    <div class="manage-container">
        <div class="manage-header">
            <input type="text" id="searchInput" placeholder="Search by event name or ID..">
            <select id="eventTypeFilter">
             <option value="">All Events</option>
               <?php
                $eventFilterQuery = mysqli_query($link, "SELECT DISTINCT eventID FROM attendanceslot");
                while ($row = mysqli_fetch_assoc($eventFilterQuery)) {
                echo "<option value='{$row['eventID']}'>{$row['eventID']}</option>";
                  }
                  ?>
               </select>

            <input type="date" id="dateFilter">
            <button class="search-button" onclick="filterEvents()">SEARCH</button>
        </div>

        <div id="eventList">
            <table>
                <thead>
                    <tr>
                        <th>Event ID</th>
                        <th>Date</th>
						<th>Time</th>
						<th>Coordinate</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="eventsTableBody">
                    <?php
                    // Fetch data from database
                    $link = mysqli_connect("localhost", "root", "") or die(mysqli_connect_error());
                    mysqli_select_db($link, "mypetakom") or die(mysqli_error($link));
                    $query = "SELECT * FROM attendanceslot";
                    $result = mysqli_query($link, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                    <td>{$row['eventID']}</td>
                                    <td>{$row['slotDate']}</td>
									<td>{$row['slotTime']}</td>
									<td>{$row['coordinate']}</td>
                                    <td>
                                        <a href='EditAttendance.php?eventID={$row['eventID']}' class='btn-manage'>Update</a> |
                                        <a href='deleteEvent.php?eventID={$row['eventID']}' class='btn-manage'>Delete</a>
										<a href='manageEvent.php?QRCode={$row['QRCode']}' class='btn-manage'>Manage</a>
										<a href='manageAttendance.php?QRCode={$row['QRCode']}' class='btn-manage'>Attendance</a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No events found.</td></tr>";
                    }

                    mysqli_close($link);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

 


  <!-- JAVASCRIPT -->
  <script>
  let map, marker;
  
function filterEvents() {
  const searchInput = document.getElementById("searchInput").value.toLowerCase();
  const eventType = document.getElementById("eventTypeFilter").value.toLowerCase();
  const dateFilter = document.getElementById("dateFilter").value;

  const rows = document.querySelectorAll("#eventsTableBody tr");
  rows.forEach(row => {
    const eventID = row.cells[0].textContent.toLowerCase();
    const date = row.cells[1].textContent;

    const matchesSearch = searchInput === "" || eventID.includes(searchInput);
    const matchesType = eventType === "all event.." || eventID.includes(eventType);
    const matchesDate = dateFilter === "" || date === dateFilter;

    if (matchesSearch && matchesType && matchesDate) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
}

  function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.display = "none";
    }

    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
      tablinks[i].classList.remove("active");
    }

    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.classList.add("active");

    // Resize map if shown
    if (tabName === "createTab" && map) {
      map.invalidateSize();
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".tablinks").click();

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

      document.getElementById("locationInput").addEventListener("change", function searchLocation() {
        let location = this.value;
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
      });
    }
  }); 
</script>
</body>
</html>