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
<html>
<head>
    <meta name="description" content="MyPetakom Event Information">
    <meta name="author" content="Wardah Wafin">
    <title><?php echo $event ? htmlspecialchars($event['eventName']) . ' - ' : ''; ?>MyPetakom Event Info</title>
    <link rel="icon" type="image/png" href="images/petakom.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            color: #333;
        }

        .content {
            padding: 30px;
        }

        h1 {
            color: #a90000 !important;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #a90000;
            padding-bottom: 10px;
            font-weight: bold;
            font-size: 24px;
        }

        h2 {
            color: #555;
            margin-top: 25px;
            margin-bottom: 10px;
        }

        .event-info {
            margin-bottom: 15px;
        }

        .event-info strong {
            display: inline-block;
            width: 120px;
            color: #666;
        }

        .description {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 3px solid #a90000;
            border-radius: 5px;
        }

        .maps-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .maps-link:hover {
            text-decoration: underline;
        }

        .footer {
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 0;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .container {
                border-radius: 15px;
            }
            
            .content {
                padding: 20px;
            }
            
            .event-info strong {
                width: 100px;
            }
            
            .footer {
                padding: 15px 20px;
            }

            h1 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="content">
            <?php if ($error): ?>
                <h1>Error</h1>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php else: ?>
                <h1><?php echo htmlspecialchars($event['eventName']); ?></h1>

                <?php if ($event['description']): ?>
                    <h2>Description</h2>
                    <div class="description">
                        <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                    </div>
                <?php endif; ?>

                <div class="event-info">
                    <strong>Level:</strong> <?php echo ucfirst(htmlspecialchars($event['eventLevel'])); ?>
                </div>

                <div class="event-info">
                    <strong>Start Date:</strong> <?php echo date('F d, Y', strtotime($event['startdate'])); ?>
                </div>

                <div class="event-info">
                    <strong>End Date:</strong> <?php echo date('F d, Y', strtotime($event['enddate'])); ?>
                </div>

                <div class="event-info">
                    <strong>Advisor:</strong> <?php echo htmlspecialchars($event['organizerName'] ?? 'Event Organizer'); ?>
                </div>

                <?php if ($event['eventLocation']): ?>
                    <h2>Location Details</h2>
                    <div class="event-info">
                        <strong>Location:</strong> <?php echo htmlspecialchars($event['eventLocation']); ?>
                    </div>
                    
                    <?php if ($event['latitude'] && $event['longitude']): ?>
                        <div class="event-info">
                            <a href="https://www.google.com/maps?q=<?php echo $event['latitude']; ?>,<?php echo $event['longitude']; ?>" target="_blank" class="maps-link">📍 View on Google Maps</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p><strong>&copy; <?php echo date('Y'); ?> MyPetakom</strong> - Event Management System</p>
            <p>Accessed on <?php echo date('F d, Y \a\t g:i A'); ?></p>
        </div>
    </div>
</body>
</html>