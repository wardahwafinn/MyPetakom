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

// Check if user is logged in and get staffID
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
    // Debug: Log received POST data
    error_log("Received POST data: " . print_r($_POST, true));
    
    // Get form data
    $eventID = mysqli_real_escape_string($data, $_POST['eventID'] ?? '');
    $studentID = mysqli_real_escape_string($data, $_POST['studentID'] ?? '');
    $committeePosition = mysqli_real_escape_string($data, $_POST['committeePosition'] ?? '');

    // Debug: Log processed values
    error_log("Processed values - EventID: '$eventID', StudentID: '$studentID', Position: '$committeePosition'");

    // Validate required fields
    if (empty($eventID) || empty($studentID) || empty($committeePosition)) {
        $missing = [];
        if (empty($eventID)) $missing[] = 'eventID';
        if (empty($studentID)) $missing[] = 'studentID';
        if (empty($committeePosition)) $missing[] = 'committeePosition';
        
        echo json_encode([
            'success' => false, 
            'message' => 'Missing required fields: ' . implode(', ', $missing),
            'debug' => [
                'eventID' => $eventID,
                'studentID' => $studentID,
                'committeePosition' => $committeePosition,
                'received_post' => $_POST
            ]
        ]);
        exit;
    }

    // Check if the event belongs to the current staff
    $eventCheckQuery = "SELECT eventID FROM event WHERE eventID = ? AND staffID = ?";
    $eventCheckStmt = $data->prepare($eventCheckQuery);
    $eventCheckStmt->bind_param("ss", $eventID, $staffID);
    $eventCheckStmt->execute();
    $eventCheckResult = $eventCheckStmt->get_result();
    
    if ($eventCheckResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Event not found or you do not have permission to add committee members to this event']);
        exit;
    }

    // Check if student exists in student table
    $studentCheckQuery = "SELECT studentID FROM student WHERE studentID = ?";
    $studentCheckStmt = $data->prepare($studentCheckQuery);
    $studentCheckStmt->bind_param("s", $studentID);
    $studentCheckStmt->execute();
    $studentCheckResult = $studentCheckStmt->get_result();
    
    if ($studentCheckResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found in the database']);
        exit;
    }

    // Check if student is already a committee member for this event
    $duplicateCheckQuery = "SELECT committeeID FROM eventcommittee WHERE eventID = ? AND studentID = ?";
    $duplicateCheckStmt = $data->prepare($duplicateCheckQuery);
    $duplicateCheckStmt->bind_param("ss", $eventID, $studentID);
    $duplicateCheckStmt->execute();
    $duplicateCheckResult = $duplicateCheckStmt->get_result();
    
    if ($duplicateCheckResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This student is already a committee member for this event']);
        exit;
    }

    // Insert committee member (committeeID will auto-increment)
    $insertQuery = "INSERT INTO eventcommittee (eventID, studentID, committeePosition) VALUES (?, ?, ?)";
    $insertStmt = $data->prepare($insertQuery);
    $insertStmt->bind_param("sss", $eventID, $studentID, $committeePosition);
    
    if ($insertStmt->execute()) {
        $newCommitteeID = mysqli_insert_id($data);
        echo json_encode(['success' => true, 'message' => 'Committee member registered successfully', 'committeeID' => $newCommitteeID]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to register committee member: ' . mysqli_error($data)]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);
?>