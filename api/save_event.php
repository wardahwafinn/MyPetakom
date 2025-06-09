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
    // Get form data
    $eventID = trim($_POST['eventID'] ?? '');
    $eventName = trim($_POST['eventName'] ?? '');
    $eventDescription = trim($_POST['eventDescription'] ?? '');
    $startDate = $_POST['startDate'] ?? '';
    $endDate = $_POST['endDate'] ?? '';
    $eventLevel = $_POST['eventLevel'] ?? '';
    $locationName = trim($_POST['locationName'] ?? '');
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    
    // Merit application logic - check if checkbox is checked
    $applyMerit = isset($_POST['applyMerit']) && $_POST['applyMerit'] === 'on';
    
    // Determine initial status based on merit application
    if ($applyMerit) {
        $eventStatus = 2; // Pending (waiting for admin approval)
        $meritApplication = 1; // Pending approval
        $statusMessage = 'Event created and submitted for merit approval. Status: Pending.';
    } else {
        $eventStatus = 1; // Active (no merit needed)
        $meritApplication = 0; // Not applied
        $statusMessage = 'Event created successfully. Status: Active.';
    }
    
    // Validate required fields
    if (empty($eventID) || empty($eventName) || empty($startDate) || empty($endDate) || 
        empty($eventLevel) || empty($locationName) || empty($latitude) || empty($longitude)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }
    
    // Validate dates
    $startDateTime = new DateTime($startDate);
    $endDateTime = new DateTime($endDate);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($endDateTime < $startDateTime) {
        echo json_encode(['success' => false, 'message' => 'End date cannot be before start date']);
        exit;
    }
    
    // Handle file upload
    $approvalLetterName = null;
    if (isset($_FILES['approvalLetter']) && $_FILES['approvalLetter']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileInfo = pathinfo($_FILES['approvalLetter']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if ($extension !== 'pdf') {
            echo json_encode(['success' => false, 'message' => 'Only PDF files are allowed']);
            exit;
        }
        
        if ($_FILES['approvalLetter']['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
            exit;
        }
        
        $approvalLetterName = $eventID . '_approval_' . time() . '.pdf';
        $uploadPath = $uploadDir . $approvalLetterName;
        
        if (!move_uploaded_file($_FILES['approvalLetter']['tmp_name'], $uploadPath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            exit;
        }
    }
    
    // Check if event ID already exists (for editing)
    $checkQuery = "SELECT eventID, eventStatus, meritApplication FROM event WHERE eventID = ?";
    $checkStmt = $data->prepare($checkQuery);
    $checkStmt->bind_param("s", $eventID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // EDITING EXISTING EVENT
        $existingEvent = $checkResult->fetch_assoc();
        
        // Preserve admin decisions when editing
        if ($existingEvent['eventStatus'] == 4 || $existingEvent['eventStatus'] == 5) {
            // Keep cancelled/postponed status if admin set it
            $eventStatus = $existingEvent['eventStatus'];
            $meritApplication = $existingEvent['meritApplication'];
        } elseif ($existingEvent['meritApplication'] == 2) {
            // Keep approved merit status
            $eventStatus = 1; // Active
            $meritApplication = 2; // Approved
        } elseif ($existingEvent['eventStatus'] == 3) {
            // Keep completed status
            $eventStatus = 3; // Completed
            $meritApplication = $existingEvent['meritApplication'];
        } else {
            // Apply new logic for other cases
            if ($applyMerit && $existingEvent['meritApplication'] != 2) {
                $eventStatus = 2; // Pending
                $meritApplication = 1; // Pending approval
            } elseif (!$applyMerit) {
                $eventStatus = 1; // Active
                $meritApplication = 0; // Not applied
            }
        }
        
        $updateQuery = "UPDATE event SET 
                        eventName = ?,
                        description = ?,
                        startdate = ?,
                        enddate = ?,
                        eventLevel = ?,
                        eventLocation = ?,
                        latitude = ?,
                        longitude = ?,
                        eventStatus = ?,
                        meritApplication = ?";
        
        $params = [$eventName, $eventDescription, $startDate, $endDate, $eventLevel, 
                  $locationName, $latitude, $longitude, $eventStatus, $meritApplication];
        $types = "ssssssssii";
        
        if ($approvalLetterName) {
            $updateQuery .= ", approvalLetter = ?";
            $params[] = $approvalLetterName;
            $types .= "s";
        }
        
        $updateQuery .= " WHERE eventID = ? AND staffID = ?";
        $params[] = $eventID;
        $params[] = $staffID;
        $types .= "ss";
        
        $stmt = $data->prepare($updateQuery);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update event or you do not have permission']);
        }
    } else {
        // CREATING NEW EVENT
        $insertQuery = "INSERT INTO event (
                        eventID, eventName, description, startdate, enddate, 
                        eventLevel, eventLocation, latitude, longitude, 
                        eventStatus, meritApplication, approvalLetter, staffID
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $data->prepare($insertQuery);
        $stmt->bind_param("sssssssssiiis", 
            $eventID, $eventName, $eventDescription, $startDate, $endDate,
            $eventLevel, $locationName, $latitude, $longitude,
            $eventStatus, $meritApplication, $approvalLetterName, $staffID
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => $statusMessage]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create event: ' . $stmt->error]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);
?>