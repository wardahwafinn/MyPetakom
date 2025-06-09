<?php
session_start();
header('Content-Type: application/json');

// Database configuration
$host = "localhost";
$user = "root";
$db_password = "";
$db = "mypetakom";

// Connect to the database
$data = mysqli_connect($host, $user, $db_password, $db);
if ($data === false) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if user is advisor or admin
if (!isset($_SESSION['userType']) || ($_SESSION['userType'] != 'advisor' && $_SESSION['userType'] != 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$staffID = $_SESSION['userID'];

try {
    $charts = [];
    
    // Chart 1: Events Timeline by Month (Line Chart Data)
    $timelineQuery = "SELECT 
                          DATE_FORMAT(startdate, '%Y-%m') as month,
                          COUNT(*) as count
                      FROM event 
                      WHERE staffID = ? 
                      AND startdate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                      GROUP BY DATE_FORMAT(startdate, '%Y-%m')
                      ORDER BY month ASC";
    
    $stmt = $data->prepare($timelineQuery);
    $stmt->bind_param("s", $staffID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $months = [];
    $monthlyCounts = [];
    
    while ($row = $result->fetch_assoc()) {
        $monthName = date('M Y', strtotime($row['month'] . '-01'));
        $months[] = $monthName;
        $monthlyCounts[] = (int)$row['count'];
    }
    
    $charts['eventsTimeline'] = [
        'labels' => $months,
        'data' => $monthlyCounts,
        'title' => 'Events Created Over Time (Last 12 Months)'
    ];
    
    // Chart 2: Student Participation Summary
    $participationQuery = "SELECT 
                               COUNT(DISTINCT ec.studentID) as total_students,
                               COUNT(ec.committeeID) as total_positions,
                               AVG(student_count.position_count) as avg_positions_per_student
                           FROM eventcommittee ec
                           JOIN event e ON ec.eventID = e.eventID
                           JOIN (
                               SELECT 
                                   ec2.studentID,
                                   COUNT(*) as position_count
                               FROM eventcommittee ec2
                               JOIN event e2 ON ec2.eventID = e2.eventID
                               WHERE e2.staffID = ?
                               GROUP BY ec2.studentID
                           ) student_count ON ec.studentID = student_count.studentID
                           WHERE e.staffID = ?";
    
    $stmt = $data->prepare($participationQuery);
    $stmt->bind_param("ss", $staffID, $staffID);
    $stmt->execute();
    $result = $stmt->get_result();
    $participationData = $result->fetch_assoc();
    
    $charts['participationSummary'] = [
        'totalStudents' => (int)($participationData['total_students'] ?? 0),
        'totalPositions' => (int)($participationData['total_positions'] ?? 0),
        'avgPositionsPerStudent' => round($participationData['avg_positions_per_student'] ?? 0, 2),
        'title' => 'Student Participation Summary'
    ];
    
    // Chart 3: Events Status Distribution
    $statusQuery = "SELECT 
                        CASE 
                            WHEN eventStatus = 0 THEN 'Cancelled'
                            WHEN enddate < CURDATE() THEN 'Completed'
                            WHEN startdate > CURDATE() THEN 'Upcoming'
                            WHEN startdate <= CURDATE() AND enddate >= CURDATE() THEN 'Active'
                            ELSE 'Unknown'
                        END as status,
                        COUNT(*) as count
                    FROM event 
                    WHERE staffID = ?
                    GROUP BY 
                        CASE 
                            WHEN eventStatus = 0 THEN 'Cancelled'
                            WHEN enddate < CURDATE() THEN 'Completed'
                            WHEN startdate > CURDATE() THEN 'Upcoming'
                            WHEN startdate <= CURDATE() AND enddate >= CURDATE() THEN 'Active'
                            ELSE 'Unknown'
                        END
                    ORDER BY count DESC";
    
    $stmt = $data->prepare($statusQuery);
    $stmt->bind_param("s", $staffID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $statusLabels = [];
    $statusCounts = [];
    $statusColors = [
        'Active' => '#28a745',
        'Upcoming' => '#007bff',
        'Completed' => '#6c757d',
        'Cancelled' => '#dc3545'
    ];
    $colors = [];
    
    while ($row = $result->fetch_assoc()) {
        $statusLabels[] = $row['status'];
        $statusCounts[] = (int)$row['count'];
        $colors[] = $statusColors[$row['status']] ?? '#999999';
    }
    
    $charts['eventStatus'] = [
        'labels' => $statusLabels,
        'data' => $statusCounts,
        'colors' => $colors,
        'title' => 'Events by Status'
    ];
    
    echo json_encode(['success' => true, 'charts' => $charts]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);
?>