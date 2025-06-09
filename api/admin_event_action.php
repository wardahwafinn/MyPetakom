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

// Check if user is admin
if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - Admin only']);
    exit;
}

try {
    // Get form data
    $action = $_POST['action'] ?? '';
    $eventID = $_POST['eventID'] ?? '';
    
    if (empty($action) || empty($eventID)) {
        echo json_encode(['success' => false, 'message' => 'Action and Event ID are required']);
        exit;
    }
    
    // Verify event exists
    $checkQuery = "SELECT eventID, eventName, staffID, eventStatus, meritApplication FROM event WHERE eventID = ?";
    $checkStmt = $data->prepare($checkQuery);
    $checkStmt->bind_param("s", $eventID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }
    
    $eventData = $checkResult->fetch_assoc();
    $eventName = $eventData['eventName'];
    $organizerID = $eventData['staffID'];
    $currentStatus = (int)$eventData['eventStatus'];
    $currentMerit = (int)$eventData['meritApplication'];
    
    $adminID = $_SESSION['userID'];
    $message = '';
    
    switch ($action) {
        case 'approve_merit':
            // Only allow if merit is pending approval
            if ($currentMerit != 1) {
                echo json_encode(['success' => false, 'message' => 'This event does not have a pending merit application']);
                exit;
            }
            
            // Update merit to approved AND status to active
            $updateQuery = "UPDATE event SET meritApplication = 2, eventStatus = 1 WHERE eventID = ?";
            $updateStmt = $data->prepare($updateQuery);
            $updateStmt->bind_param("s", $eventID);
            
            if ($updateStmt->execute()) {
                $message = "Merit approved successfully! Event '$eventName' is now active.";
                logAdminAction($data, $adminID, $eventID, 'approve_merit', "Merit approved and event activated: $eventName");
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to approve merit application']);
                exit;
            }
            break;
            
        case 'postpone':
            // Only allow for active or pending events
            if ($currentStatus != 1 && $currentStatus != 2) {
                echo json_encode(['success' => false, 'message' => 'Only active or pending events can be postponed']);
                exit;
            }
            
            // Update event status to postponed (5)
            $updateQuery = "UPDATE event SET eventStatus = 5 WHERE eventID = ?";
            $updateStmt = $data->prepare($updateQuery);
            $updateStmt->bind_param("s", $eventID);
            
            if ($updateStmt->execute()) {
                $message = "Event postponed successfully: $eventName";
                logAdminAction($data, $adminID, $eventID, 'postpone', "Event postponed: $eventName");
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to postpone event']);
                exit;
            }
            break;
            
        case 'cancel':
            // Only allow for active or pending events
            if ($currentStatus != 1 && $currentStatus != 2) {
                echo json_encode(['success' => false, 'message' => 'Only active or pending events can be cancelled']);
                exit;
            }
            
            // Update event status to cancelled (4)
            $updateQuery = "UPDATE event SET eventStatus = 4 WHERE eventID = ?";
            $updateStmt = $data->prepare($updateQuery);
            $updateStmt->bind_param("s", $eventID);
            
            if ($updateStmt->execute()) {
                $message = "Event cancelled successfully: $eventName";
                logAdminAction($data, $adminID, $eventID, 'cancel', "Event cancelled: $eventName");
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to cancel event']);
                exit;
            }
            break;
            
        case 'reactivate':
            // Only allow for postponed or cancelled events
            if ($currentStatus != 4 && $currentStatus != 5) {
                echo json_encode(['success' => false, 'message' => 'Only cancelled or postponed events can be reactivated']);
                exit;
            }
            
            // Determine new status based on merit application
            $newStatus = 1; // Active by default
            if ($currentMerit == 1) {
                // If merit is still pending, set to pending status
                $newStatus = 2;
            }
            
            $updateQuery = "UPDATE event SET eventStatus = ? WHERE eventID = ?";
            $updateStmt = $data->prepare($updateQuery);
            $updateStmt->bind_param("is", $newStatus, $eventID);
            
            if ($updateStmt->execute()) {
                $statusText = ($newStatus == 2) ? 'pending (merit approval required)' : 'active';
                $message = "Event reactivated successfully: $eventName (Status: $statusText)";
                logAdminAction($data, $adminID, $eventID, 'reactivate', "Event reactivated: $eventName");
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to reactivate event']);
                exit;
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);

// Function to log admin actions (optional - for audit trail)
function logAdminAction($data, $adminID, $eventID, $action, $description) {
    try {
        // Create admin_actions table if it doesn't exist
        $createTableQuery = "CREATE TABLE IF NOT EXISTS admin_actions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            adminID VARCHAR(20) NOT NULL,
            eventID VARCHAR(10) NOT NULL,
            action VARCHAR(50) NOT NULL,
            description TEXT,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_admin (adminID),
            INDEX idx_event (eventID),
            INDEX idx_timestamp (timestamp)
        )";
        
        mysqli_query($data, $createTableQuery);
        
        $logQuery = "INSERT INTO admin_actions (adminID, eventID, action, description, timestamp) 
                     VALUES (?, ?, ?, ?, NOW())";
        
        $logStmt = $data->prepare($logQuery);
        if ($logStmt) {
            $logStmt->bind_param("ssss", $adminID, $eventID, $action, $description);
            $logStmt->execute();
        }
    } catch (Exception $e) {
        error_log("Failed to log admin action: " . $e->getMessage());
    }
}
?>