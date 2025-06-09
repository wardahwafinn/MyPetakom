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

$staffID = trim($_SESSION['userID']);

try {
    // Get JSON data
    $input = json_decode(file_get_contents('php://input'), true);
    $eventID = $input['eventID'] ?? '';
    
    if (empty($eventID)) {
        echo json_encode(['success' => false, 'message' => 'Event ID is required']);
        exit;
    }
    
    // Check if the event belongs to the current staff
    $checkQuery = "SELECT eventID, approvalLetter FROM event WHERE eventID = ? AND staffID = ?";
    $checkStmt = $data->prepare($checkQuery);
    $checkStmt->bind_param("ss", $eventID, $staffID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Event not found or you do not have permission to delete this event']);
        exit;
    }
    
    $eventData = $checkResult->fetch_assoc();
    $approvalLetter = $eventData['approvalLetter'];
    
    // Start transaction
    mysqli_autocommit($data, false);
    
    try {
        // Delete related committee members first (if table exists)
        $deleteCommitteeQuery = "DELETE FROM eventcommittee WHERE eventID = ?";
        $deleteCommitteeStmt = $data->prepare($deleteCommitteeQuery);
        if ($deleteCommitteeStmt) {
            $deleteCommitteeStmt->bind_param("s", $eventID);
            $deleteCommitteeStmt->execute();
        }
        
        // Delete the event
        $deleteEventQuery = "DELETE FROM event WHERE eventID = ? AND staffID = ?";
        $deleteEventStmt = $data->prepare($deleteEventQuery);
        $deleteEventStmt->bind_param("ss", $eventID, $staffID);
        $deleteEventStmt->execute();
        
        if ($deleteEventStmt->affected_rows > 0) {
            // Delete approval letter file if it exists
            if (!empty($approvalLetter)) {
                $filePath = "uploads/" . $approvalLetter;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            mysqli_commit($data);
            echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
        } else {
            mysqli_rollback($data);
            echo json_encode(['success' => false, 'message' => 'Failed to delete event - no matching record found']);
        }
    } catch (Exception $e) {
        mysqli_rollback($data);
        throw $e;
    } finally {
        mysqli_autocommit($data, true);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);
?>