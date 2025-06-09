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
<html>

<head>
    <meta name="description" content="MyPetakom">
    <meta name="author" content="Wardah Wafin">
    <title>MyPetakom</title>
    <link rel="stylesheet" href="style/advisor_dash.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="background">

    <div class="sidebar">
        <a href="advisor_dash.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a>
        <hr>
        <a href="advisor_dash.php" class="nav-item">Home</a>
        <hr>

        <a href="profile.php" class="nav-item">Profile</a>
        <hr>

        <span class="nav-item event-title">Event</span>
        <div class="submenu nav-itemhover">
            <a href="event_list.php">&gt; Event List</a>
            <a href="committee.php">&gt; Committee</a>
            <a href="event_registration.php">&gt; Registration</a>
            <a href="attendance.php">&gt; Attendance</a>
        </div>
    </div>

    <div class="top-right-bar">
        <a href="profile.php" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">HI, <?php echo $userType; ?>
        </a>
        <a href="login/logout.php">
            <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
        </a>
    </div>

    <div class="main-content">
        <div class="content-wrapper">
            <div class="new-event-section">
                <button class="button button4" onclick="window.location.href='event_registration.php'">+ New Event</button>
            </div>

            <div class="metrics-container">
                <!-- Total Events Card -->
                <div class="metric-card">
                    <div class="metric-title">Total Events</div>
                    <div class="metric-value" id="totalEvents">0</div>
                    <div class="metric-change positive">
                        <span id="totalEventsChange">Loading...</span>
                    </div>
                </div>

                <!-- Upcoming Events Card -->
                <div class="metric-card">
                    <div class="metric-title">Upcoming Events</div>
                    <div class="metric-value" id="upcomingEvents">0</div>
                    <div class="metric-change negative">
                        <span id="upcomingEventsChange">Loading...</span>
                    </div>
                </div>

                <!-- Students Participation Card -->
                <div class="metric-card">
                    <div class="metric-title">Students Participation</div>
                    <div class="metric-value" id="studentsParticipation">0</div>
                    <div class="metric-change positive">
                        <span id="participationChange">Loading...</span>
                    </div>
                </div>

                <!-- Merit Points Card -->
                <div class="metric-card">
                    <div class="metric-title">Merit Points Awarded</div>
                    <div class="metric-value" id="meritPoints">0</div>
                    <div class="metric-change positive">
                        <span id="meritChange">Loading...</span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <!-- Row 1: Events Timeline & Events Status -->
                <div class="charts-row">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3>Events Timeline</h3>
                            <p class="chart-description">Event creation trends over the last 12 months</p>
                        </div>
                        <canvas id="timelineChart"></canvas>
                    </div>

                    <div class="chart-container">
                        <div class="chart-header">
                            <h3>Events by Status</h3>
                            <p class="chart-description">Current status breakdown of all events</p>
                        </div>
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Row 2: Participation Summary -->
                <div class="charts-row">
                    <div class="chart-container summary-container full-width">
                        <div class="chart-header">
                            <h3>Student Participation Summary</h3>
                            <p class="chart-description">Student engagement statistics across all events</p>
                        </div>
                        <div class="summary-stats" id="participationSummary">
                            <div class="summary-item">
                                <div class="summary-number" id="totalStudents">0</div>
                                <div class="summary-label">Total Students</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number" id="totalPositions">0</div>
                                <div class="summary-label">Total Positions</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number" id="avgPositions">0</div>
                                <div class="summary-label">Avg. Positions/Student</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                            borderColor: '#4BC0C0',
                            backgroundColor: 'rgba(75, 192, 192, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#4BC0C0',
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
                            backgroundColor: chartsData.eventStatus.colors,
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
                document.getElementById('totalStudents').textContent = summary.totalStudents;
                document.getElementById('totalPositions').textContent = summary.totalPositions;
                document.getElementById('avgPositions').textContent = summary.avgPositionsPerStudent;
            }
        }

        function showEmptyChart(canvasId, message) {
            const canvas = document.getElementById(canvasId);
            const container = canvas.parentElement;
            const emptyDiv = document.createElement('div');
            emptyDiv.className = 'chart-empty';
            emptyDiv.innerHTML = `
                <h4>No Data Available</h4>
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

    <style>
        /* Enhanced styles for 3-chart layout */
        .charts-section {
            display: flex;
            flex-direction: column;
            gap: 30px;
            margin-top: 30px;
        }

        .charts-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .chart-container {
            flex: 1;
            min-width: 400px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            height: 400px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .chart-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .chart-container canvas {
            max-height: 300px;
        }

        .chart-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }

        .chart-header h3 {
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

        /* Summary Container Styles */
        .summary-container {
            display: flex;
            flex-direction: column;
        }
        
        .full-width {
            min-width: 100%;
        }

        .summary-stats {
            display: flex;
            justify-content: space-around;
            align-items: center;
            flex: 1;
            margin-top: 20px;
            gap: 20px;
        }

        .summary-item {
            text-align: center;
            padding: 25px 30px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            flex: 1;
            max-width: 250px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid rgba(169, 0, 0, 0.1);
        }

        .summary-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .summary-number {
            font-size: 36px;
            font-weight: bold;
            color: #a90000;
            margin-bottom: 10px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
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

        .chart-empty h4 {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #666;
        }

        .chart-empty p {
            margin: 0;
            font-size: 14px;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .charts-row {
                flex-direction: column;
            }
            
            .chart-container {
                min-width: auto;
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .chart-container {
                height: 350px;
                padding: 20px;
            }
            
            .chart-container canvas {
                max-height: 250px;
            }
            
            .summary-stats {
                flex-direction: column;
                gap: 15px;
            }
            
            .summary-item {
                width: 100%;
                max-width: none;
            }
        }
    </style>

</body>

</html>