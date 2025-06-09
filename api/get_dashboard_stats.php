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
    $stats = [];
    
    // Get total events for this staff
    $totalEventsQuery = "SELECT COUNT(*) as total FROM event WHERE staffID = ?";
    $totalEventsStmt = $data->prepare($totalEventsQuery);
    $totalEventsStmt->bind_param("s", $staffID);
    $totalEventsStmt->execute();
    $totalEventsResult = $totalEventsStmt->get_result();
    $totalEvents = $totalEventsResult->fetch_assoc()['total'];
    $stats['totalEvents'] = $totalEvents;
    
    // Get upcoming events (events that haven't ended yet) for this staff
    $upcomingEventsQuery = "SELECT COUNT(*) as total FROM event WHERE staffID = ? AND enddate >= CURDATE()";
    $upcomingEventsStmt = $data->prepare($upcomingEventsQuery);
    $upcomingEventsStmt->bind_param("s", $staffID);
    $upcomingEventsStmt->execute();
    $upcomingEventsResult = $upcomingEventsStmt->get_result();
    $upcomingEvents = $upcomingEventsResult->fetch_assoc()['total'];
    $stats['upcomingEvents'] = $upcomingEvents;
    
    // Get students participation (total committee members for this staff's events)
    $participationQuery = "SELECT COUNT(DISTINCT ec.studentID) as total 
                          FROM eventcommittee ec 
                          JOIN event e ON ec.eventID = e.eventID 
                          WHERE e.staffID = ?";
    $participationStmt = $data->prepare($participationQuery);
    $participationStmt->bind_param("s", $staffID);
    $participationStmt->execute();
    $participationResult = $participationStmt->get_result();
    $participation = $participationResult->fetch_assoc()['total'];
    $stats['studentsParticipation'] = $participation;
    
    // Get merit points awarded (count of events with merit application approved by this staff)
    $meritQuery = "SELECT COUNT(*) as total FROM event WHERE staffID = ? AND meritApplication = 1";
    $meritStmt = $data->prepare($meritQuery);
    $meritStmt->bind_param("s", $staffID);
    $meritStmt->execute();
    $meritResult = $meritStmt->get_result();
    $meritPoints = $meritResult->fetch_assoc()['total'] * 10; // Assume 10 points per event
    $stats['meritPoints'] = $meritPoints;
    
    echo json_encode(['success' => true, 'stats' => $stats]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);
?>