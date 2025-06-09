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

$staffID = trim($_SESSION['userID']);

try {
    // Check if this is a single event request (for editing) or list request
    if (isset($_GET['id'])) {
        // Single event request for editing
        $eventID = $_GET['id'];
        
        if (empty($eventID)) {
            echo json_encode(['success' => false, 'message' => 'Event ID is required']);
            exit;
        }
        
        // Get event data - only events belonging to current staff
        $sql = "SELECT 
                    eventID,
                    eventName,
                    description,
                    startdate,
                    enddate,
                    eventLevel,
                    eventLocation,
                    latitude,
                    longitude,
                    eventStatus,
                    approvalLetter,
                    meritApplication,
                    qrCodePath,
                    qrCodeUrl
                FROM event 
                WHERE eventID = ? AND staffID = ?";

        $stmt = $data->prepare($sql);
        $stmt->bind_param("ss", $eventID, $staffID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $event = $result->fetch_assoc();
        
        if ($event) {
            // Ensure integer values for status fields
            $event['eventStatus'] = (int)$event['eventStatus'];
            $event['meritApplication'] = (int)$event['meritApplication'];
            
            echo json_encode(['success' => true, 'event' => $event]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Event not found or you do not have permission to access this event']);
        }
    } else {
        // Event list request with pagination and search
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;
        $searchTerm = trim($_GET['search'] ?? '');
        
        // Build the WHERE clause
        $whereClause = "WHERE staffID = ?";
        $params = [$staffID];
        $types = "s";
        
        // Add search conditions if search term is provided
        if (!empty($searchTerm)) {
            $whereClause .= " AND (eventName LIKE ? OR eventID LIKE ?)";
            $searchPattern = "%" . $searchTerm . "%";
            $params[] = $searchPattern;
            $params[] = $searchPattern;
            $types .= "ss";
        }
        
        // Get total count of events for this staff (with search filter)
        $countQuery = "SELECT COUNT(*) as total FROM event " . $whereClause;
        $countStmt = $data->prepare($countQuery);
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalEvents = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalEvents / $limit);
        
        // Get events for this staff with pagination and search
        $query = "SELECT 
                    eventID,
                    eventName,
                    description,
                    startdate,
                    enddate,
                    eventLevel,
                    eventLocation,
                    latitude,
                    longitude,
                    eventStatus,
                    approvalLetter,
                    meritApplication,
                    qrCodePath,
                    qrCodeUrl
                  FROM event 
                  " . $whereClause . "
                  ORDER BY startdate DESC, eventID DESC
                  LIMIT ? OFFSET ?";
        
        // Add limit and offset parameters
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $data->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            // Auto-update completed events based on end date
            $endDate = new DateTime($row['enddate']);
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            
            // If event has passed end date and is still active or pending, mark as completed
            $currentStatus = (int)$row['eventStatus'];
            if ($endDate < $today && ($currentStatus == 1 || $currentStatus == 2)) {
                $updateQuery = "UPDATE event SET eventStatus = 3 WHERE eventID = ?";
                $updateStmt = $data->prepare($updateQuery);
                $updateStmt->bind_param("s", $row['eventID']);
                $updateStmt->execute();
                $row['eventStatus'] = 3; // Update the row data
            }
            
            // Ensure integer values for status fields
            $row['eventStatus'] = (int)$row['eventStatus'];
            $row['meritApplication'] = (int)$row['meritApplication'];
            
            $events[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'events' => $events,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalEvents' => $totalEvents,
            'searchTerm' => $searchTerm
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);
?>