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
      width: 230px;
      background: linear-gradient(to bottom, #ba7373, #5b2c2c);
      color: white;
      height: 100vh;
      padding: 20px 10px;
      box-sizing: border-box;
    }
    .sidebar .logo {
      width: 150px;
      display: block;
      margin: 0 auto 20px;
    }
    .sidebar nav {
      display: flex;
      flex-direction: column;
    }
    .sidebar .nav-item {
      padding: 10px;
      margin: 5px 0;
      background-color: transparent;
      color: white;
      text-decoration: none;
      font-weight: bold;
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
      border-top: 1px solid #ffffff66;
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
  </style>
</head>
<body>

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
