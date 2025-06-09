<?php
$link = mysqli_connect("localhost", "root", "", "mypetakom");

$res1 = mysqli_query($link, "SELECT COUNT(*) AS totalCheckins FROM attendancelist WHERE listStatus = 1");
$checkins = mysqli_fetch_assoc($res1)['totalCheckins'];

$res2 = mysqli_query($link, "SELECT COUNT(DISTINCT eventID) AS totalEvents FROM attendanceslot");
$totalEvents = mysqli_fetch_assoc($res2)['totalEvents'];

$res3 = mysqli_query($link, "SELECT slot.eventID, COUNT(*) AS total, SUM(a.listStatus = 1) AS attended 
                             FROM attendancelist a 
                             JOIN attendanceslot slot ON a.slotID = slot.slotID 
                             GROUP BY slot.eventID");

$eventNames = [];
$eventRates = [];
$totalRate = 0;
$eventCount = 0;

while ($row = mysqli_fetch_assoc($res3)) {
  $eventNames[] = $row['eventID'];
  $rate = $row['attended'] > 0 ? round(($row['attended'] / $row['total']) * 100) : 0;
  $eventRates[] = $rate;
  $totalRate += $rate;
  $eventCount++;
}

$averageRate = $eventCount > 0 ? round($totalRate / $eventCount, 2) : 0;

$res5 = mysqli_query($link, "
  SELECT e.eventName, COUNT(*) AS checkins 
  FROM attendancelist a
  JOIN attendanceslot s ON a.slotID = s.slotID
  JOIN event e ON s.eventID = e.eventID
  WHERE a.listStatus = 1
  GROUP BY e.eventName
  ORDER BY checkins DESC
  LIMIT 5
");

$topEventNames = [];
$topEventCounts = [];

while ($row = mysqli_fetch_assoc($res5)) {
  $topEventNames[] = $row['eventName'];
  $topEventCounts[] = $row['checkins'];
}

$res4 = mysqli_query($link, "SELECT studentID FROM attendancelist WHERE listStatus = 1");
$courses = ['BCS' => 0, 'BCN' => 0, 'BCG' => 0, 'BCY' => 0, 'DRC' => 0];
while ($row = mysqli_fetch_assoc($res4)) {
  $id = $row['studentID'];
  $prefix = substr($id, 0, 2);
  switch ($prefix) {
    case 'CB': $courses['BCS']++; break;
    case 'CA': $courses['BCN']++; break;
    case 'CD': $courses['BCG']++; break;
    case 'CF': $courses['BCY']++; break;
    case 'RC': $courses['DRC']++; break;
  }
}
$courseLabels = array_keys($courses);
$courseCounts = array_values($courses);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="description" content="Event Attendance">
  <meta name="author" content="HAZIRAH BINTI ERMON CHATIB">
  <title>Administrator Dashboard</title>
  <link rel="website icon" type="png" href="image/Petakom.png">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
  <link rel="stylesheet" href="style/project_3.css">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-image: url('image/bg.jpg');
      background-size: cover;
      background-position: center;
    }
    .container {
      display: flex;
    }
    .sidebar {
  width: 200px;
  background: linear-gradient(to bottom right,rgb(179, 99, 99),rgb(183, 129, 129));
  padding: 20px;
  height: 100vh;
  color: #000;
  font-family: Arial, sans-serif;
}
   .sidebar .logo img {
  width: 100%;
  height: auto;
  margin-bottom: 10px;
}
    .sidebar-nav {
  list-style: none;
  padding: 0;
}
.sidebar-nav li {
  margin: 10px 0;
}
.sidebar-nav li a {
  text-decoration: none;
  color: black;
  display: block;
}

.sidebar-nav li.active a {
  background-color: rgba(255, 255, 255, 0.3);
  padding: 5px;
  border-radius: 4px;
}
    .sidebar .nav-item {
      padding: 10px;
      margin: 5px 0;
      background-color: transparent;
      color: black;
      text-decoration: none;
      font-weight: light;
      border-left: 3px solid transparent;
      transition: background 0.3s, border-left 0.3s;
    }
    .sidebar .nav-item:hover,
    .sidebar .nav-item.active {
      background-color: rgba(255,255,255,0.2);
      border-left: 3px solid #fff;
    }
    .sidebar hr {
  border: none;
  border-top: 1px solid white;
  margin: 10px 0;
}
    .main-content {
      flex: 1;
      padding: 30px;
    }
    .dashboard-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }
    .summary-section {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 30px;
    }
    .summary-card {
      padding: 20px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      width: 200px;
      text-align: center;
    }
    .charts-row {
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
    }
    .chart-container {
      flex: 1;
      min-width: 400px;
      max-width: 600px;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    font-family: 'Arial', sans-serif;
}

.background {
    background-image: url("../images/bg.png");
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
    width: 100%;
    height: 200%;
    background-attachment: fixed;
    
}

.img1 {
    border-radius: 50%;
}

li a.active {
background-color: rgba(217, 217, 217, 0.40);
 display: block;
    color: #333;
    text-decoration: none;
    padding: 10px 80px;
    font-size: 16px;
    list-style: none;
}


.flex-container {
  display: flex;
  background-color: transparent;
  margin-left: 20%;
}

.flex-container > div {
  background-color: transparent;
  padding-left: 20%;
  margin: 1%;
}

.container {
  position: relative;
  width: 100%;
}

.image {
  display: block;
  width: 100%;
  height: 100%;
}

.overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: #ddb333;
  overflow: hidden;
  width: 0;
  height: 100%;
  transition: .5s ease;
}

.container:hover .overlay {
  width: 100%;
  height: 99%;
}

.text {
  white-space: nowrap; 
  color: rgb(0, 0, 0);
  font-size: 20px;
  position: absolute;
  overflow: hidden;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  -ms-transform: translate(-50%, -50%);
}


div.container {
  padding: 10px;
}

.listyle{

list-style: none;
}

.h1text {
color: #000;
font-family: "Red Rose";
font-style: normal;
font-size: 30px;
font-weight: 700;
line-height: normal;
padding-left: 20%;
padding-top: 1.5%;
}

.h2text {

color: #000;
font-family: "Red Rose";
font-style: normal;
font-size: 20px;
font-weight: 600;
line-height: normal;
padding-left: 20%;
text-shadow: 2px 2px 5px rgb(232, 105, 37);
}

.h3text {

color: #00;
font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
font-style: normal;
font-size: 18px;
font-weight: 400;
line-height: normal;
padding-left: 20%;
list-style: circle;
}

.h4text {
color: rgba(95, 95, 95, 0.96);
font-family: Roboto;
font-size: 24px;
font-style: normal;
font-weight: 600;
line-height: normal;
}



ul{
    list-style-type: disc;
padding-left: 22%;
color: #000;
font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
font-style: normal;
font-size: 18px;
font-weight: 400;
line-height: 30px;

}



.sidebar {
    width: 210px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    padding-top: 20px;
    box-sizing: border-box;
}

.logo {
    display: block;
    width: 125px;
    height: 125px;
    margin: 0 auto 15px;
}

hr {
    border: 0;
    height: 1px;
    background-color: white;
    margin: 10px 0;
}

.nav-item {
    display: block;
    color: #333;
    text-decoration: none;
    padding: 10px 80px;
    font-size: 16px;
}

.nav-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.event-title {
    color: #333;
    padding: 10px 80px;
    display: block;
}

.submenu a {
    display: block;
    color: #333;
    text-decoration: none;
    padding: 8px 55px 7px 55px;
    font-size: 14px;
}

.submenu a:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.button {
    background-color: #a90000;
    border: none;
    color: white;
    padding: 12px 30px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
    margin: 40px 280px;
    cursor: pointer;
    border-radius: 7px;
}

.top-right-bar {
    position: fixed;
    top: 20px;
    right: 20px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 15px;
    z-index: 1000;
    width: auto;
}


.profilename {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border: 2px solid #4a4a4a;
    border-radius: 6px;
    text-decoration: none;
    color: #4a4a4a;
    font-weight: bold;
    font-family: sans-serif;
    background-color: transparent;
    transition: background-color 0.2s;
}

.profile-icon {
    width: 20px;
    height: 20px;
    object-fit: contain;
}

.logout-icon {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

    .container2 {
      width: 90%;
      max-width: 800px;
      margin: 50px auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0 0 10px #ccc;
      padding: 20px;
    }
    h2 {
      margin-bottom: 10px;
    }
    input[type="text"] {
      width: 50%;
      padding: 8px;
      margin-bottom: 15px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    .actions button {
      border: none;
      padding: 6px 10px;
      margin: 0 5px;
      font-size: 18px;
      cursor: pointer;
      border-radius: 50%;
    }
    .approve {
      background-color: #c6f6c6;
    }
    .reject {
      background-color: #f6c6c6;
    }
     .dropdown {
      display: none;
      flex-direction: column;
      background-color: rgba(255, 255, 255, 0.3);
    }

    .menu-item:hover .dropdown {
      display: flex;
    }

    .dropdown-item {
      padding: 10px 30px;
      cursor: pointer;
    }

    .dropdown-item:hover {
      background-color: rgba(255, 255, 255, 0.6);
    }
  </style>
</head>
<body>

<div class="container">
   <div class="sidebar">
        <li class="listyle"><a href="admin.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a></li>
        <hr>
        <li class="listyle"><a href="staffProfile.php" class="nav-item">Profile</a>
        <a href="admin_manage_profile.php" class="nav-item">Manage Profile</a>
        </li>
        <hr>
        <li class="listyle"><a href="admin.php" class="nav-item">Dashboard</a></li>
        <hr>
        <li class="listyle"><a href="adminMember.php" class="nav-item">Manage Membership</a></li>
        <hr>
        <li class="listyle"><a href="admin_view_event.php" class="nav-item ">View Event</a></li>
        <a href="adminAttendanceDash.php" class="nav-item active">>Attendance</a>
        <hr>
    </div>

  <main class="main-content">
    <header class="dashboard-header">
      <h1>Administrator Dashboard</h1>
      <div class="user-info">👤 Hi, Admin</div>
    </header>

    <div class="summary-section">
      <div class="summary-card">
        <h2><?= $checkins ?></h2>
        <p>Total Check-ins</p>
      </div>
      <div class="summary-card">
        <h2><?= $totalEvents ?></h2>
        <p>Total Events</p>
      </div>
      <div class="summary-card">
        <h2><?= $averageRate ?>%</h2>
        <p>Average Attendance Rate</p>
      </div>
    </div>

    <div class="charts-row">
      <div class="chart-container">
        <h3>Attendance Rate by Event</h3>
        <canvas id="eventChart"></canvas>
      </div>
      <div class="chart-container">
        <h3>Student Attendance by Course</h3>
        <canvas id="courseChart"></canvas>
      </div>
      <div class="chart-container">
        <h3>Top 5 Most Attended Events</h3>
        <canvas id="topEventChart"></canvas>
      </div>
    </div>
  </main>
</div>

<script>
const topEventChart = new Chart(document.getElementById('topEventChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($topEventNames) ?>,
    datasets: [{
      label: 'Check-ins',
      data: <?= json_encode($topEventCounts) ?>,
      backgroundColor: '#8e44ad'
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false }
    },
    scales: {
      y: { beginAtZero: true }
    }
  }
});

const eventChart = new Chart(document.getElementById('eventChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($eventNames) ?>,
    datasets: [{
      label: 'Attendance Rate (%)',
      data: <?= json_encode($eventRates) ?>,
      backgroundColor: '#3498db'
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true, max: 100 }
    }
  }
});

const courseChart = new Chart(document.getElementById('courseChart'), {
  type: 'pie',
  data: {
    labels: <?= json_encode($courseLabels) ?>,
    datasets: [{
      data: <?= json_encode($courseCounts) ?>,
      backgroundColor: ['#f1c40f', '#2ecc71', '#e74c3c', '#9b59b6', '#1abc9c']
    }]
  },
  options: {
    plugins: {
      datalabels: {
        formatter: (value, context) => {
          const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
          if (value === 0) return '';
          const percentage = ((value / total) * 100).toFixed(1);
          return `${context.chart.data.labels[context.dataIndex]}: ${percentage}%`;
        },
        color: '#000',
        font: {
          weight: 'bold',
          size: 14
        },
        anchor: 'center',
        align: 'center'
      }
    }
  },
  plugins: [ChartDataLabels]
});
</script>
</body>
</html>