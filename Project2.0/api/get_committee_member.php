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
    $committeeID = intval($_GET['committeeID'] ?? 0);
    
    if ($committeeID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Valid Committee ID is required']);
        exit;
    }
    
    // Get committee member details (only for events created by current staff)
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
              WHERE ec.committeeID = ? AND e.staffID = ?";
    
    $stmt = $data->prepare($query);
    $stmt->bind_param("is", $committeeID, $staffID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'member' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Committee member not found or you do not have permission to access this record']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);
?>