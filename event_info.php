<?php
// This is a PUBLIC page - no login required for QR code scanning

// Database configuration
$host = "localhost";
$user = "root";
$db_password = "";
$db = "mypetakom";

// Connect to the database
$data = mysqli_connect($host, $user, $db_password, $db);
if ($data === false) {
    die("Connection failed: " . mysqli_connect_error());
}

$eventID = $_GET['id'] ?? '';
$event = null;
$error = null;

if (empty($eventID)) {
    $error = "Event ID is required";
} else {
    // Get event details (public information only)
    // Allow access to all events except cancelled (4) - for QR code access
    $query = "SELECT 
                e.eventID,
                e.eventName,
                e.description,
                e.startdate,
                e.enddate,
                e.eventLevel,
                e.eventLocation,
                e.latitude,
                e.longitude,
                e.eventStatus,
                e.meritApplication,
                s.staffName as organizerName
              FROM event e
              LEFT JOIN staff s ON e.staffID = s.staffID
              WHERE e.eventID = ? AND e.eventStatus != 4";
    
    $stmt = $data->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $eventID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $event = $result->fetch_assoc();
        } else {
            $error = "Event not found or has been cancelled";
        }
    } else {
        $error = "Database query error: " . $data->error;
    }
}

mysqli_close($data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MyPetakom Event Information">
    <meta name="author" content="Wardah Wafin">
    <title><?php echo $event ? htmlspecialchars($event['eventName']) . ' - ' : ''; ?>MyPetakom Event Info</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="icon" type="image/png" href="images/petakom.png">
    
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }

        /* Custom Bootstrap overrides */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: none;
            overflow: hidden;
        }

        /* Custom brand colors */
        .text-primary {
            color: #a90000 !important;
        }

        .border-primary {
            border-color: #a90000 !important;
        }

        .btn-primary {
            background-color: #a90000;
            border-color: #a90000;
        }

        .btn-primary:hover {
            background-color: #8b0000;
            border-color: #8b0000;
        }

        .event-header {
            background: linear-gradient(135deg, #a90000 0%, #8b0000 100%);
            color: white;
            border-bottom: 2px solid #8b0000;
        }

        .info-item {
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 12px;
        }

        .info-item:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
        }

        .description-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #a90000;
        }

        .maps-link {
            transition: all 0.3s ease;
        }

        .maps-link:hover {
            transform: scale(1.05);
        }

        .footer-brand {
            background: linear-gradient(135deg, #343a40 0%, #495057 100%);
            color: white;
        }

        .error-card {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border-left: 4px solid #dc3545;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .card {
                border-radius: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-6">
                <div class="card">
                    <?php if ($error): ?>
                        <!-- Error State -->
                        <div class="card-header text-center py-4 bg-danger text-white">
                            <h1 class="h3 mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>Error
                            </h1>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="error-card p-4 rounded mb-4">
                                <i class="bi bi-x-circle display-4 text-danger mb-3"></i>
                                <p class="fs-5 mb-0"><?php echo htmlspecialchars($error); ?></p>
                            </div>
                            <a href="#" onclick="history.back()" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Go Back
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Event Information -->
                        <div class="event-header text-center py-4">
                            <h1 class="h3 mb-0">
                                <i class="bi bi-calendar-event me-2"></i>
                                <?php echo htmlspecialchars($event['eventName']); ?>
                            </h1>
                        </div>

                        <div class="card-body p-4">
                            <?php if ($event['description']): ?>
                                <div class="description-card p-4 rounded mb-4">
                                    <h5 class="text-primary mb-3">
                                        <i class="bi bi-info-circle me-2"></i>Description
                                    </h5>
                                    <p class="mb-0 lh-lg"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <!-- Event Details -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-award text-primary me-3 fs-5"></i>
                                            <div>
                                                <small class="text-muted d-block">Level</small>
                                                <strong><?php echo ucfirst(htmlspecialchars($event['eventLevel'])); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-badge text-primary me-3 fs-5"></i>
                                            <div>
                                                <small class="text-muted d-block">Advisor</small>
                                                <strong><?php echo htmlspecialchars($event['organizerName'] ?? 'Event Organizer'); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar-check text-primary me-3 fs-5"></i>
                                            <div>
                                                <small class="text-muted d-block">Start Date</small>
                                                <strong><?php echo date('F d, Y', strtotime($event['startdate'])); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar-x text-primary me-3 fs-5"></i>
                                            <div>
                                                <small class="text-muted d-block">End Date</small>
                                                <strong><?php echo date('F d, Y', strtotime($event['enddate'])); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($event['eventLocation']): ?>
                                <hr class="my-4">
                                <h5 class="text-primary mb-3">
                                    <i class="bi bi-geo-alt me-2"></i>Location Details
                                </h5>
                                
                                <div class="info-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-building text-primary me-3 fs-5 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Venue</small>
                                            <strong><?php echo htmlspecialchars($event['eventLocation']); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($event['latitude'] && $event['longitude']): ?>
                                    <div class="text-center">
                                        <a href="https://www.google.com/maps?q=<?php echo $event['latitude']; ?>,<?php echo $event['longitude']; ?>" 
                                           target="_blank" 
                                           class="btn btn-primary maps-link">
                                            <i class="bi bi-geo-alt-fill me-2"></i>View on Google Maps
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Footer -->
                        <div class="footer-brand text-center py-4">
                            <div class="mb-2">
                                <strong>
                                    <i class="bi bi-c-circle me-1"></i>
                                    <?php echo date('Y'); ?> MyPetakom
                                </strong>
                            </div>
                            <small class="opacity-75">
                                <i class="bi bi-clock me-1"></i>
                                Accessed on <?php echo date('F d, Y \a\t g:i A'); ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>