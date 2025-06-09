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

$staffID = $_SESSION['userID'];

try {
    $eventID = $_GET['eventID'] ?? '';
    
    if (empty($eventID)) {
        echo json_encode(['success' => false, 'message' => 'Event ID is required']);
        exit;
    }
    
    // Get event details for events created by current staff
    $query = "SELECT 
                eventID,
                eventName,
                description,
                startdate,
                enddate,
                eventLevel,
                eventLocation,
                latitude,
                longitude,
                eventStatus,
                approvalLetter,
                meritApplication
              FROM event 
              WHERE eventID = ? AND staffID = ?";
    
    $stmt = $data->prepare($query);
    $stmt->bind_param("ss", $eventID, $staffID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        echo json_encode(['success' => true, 'event' => $event]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Event not found or you do not have permission to view this event']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);
?>