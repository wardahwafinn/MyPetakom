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
    
    // Debug logging
    error_log("get_committee.php - EventID filter: '$eventID'");
    error_log("get_committee.php - Current staffID: '$staffID'");
    
    // Base query to get committee members for events created by current staff
    $query = "SELECT 
                ec.committeeID,
                ec.eventID,
                ec.studentID,
                ec.committeePosition,
                e.eventName,
                s.studentName
              FROM eventcommittee ec
              JOIN event e ON ec.eventID = e.eventID
              JOIN student s ON ec.studentID = s.studentID
              WHERE e.staffID = ?";
    
    $params = [$staffID];
    $types = "s";
    
    // If specific event ID is provided, filter by it
    if (!empty($eventID)) {
        $query .= " AND ec.eventID = ?";
        $params[] = $eventID;
        $types .= "s";
    }
    
    $query .= " ORDER BY e.eventName, ec.committeePosition, s.studentName";
    
    error_log("get_committee.php - Final query: $query");
    error_log("get_committee.php - Parameters: " . print_r($params, true));
    
    $stmt = $data->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $committee = [];
    while ($row = $result->fetch_assoc()) {
        $committee[] = $row;
    }
    
    error_log("get_committee.php - Found " . count($committee) . " committee members");
    
    echo json_encode(['success' => true, 'committee' => $committee]);

} catch (Exception $e) {
    error_log("get_committee.php - Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);
?>