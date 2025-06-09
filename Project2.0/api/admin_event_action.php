<?php
session_start();
header('Content-Type: application/json');

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

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

// Check if user is admin
if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - Admin only']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST method required']);
    exit;
}

// Get POST parameters
$action = trim($_POST['action'] ?? '');
$eventID = trim($_POST['eventID'] ?? '');

if (empty($action) || empty($eventID)) {
    echo json_encode(['success' => false, 'message' => 'Action and Event ID are required']);
    exit;
}

try {
    // First, get the current event details
    $checkQuery = "SELECT eventName, eventStatus, meritApplication FROM event WHERE eventID = ?";
    $checkStmt = $data->prepare($checkQuery);
    $checkStmt->bind_param("s", $eventID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }
    
    $event = $checkResult->fetch_assoc();
    $eventName = $event['eventName'];
    $currentStatus = (int)$event['eventStatus'];
    $currentMerit = (int)$event['meritApplication'];
    
    $updateQuery = "";
    $message = "";
    $newStatus = null;
    $newMerit = null;
    
    switch ($action) {
        case 'approve_merit':
            if ($currentMerit != 1) {
                echo json_encode(['success' => false, 'message' => 'Merit application is not pending approval']);
                exit;
            }
            $newMerit = 2; // Approved
            $newStatus = 1; // Active
            $updateQuery = "UPDATE event SET meritApplication = ?, eventStatus = ? WHERE eventID = ?";
            $message = "Merit approved successfully for event: " . $eventName;
            break;
            
        case 'postpone':
            if ($currentStatus != 1 && $currentStatus != 2) {
                echo json_encode(['success' => false, 'message' => 'Only active or pending events can be postponed']);
                exit;
            }
            $newStatus = 5; // Postponed
            $updateQuery = "UPDATE event SET eventStatus = ? WHERE eventID = ?";
            $message = "Event postponed successfully: " . $eventName;
            break;
            
        case 'cancel':
            if ($currentStatus != 1 && $currentStatus != 2) {
                echo json_encode(['success' => false, 'message' => 'Only active or pending events can be cancelled']);
                exit;
            }
            $newStatus = 4; // Cancelled
            $updateQuery = "UPDATE event SET eventStatus = ? WHERE eventID = ?";
            $message = "Event cancelled successfully: " . $eventName;
            break;
            
        case 'reactivate':
            if ($currentStatus != 4 && $currentStatus != 5) {
                echo json_encode(['success' => false, 'message' => 'Only cancelled or postponed events can be reactivated']);
                exit;
            }
            // Determine appropriate status based on merit
            $newStatus = ($currentMerit == 2) ? 1 : 2; // Active if merit approved, pending if not
            $updateQuery = "UPDATE event SET eventStatus = ? WHERE eventID = ?";
            $message = "Event reactivated successfully: " . $eventName;
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action specified: ' . $action]);
            exit;
    }
    
    // Execute the update
    $updateStmt = $data->prepare($updateQuery);
    
    if ($newMerit !== null) {
        // For approve_merit action - update both merit and status
        $updateStmt->bind_param("iis", $newMerit, $newStatus, $eventID);
    } else {
        // For other actions - update only status
        $updateStmt->bind_param("is", $newStatus, $eventID);
    }
    
    if ($updateStmt->execute()) {
        if ($updateStmt->affected_rows > 0) {
            // Optional: Log the admin action
            $adminID = $_SESSION['userID'];
            $actionLog = "Admin $adminID performed action '$action' on event $eventID ($eventName)";
            error_log($actionLog); // This will log to PHP error log
            
            echo json_encode([
                'success' => true, 
                'message' => $message,
                'eventID' => $eventID,
                'action' => $action
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes were made to the event']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update event: ' . $data->error]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'line' => $e->getLine(),
            'action' => $action ?? 'unknown',
            'eventID' => $eventID ?? 'unknown'
        ]
    ]);
}

mysqli_close($data);
?>