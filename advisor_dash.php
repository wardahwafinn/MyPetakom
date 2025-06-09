<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login/loginForm.php");
    exit;
}

// Check if user is advisor or admin
if (!isset($_SESSION['userType']) || ($_SESSION['userType'] != 'advisor' && $_SESSION['userType'] != 'admin')) {
    header("Location: login/loginForm.php");
    exit;
}

// Get user type for display
$userType = strtoupper($_SESSION['userType']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MyPetakom">
    <meta name="author" content="Wardah Wafin">
    <title>MyPetakom - Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="icon" type="image/png" href="images/petakom.png">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    
    <style>
        /* Custom styles to complement Bootstrap */
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background-image: url("images/bg.png");
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* Original Sidebar Design */
        .sidebar {
            width: 210px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            box-sizing: border-box;
            z-index: 1000;
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
            transition: background-color 0.3s;
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
            transition: background-color 0.3s;
        }

        .submenu a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Original Top Right Bar */
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

        /* Main content with original sidebar offset */
        .main-content {
            margin-left: 230px;
            margin-right: 20px;
            padding: 20px;
            padding-top: 80px; /* Add space for top-right bar */
            min-height: 100vh;
            overflow-y: auto;
        }

        /* Custom Bootstrap overrides */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        /* Custom brand colors */
        .btn-primary {
            background-color: #a90000;
            border-color: #a90000;
            padding: 12px 30px;
            font-size: 14px;
            border-radius: 7px;
        }

        .btn-primary:hover {
            background-color: #8b0000;
            border-color: #8b0000;
        }

        .text-primary {
            color: #a90000 !important;
        }

        .border-primary {
            border-color: #a90000 !important;
        }

        /* Custom metric cards */
        .metric-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
            height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .metric-title {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .metric-value {
            font-size: 32px;
            font-weight: bold;
            color: #a90000;
            margin: 8px 0;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .metric-change {
            font-size: 10px;
            color: #666;
            line-height: 1.3;
        }

        .metric-change.positive {
            color: #28a745;
        }

        .metric-change.negative {
            color: #dc3545;
        }

        /* Chart containers */
        .chart-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
            height: 450px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .chart-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .chart-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .chart-header h5 {
            color: #333;
            font-size: 18px;
            margin: 0 0 8px 0;
            font-weight: bold;
        }

        .chart-description {
            color: #666;
            font-size: 13px;
            margin: 0;
            font-style: italic;
            line-height: 1.4;
        }

        .chart-canvas-container {
            height: 320px;
            position: relative;
        }

        .chart-canvas-container canvas {
            max-height: 100% !important;
        }

        /* Summary stats */
        .summary-stats-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
            height: 450px;
        }

        .summary-stats {
            display: flex;
            justify-content: space-around;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
            height: calc(100% - 80px);
        }

        .summary-item {
            text-align: center;
            padding: 25px 30px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            flex: 1;
            min-width: 200px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid rgba(169, 0, 0, 0.1);
        }

        .summary-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }

        .summary-number {
            font-size: 36px;
            font-weight: bold;
            color: #a90000;
            margin-bottom: 10px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .summary-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 1.2;
        }

        /* Empty state for charts */
        .chart-empty {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 300px;
            color: #999;
            text-align: center;
        }

        .chart-empty h6 {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #666;
        }

        .chart-empty p {
            margin: 0;
            font-size: 14px;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 220px;
                margin-right: 10px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                margin-right: 0;
                padding: 10px;
                padding-top: 120px; /* Extra space for mobile top-right bar */
            }
            
            .top-right-bar {
                position: relative;
                top: 0;
                right: 0;
                justify-content: center;
                margin-bottom: 20px;
            }
            
            .metric-card {
                height: auto;
                min-height: 120px;
            }
            
            .chart-card, .summary-stats-card {
                height: auto;
                min-height: 350px;
            }
            
            .chart-canvas-container {
                height: 250px;
            }
            
            .summary-stats {
                flex-direction: column;
                height: auto;
                gap: 15px;
            }
            
            .summary-item {
                width: 100%;
                min-width: auto;
            }
        }
    </style>
</head>

<body>
    <!-- Original Sidebar -->
    <div class="sidebar">
        <a href="advisor_dash.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a>
        <hr>
        <a href="advisor_dash.php" class="nav-item">Home</a>
        <hr>
        <a href="profile.php" class="nav-item">Profile</a>
        <hr>
        <span class="nav-item event-title">Event</span>
        <div class="submenu">
            <a href="event_list.php">&gt; Event List</a>
            <a href="committee.php">&gt; Committee</a>
            <a href="event_registration.php">&gt; Registration</a>
            <a href="attendance.php">&gt; Attendance</a>
        </div>
    </div>

    <!-- Original Top Right Bar -->
    <div class="top-right-bar">
        <a href="profile.php" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">HI, <?php echo $userType; ?>
        </a>
        <a href="login/logout.php">
            <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Header with New Event Button -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="text-primary mb-0">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard Overview
                        </h2>
                        <button class="btn btn-primary" onclick="window.location.href='event_registration.php'">
                            <i class="bi bi-plus-circle me-2"></i>New Event
                        </button>
                    </div>
                </div>
            </div>

            <!-- Metrics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-title">
                            <i class="bi bi-calendar-event me-1"></i>Total Events
                        </div>
                        <div class="metric-value" id="totalEvents">0</div>
                        <div class="metric-change">
                            <span id="totalEventsChange">Loading...</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-title">
                            <i class="bi bi-calendar-plus me-1"></i>Upcoming Events
                        </div>
                        <div class="metric-value" id="upcomingEvents">0</div>
                        <div class="metric-change">
                            <span id="upcomingEventsChange">Loading...</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-title">
                            <i class="bi bi-people me-1"></i>Students Participation
                        </div>
                        <div class="metric-value" id="studentsParticipation">0</div>
                        <div class="metric-change positive">
                            <span id="participationChange">Loading...</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-title">
                            <i class="bi bi-award me-1"></i>Merit Points Awarded
                        </div>
                        <div class="metric-value" id="meritPoints">0</div>
                        <div class="metric-change positive">
                            <span id="meritChange">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mb-4">
                <!-- Events Timeline Chart -->
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h5>
                                <i class="bi bi-graph-up me-2 text-primary"></i>Events Timeline
                            </h5>
                            <p class="chart-description">Event creation trends over the last 12 months</p>
                        </div>
                        <div class="chart-canvas-container">
                            <canvas id="timelineChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Events by Status Chart -->
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h5>
                                <i class="bi bi-pie-chart me-2 text-primary"></i>Events by Status
                            </h5>
                            <p class="chart-description">Current status breakdown of all events</p>
                        </div>
                        <div class="chart-canvas-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Participation Summary -->
            <div class="row">
                <div class="col-12">
                    <div class="summary-stats-card">
                        <div class="chart-header">
                            <h5>
                                <i class="bi bi-bar-chart me-2 text-primary"></i>Student Participation Summary
                            </h5>
                            <p class="chart-description">Student engagement statistics across all events</p>
                        </div>
                        <div class="summary-stats" id="participationSummary">
                            <div class="summary-item">
                                <div class="summary-number" id="totalStudents">0</div>
                                <div class="summary-label">
                                    <i class="bi bi-people me-1"></i>Total Students
                                </div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number" id="totalPositions">0</div>
                                <div class="summary-label">
                                    <i class="bi bi-briefcase me-1"></i>Total Positions
                                </div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number" id="avgPositions">0</div>
                                <div class="summary-label">
                                    <i class="bi bi-graph-up-arrow me-1"></i>Avg. Positions/Student
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let chartInstances = {};
        
        // Load dashboard statistics and charts
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardStats();
            loadChartData();
        });

        function loadDashboardStats() {
            // Fetch real statistics from database
            fetch('api/get_dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the metrics with real data
                        document.getElementById('totalEvents').textContent = data.stats.totalEvents || '0';
                        document.getElementById('upcomingEvents').textContent = data.stats.upcomingEvents || '0';
                        document.getElementById('studentsParticipation').textContent = data.stats.studentsParticipation || '0';
                        document.getElementById('meritPoints').textContent = data.stats.meritPoints || '0';
                        
                        // Update change indicators
                        document.getElementById('totalEventsChange').textContent = 'Total events created';
                        document.getElementById('upcomingEventsChange').textContent = 'Events not yet completed';
                        document.getElementById('participationChange').textContent = 'Unique students involved';
                        document.getElementById('meritChange').textContent = 'Points from merit events';
                    }
                })
                .catch(error => {
                    console.error('Error loading dashboard stats:', error);
                });
        }

        function loadChartData() {
            fetch('api/get_chart_data.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        createCharts(data.charts);
                    } else {
                        console.error('Error loading chart data:', data.message);
                        showChartErrors();
                    }
                })
                .catch(error => {
                    console.error('Error loading charts:', error);
                    showChartErrors();
                });
        }

        function createCharts(chartsData) {
            // 1. Events Timeline (Line Chart)
            if (chartsData.eventsTimeline && chartsData.eventsTimeline.data.length > 0) {
                const ctx1 = document.getElementById('timelineChart').getContext('2d');
                chartInstances.timeline = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: chartsData.eventsTimeline.labels,
                        datasets: [{
                            label: 'Events Created',
                            data: chartsData.eventsTimeline.data,
                            borderColor: '#a90000',
                            backgroundColor: 'rgba(169, 0, 0, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#a90000',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            } else {
                showEmptyChart('timelineChart', 'No timeline data available');
            }

            // 2. Event Status (Pie Chart)
            if (chartsData.eventStatus && chartsData.eventStatus.data.length > 0) {
                const ctx2 = document.getElementById('statusChart').getContext('2d');
                chartInstances.status = new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: chartsData.eventStatus.labels,
                        datasets: [{
                            data: chartsData.eventStatus.data,
                            backgroundColor: chartsData.eventStatus.colors || [
                                '#a90000', '#dc3545', '#ffc107', '#28a745', '#17a2b8'
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            }
                        }
                    }
                });
            } else {
                showEmptyChart('statusChart', 'No status data available');
            }

            // 3. Participation Summary (Text Display)
            if (chartsData.participationSummary) {
                const summary = chartsData.participationSummary;
                document.getElementById('totalStudents').textContent = summary.totalStudents || '0';
                document.getElementById('totalPositions').textContent = summary.totalPositions || '0';
                document.getElementById('avgPositions').textContent = summary.avgPositionsPerStudent || '0';
            }
        }

        function showEmptyChart(canvasId, message) {
            const canvas = document.getElementById(canvasId);
            const container = canvas.parentElement;
            const emptyDiv = document.createElement('div');
            emptyDiv.className = 'chart-empty';
            emptyDiv.innerHTML = `
                <h6><i class="bi bi-inbox text-muted me-2"></i>No Data Available</h6>
                <p>${message}</p>
            `;
            container.appendChild(emptyDiv);
            canvas.style.display = 'none';
        }

        function showChartErrors() {
            const chartIds = ['timelineChart', 'statusChart'];
            chartIds.forEach(id => {
                showEmptyChart(id, 'Error loading chart data');
            });
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            Object.values(chartInstances).forEach(chart => {
                if (chart) chart.resize();
            });
        });
    </script>
</body>
</html>