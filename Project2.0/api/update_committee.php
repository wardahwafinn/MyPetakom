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
    // Get form data
    $committeeID = intval($_POST['committeeID'] ?? 0);
    $newPosition = mysqli_real_escape_string($data, $_POST['newPosition'] ?? '');

    // Validate required fields
    if ($committeeID <= 0 || empty($newPosition)) {
        echo json_encode(['success' => false, 'message' => 'Committee ID and new position are required']);
        exit;
    }

    // Check if the committee member belongs to an event created by current staff
    $checkQuery = "SELECT ec.committeeID 
                   FROM eventcommittee ec
                   JOIN event e ON ec.eventID = e.eventID
                   WHERE ec.committeeID = ? AND e.staffID = ?";
    
    $checkStmt = $data->prepare($checkQuery);
    $checkStmt->bind_param("is", $committeeID, $staffID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Committee member not found or you do not have permission to edit this record']);
        exit;
    }

    // Update the committee member's position
    $updateQuery = "UPDATE eventcommittee SET committeePosition = ? WHERE committeeID = ?";
    $updateStmt = $data->prepare($updateQuery);
    $updateStmt->bind_param("si", $newPosition, $committeeID);
    
    if ($updateStmt->execute()) {
        if ($updateStmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Committee member position updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made - position may already be the same']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update committee member: ' . mysqli_error($data)]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);
?>