<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mypetakom";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Count students
$student_sql = "SELECT COUNT(studentID) AS total_students FROM student";
$student_result = $conn->query($student_sql);
$student_count = $student_result->fetch_assoc()['total_students'];

// Count staff
$advisor_sql = "SELECT COUNT(staffID) AS total_advisors FROM staff WHERE staffRole = 'advisor'";
$advisor_result = $conn->query($advisor_sql);
$advisor_count = $advisor_result->fetch_assoc()['total_advisors'];

$staff_sql = "SELECT COUNT(staffID) AS total_staff FROM staff WHERE staffRole = 'admin'";
$staff_result = $conn->query($staff_sql);
$staff_count = $staff_result->fetch_assoc()['total_staff'];

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>

    <meta name="description" content=" admin Dashboard for myPetakom">
    <meta name="author" content="UMI MAISARAH BINTI MOHD AFENDI">
    <title>MyPetakom</title>
    <link rel="stylesheet" type="text/css" href="style/admin_manage_profile.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <meta charset="UTF-8">

    <title>Dashboard Overview</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style/dashboard.css">
</head>
<body class="background">

    <div class="sidebar">
        <li class="listyle"><a href="student.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a></li>
        <hr>

        <li class="listyle"><a href="staffProfile.php" class="nav-item">Profile</a></li>

        <li class="listyle"><a  href="admin_manage_profile.php" class="nav-item">Manage Profile</a>
        </li>
        <hr>

        <li class="listyle"><a class="active" href="admin.php" class="nav-item">Dashboard</a></li>
        <hr>

        <li class="listyle"><a href="adminMember.php" class="nav-item">Manage Membership</a></li>
        <hr>

        <li class="listyle"><a href="#" class="nav-item">View Event</a></li>
        <hr>

    </div>

        <div class="h1text">
        <h1>PROFILE MANAGEMENT</h1>
    </div>
    

   <div class="top-right-bar">
        <a href="staffProfile.php" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">HI, ADMIN
        </a>
        <a href="logout.php">
            <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
        </a>
    </div>

    <div class="stats-container">
        <div class="card"><strong>Students</strong><p class="count"><?php echo $student_count; ?></p></div>
        <div class="card"><strong>Event Advisor</strong><p class="count"><?php echo $advisor_count; ?></p></div>
        <div class="card"><strong>Admin Member</strong><p class="count"><?php echo $staff_count; ?></p></div>
    </div>

    <div class="charts-container">
    <div class="chart-card">
        <h3>Login Trends</h3>
        <canvas id="lineChart"></canvas>
    </div>
    <div class="chart-card">
        <h3>User Distribution</h3>
        <canvas id="pieChart"></canvas>
    </div>
</div>


    <script>
        // Dummy data for Login Trends
        const lineChart = new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: ['04-12', '04-14', '04-16', '04-18', '04-20', '04-22', '04-24', '04-26', '04-28', '05-02'],
                datasets: [{
                    label: 'Logins',
                    data: [50, 30, 45, 40, 38, 35, 48, 55, 42, 47],
                    borderColor: '#8B0000',
                    fill: false
                }]
            },
            options: {
                responsive: true
            }
        });


        // Pie Chart for User Distribution
const pieChart = new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: ['Students', 'Admins', 'Advisors'],
        datasets: [{
            label: 'User Distribution',
            data: [<?php echo $student_count; ?>, <?php echo $staff_count; ?>, <?php echo $advisor_count; ?>],
            backgroundColor: [
                '#6495ED', // Students
                '#A9A9A9', // Admins
                '#FFD700'  // Advisors
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true
    }
});

    </script>

</body>
</html>
