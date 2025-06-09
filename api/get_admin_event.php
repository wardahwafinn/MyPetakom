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

// Check if user is admin
if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - Admin only']);
    exit;
}

try {
    // Get parameters
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 15)));
    $offset = ($page - 1) * $limit;
    $searchTerm = trim($_GET['search'] ?? '');
    
    // Build search condition
    $searchCondition = "";
    $searchParams = [];
    if (!empty($searchTerm)) {
        $searchCondition = " WHERE (e.eventName LIKE ? OR e.eventID LIKE ?)";
        $searchPattern = "%" . $searchTerm . "%";
        $searchParams = [$searchPattern, $searchPattern];
    }
    
    // Get total count of events
    $countQuery = "SELECT COUNT(*) as total FROM event e" . $searchCondition;
    
    if (!empty($searchParams)) {
        $countStmt = $data->prepare($countQuery);
        $countStmt->bind_param("ss", ...$searchParams);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
    } else {
        $countResult = mysqli_query($data, $countQuery);
    }
    
    if (!$countResult) {
        throw new Exception("Count query failed: " . mysqli_error($data));
    }
    
    $totalEvents = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalEvents / $limit);
    
    // Get events with pagination
    $query = "SELECT 
                e.eventID,
                e.eventName,
                e.startdate,
                e.enddate,
                e.eventStatus,
                e.meritApplication,
                s.staffName as organizerName
              FROM event e
              LEFT JOIN staff s ON e.staffID = s.staffID
              " . $searchCondition . "
              ORDER BY e.startdate DESC, e.eventID DESC
              LIMIT ? OFFSET ?";
    
    $stmt = $data->prepare($query);
    
    if (!empty($searchParams)) {
        $params = array_merge($searchParams, [$limit, $offset]);
        $types = "ssii";
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param("ii", $limit, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Main query failed: " . mysqli_error($data));
    }
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Auto-update completed events based on end date
        $endDate = new DateTime($row['enddate']);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        // If event has passed end date and is still active or pending, mark as completed
        if ($endDate < $today && (intval($row['eventStatus']) == 1 || intval($row['eventStatus']) == 2)) {
            $updateQuery = "UPDATE event SET eventStatus = 3 WHERE eventID = ?";
            $updateStmt = $data->prepare($updateQuery);
            $updateStmt->bind_param("s", $row['eventID']);
            $updateStmt->execute();
            $row['eventStatus'] = 3; // Update the row data
        }
        
        // Ensure values are integers
        $row['eventStatus'] = (int)$row['eventStatus'];
        $row['meritApplication'] = (int)$row['meritApplication'];
        
        $events[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'events' => $events,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalEvents' => (int)$totalEvents,
        'searchTerm' => $_GET['search'] ?? ''
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'line' => $e->getLine()
        ]
    ]);
}

mysqli_close($data);
?>