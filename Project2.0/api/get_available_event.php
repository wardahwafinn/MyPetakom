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

// Check if user is student
if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $eventID = $_GET['id'] ?? '';
    
    if (empty($eventID)) {
        echo json_encode(['success' => false, 'message' => 'Event ID is required']);
        exit;
    }
    
    // Get available event details with real advisor information
    $query = "SELECT 
                e.eventID,
                e.eventName,
                e.description,
                e.startdate,
                e.enddate,
                e.eventLevel,
                e.eventLocation,
                e.latitude,
                e.longitude,
                e.meritApplication,
                s.staffName as advisorName,
                s.staffEmail as advisorEmail
              FROM event e
              JOIN staff s ON e.staffID = s.staffID
              WHERE e.eventID = ?";
    
    $stmt = $data->prepare($query);
    $stmt->bind_param("s", $eventID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        echo json_encode(['success' => true, 'event' => $event]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Event not found or not available']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);
?>