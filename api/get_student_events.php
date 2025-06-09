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
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if user is student
if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. UserType: ' . ($_SESSION['userType'] ?? 'not set')]);
    exit;
}

$studentID = $_SESSION['userID'];

try {
    // Get events where student is a committee member
    $studentEventsQuery = "SELECT 
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
                            ec.committeePosition
                          FROM eventcommittee ec
                          JOIN event e ON ec.eventID = e.eventID
                          WHERE ec.studentID = ?
                          ORDER BY e.startdate DESC";
    
    $stmt = $data->prepare($studentEventsQuery);
    $stmt->bind_param("s", $studentID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    
    if (count($events) > 0) {
        // Student has events assigned
        echo json_encode(['success' => true, 'events' => $events]);
    } else {
        // Student has no events, get available events with real staff info
        $availableEventsQuery = "SELECT 
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
                              WHERE e.enddate >= CURDATE()
                              ORDER BY e.startdate ASC";
        
        $availableStmt = $data->prepare($availableEventsQuery);
        $availableStmt->execute();
        $availableResult = $availableStmt->get_result();
        
        $availableEvents = [];
        while ($row = $availableResult->fetch_assoc()) {
            $availableEvents[] = $row;
        }
        
        echo json_encode([
            'success' => true, 
            'events' => [], 
            'availableEvents' => $availableEvents
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);
?>