<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Check if user is logged in (for manual generation, but allow auto-generation)
$requireAuth = !isset($_GET['auto']) || $_GET['auto'] !== 'true';

if ($requireAuth) {
    if (!isset($_SESSION['userID'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    if (!isset($_SESSION['userType']) || ($_SESSION['userType'] != 'advisor' && $_SESSION['userType'] != 'admin')) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    $staffID = $_SESSION['userID'];
} else {
    $staffID = trim($_GET['staffID'] ?? '');
}

try {
    $eventID = trim($_GET['eventID'] ?? '');

    if (empty($eventID)) {
        echo json_encode(['success' => false, 'message' => 'Event ID is required']);
        exit;
    }

    // Get event details
    $eventQuery = "SELECT eventID, eventName, qrCodePath, qrCodeUrl, staffID FROM event WHERE eventID = ?";
    if ($requireAuth) {
        $eventQuery .= " AND staffID = ?";
        $eventStmt = $data->prepare($eventQuery);
        $eventStmt->bind_param("ss", $eventID, $staffID);
    } else {
        $eventStmt = $data->prepare($eventQuery);
        $eventStmt->bind_param("s", $eventID);
    }

    $eventStmt->execute();
    $eventResult = $eventStmt->get_result();

    if ($eventResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Event not found or no permission']);
        exit;
    }

    $event = $eventResult->fetch_assoc();

    // Check if QR code already exists and file is valid
    if (!empty($event['qrCodePath']) && !empty($event['qrCodeUrl'])) {
        $fullQrPath = __DIR__ . '/../' . $event['qrCodePath']; // Go up one directory from api folder
        
        if (file_exists($fullQrPath)) {
            $baseUrl = getBaseUrl();
            echo json_encode([
                'success' => true,
                'eventID' => $eventID,
                'eventName' => $event['eventName'],
                'qrUrl' => $event['qrCodeUrl'],
                'qrCodePath' => $event['qrCodePath'],
                'qrImageUrl' => getQRImageUrl($event['qrCodeUrl']),
                'isExisting' => true,
                'message' => 'Using existing QR code'
            ]);
            exit;
        }
    }

    // Generate new QR code
    $baseUrl = getBaseUrl();
    $qrUrl = $baseUrl . '/event_info.php?id=' . urlencode($eventID);
    $qrCodeFilename = 'qr_' . $eventID . '_' . time() . '.png';
    $qrCodePath = 'qrcodes/' . $qrCodeFilename;
    
    // Create full path - go up one directory from api folder
    $qrCodesDir = __DIR__ . '/../qrcodes';
    $fullQrPath = $qrCodesDir . '/' . $qrCodeFilename;

    // Create qrcodes directory if it doesn't exist
    if (!is_dir($qrCodesDir)) {
        if (!mkdir($qrCodesDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Failed to create qrcodes directory']);
            exit;
        }
    }

    // Generate QR code image
    if (!generateQRCodeImage($qrUrl, $fullQrPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to generate QR code image']);
        exit;
    }

    // Update database with QR code information
    $updateQuery = "UPDATE event SET qrCodePath = ?, qrCodeUrl = ? WHERE eventID = ?";
    $updateStmt = $data->prepare($updateQuery);
    $updateStmt->bind_param("sss", $qrCodePath, $qrUrl, $eventID);

    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true,
            'eventID' => $eventID,
            'eventName' => $event['eventName'],
            'qrUrl' => $qrUrl,
            'qrCodePath' => $qrCodePath,
            'qrImageUrl' => getQRImageUrl($qrUrl),
            'qrCodeFilename' => $qrCodeFilename,
            'downloadUrl' => $baseUrl . '/' . $qrCodePath,
            'isExisting' => false,
            'message' => 'New QR code generated successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save QR code info to database: ' . $data->error]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($data);

// Helper functions
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // Remove /api from the path if present
    $basePath = rtrim(preg_replace('/\/api$/', '', $scriptDir), '/');
    
    return $protocol . '://' . $host . $basePath;
}

function getQRImageUrl($qrUrl) {
    return "https://api.qrserver.com/v1/create-qr-code/?size=300x300&format=png&margin=10&data=" . urlencode($qrUrl);
}

function generateQRCodeImage($data, $filepath) {
    try {
        $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&format=png&margin=10&data=" . urlencode($data);
        
        // Create context with timeout and user agent
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (compatible; QR Generator)'
            ]
        ]);
        
        $imageData = @file_get_contents($qrImageUrl, false, $context);
        
        if ($imageData === false) {
            error_log("Failed to fetch QR image from: " . $qrImageUrl);
            return false;
        }
        
        $result = file_put_contents($filepath, $imageData);
        
        if ($result === false) {
            error_log("Failed to save QR image to: " . $filepath);
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("QR generation error: " . $e->getMessage());
        return false;
    }
}
?>