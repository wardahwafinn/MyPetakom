<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login/loginForm.php");
    exit;
}

// Check if user is student
if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'student') {
    header("Location: login/loginForm.php");
    exit;
}

// Database connection
$host = "localhost";
$user = "root";
$db_password = "";
$db = "mypetakom";

$data = mysqli_connect($host, $user, $db_password, $db);
if ($data === false) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get current student ID
$currentStudentID = $_SESSION['userID'];

// Get student name
$studentQuery = "SELECT studentName FROM student WHERE studentID = ?";
$studentStmt = $data->prepare($studentQuery);
$studentStmt->bind_param("s", $currentStudentID);
$studentStmt->execute();
$studentResult = $studentStmt->get_result();
$studentName = "STUDENT";
if ($studentResult->num_rows > 0) {
    $studentData = $studentResult->fetch_assoc();
    $studentName = strtoupper($studentData['studentName']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="description" content="MyPetakom Student Events">
    <meta name="author" content="Wardah Wafin">
    <title>MyPetakom - My Events</title>
    <link rel="stylesheet" href="style/student_dash.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        /* Additional styles for expanded details */
        .event-details-expanded {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #a90000;
        }

        .event-details-expanded.active {
            display: block;
        }

        .detail-row {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .detail-label {
            font-weight: bold;
            color: #333;
            min-width: 120px;
            margin-right: 15px;
        }

        .detail-value {
            color: #666;
            flex: 1;
            line-height: 1.5;
        }

        .role-display {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }

        .merit-display {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }

        .merit-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .merit-none {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .event-map {
            width: 100%;
            height: 300px;
            border-radius: 10px;
            margin-top: 15px;
            border: 1px solid #ddd;
        }

        /* Available Events Section */
        .no-events-section {
            margin-bottom: 30px;
        }

        .no-events-message {
            text-align: center;
            padding: 40px 20px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #ffc107;
        }

        .no-events-message h2 {
            color: #333;
            margin: 0 0 15px 0;
            font-size: 24px;
        }

        .no-events-message p {
            color: #666;
            margin: 0;
            font-size: 16px;
            line-height: 1.6;
        }

        .available-events-section {
            margin-top: 20px;
        }

        .available-events-section h2 {
            color: #333;
            font-size: 28px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #a90000;
        }

        .available-event-card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #007bff;
        }

        .available-event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .event-description {
            color: #666;
            margin: 10px 0;
            font-size: 14px;
            line-height: 1.5;
        }

        .advisor-info h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 16px;
        }

        .advisor-detail {
            margin: 8px 0;
            color: #555;
        }

        .advisor-detail strong {
            color: #333;
            margin-right: 10px;
        }

        .loading-message {
            text-align: center;
            padding: 60px 20px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .loading-message p {
            color: #666;
            margin: 0;
            font-size: 16px;
        }

        .error-message {
            text-align: center;
            padding: 60px 20px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #dc3545;
        }

        .error-message p {
            color: #dc3545;
            margin: 0;
            font-size: 16px;
        }
    </style>
</head>

<body class="background">
    <div class="sidebar">
        <a href="student_dash.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a>
        <hr>
        <a href="profile.php" class="nav-item">Profile</a>
        <hr>
        <a href="student_dash.php" class="nav-item">Apply<br>Membership</a>
        <hr>
        <a href="student_view_events.php" class="nav-item active">View Event</a>
    </div>

    <div class="top-right-bar">
        <a href="profile.php" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">
            HI, <?php echo $studentName; ?>
        </a>
        <a href="login/logout.php">
            <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
        </a>
    </div>

    <div class="main-content">
        <h1>My Events</h1>
        
        <div class="events-container" id="eventsContainer">
            <div class="loading-message">
                <p>Loading your events...</p>
            </div>
        </div>
    </div>

    <script>
        let mapInstances = {};

        document.addEventListener('DOMContentLoaded', function() {
            loadStudentEvents();
        });

        function loadStudentEvents() {
            fetch('api/get_student_events.php', {
                cache: 'no-cache',
                headers: {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('API Response:', data);
                if (data.success) {
                    if (data.events && data.events.length > 0) {
                        displayStudentEvents(data.events);
                    } else if (data.availableEvents) {
                        displayAvailableEvents(data.availableEvents);
                    } else {
                        document.getElementById('eventsContainer').innerHTML = 
                            '<div class="no-events"><p>No events available at this time.</p></div>';
                    }
                } else {
                    document.getElementById('eventsContainer').innerHTML = 
                        '<div class="error-message"><p>Error: ' + (data.message || 'Unknown error occurred') + '</p></div>';
                }
            })
            .catch(error => {
                console.error('Error loading events:', error);
                document.getElementById('eventsContainer').innerHTML = 
                    '<div class="error-message"><p>Error loading events. Please try again.</p></div>';
            });
        }

        function displayStudentEvents(events) {
            const container = document.getElementById('eventsContainer');
            
            if (events.length === 0) {
                container.innerHTML = '<div class="no-events"><p>No events assigned to you yet.</p></div>';
                return;
            }

            let html = '';
            events.forEach((event, index) => {
                const eventDate = formatEventDate(event.startdate);
                
                html += `
                    <div class="event-card">
                        <div class="event-header">
                            <div class="event-info">
                                <div class="event-title">${escapeHtml(event.eventName)}</div>
                                <div class="role-badge">${escapeHtml(event.committeePosition)}</div>
                            </div>
                            <div class="event-date">
                                <div class="month">${eventDate.month.toUpperCase()}</div>
                                <div class="day">${eventDate.day}</div>
                            </div>
                        </div>
                        
                        <button onclick="toggleEventDetails('${event.eventID}', ${index})" class="view-details-btn" id="btn-${index}">
                            View Details
                        </button>
                        
                        <div id="details-${index}" class="event-details-expanded">
                            <div class="detail-row">
                                <span class="detail-label">Role:</span>
                                <span class="detail-value role-display">${escapeHtml(event.committeePosition)}</span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Description:</span>
                                <span class="detail-value">${escapeHtml(event.description || 'No description available')}</span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Date:</span>
                                <span class="detail-value">${event.startdate === event.enddate ? formatFullDate(event.startdate) : formatFullDate(event.startdate) + ' - ' + formatFullDate(event.enddate)}</span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Location:</span>
                                <span class="detail-value">${escapeHtml(event.eventLocation)}</span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Event Level:</span>
                                <span class="detail-value">${escapeHtml(event.eventLevel)}</span>
                            </div>
                            
                            ${event.latitude && event.longitude ? `
                            <div class="detail-row">
                                <span class="detail-label">Geolocation:</span>
                                <span class="detail-value">Latitude: ${event.latitude}, Longitude: ${event.longitude}</span>
                            </div>
                            <div id="map-${index}" class="event-map"></div>
                            ` : ''}
                            
                            <div class="detail-row">
                                <span class="detail-label">Merit:</span>
                                <span class="detail-value">
                                    <span class="merit-display ${event.meritApplication == 1 ? 'merit-approved' : 'merit-none'}">
                                        ${event.meritApplication == 1 ? 'Approved by Coordinator' : 'No merit application'}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function displayAvailableEvents(availableEvents) {
            const container = document.getElementById('eventsContainer');
            
            let html = `
                <div class="no-events-section">
                    <div class="no-events-message">
                        <h2>No Events Registered</h2>
                        <p>You are not currently registered as a committee member for any events. Here are available events with advisor contact information.</p>
                    </div>
                </div>
            `;

            if (availableEvents.length > 0) {
                html += '<div class="available-events-section"><h2>Available Events</h2>';
                
                availableEvents.forEach((event, index) => {
                    const eventDate = formatEventDate(event.startdate);
                    
                    html += `
                        <div class="available-event-card">
                            <div class="event-header">
                                <div class="event-info">
                                    <div class="event-title">${escapeHtml(event.eventName)}</div>
                                    <p class="event-description">${escapeHtml(event.description || 'No description available')}</p>
                                    <div class="advisor-info">
                                        <h4>Event Advisor</h4>
                                        <div class="advisor-detail">
                                            <strong>Name:</strong> ${escapeHtml(event.advisorName)}
                                        </div>
                                        <div class="advisor-detail">
                                            <strong>Email:</strong> ${escapeHtml(event.advisorEmail)}
                                        </div>
                                    </div>
                                </div>
                                <div class="event-date">
                                    <div class="month">${eventDate.month.toUpperCase()}</div>
                                    <div class="day">${eventDate.day}</div>
                                </div>
                            </div>
                            
                            <button onclick="toggleAvailableEventDetails('${event.eventID}', ${index})" class="view-details-btn" id="available-btn-${index}">
                                View Details
                            </button>
                            
                            <div id="available-details-${index}" class="event-details-expanded">
                                <div class="detail-row">
                                    <span class="detail-label">Date:</span>
                                    <span class="detail-value">${event.startdate === event.enddate ? formatFullDate(event.startdate) : formatFullDate(event.startdate) + ' - ' + formatFullDate(event.enddate)}</span>
                                </div>
                                
                                <div class="detail-row">
                                    <span class="detail-label">Location:</span>
                                    <span class="detail-value">${escapeHtml(event.eventLocation)}</span>
                                </div>
                                
                                <div class="detail-row">
                                    <span class="detail-label">Event Level:</span>
                                    <span class="detail-value">${escapeHtml(event.eventLevel)}</span>
                                </div>
                                
                                ${event.latitude && event.longitude ? `
                                <div class="detail-row">
                                    <span class="detail-label">Geolocation:</span>
                                    <span class="detail-value">Latitude: ${event.latitude}, Longitude: ${event.longitude}</span>
                                </div>
                                <div id="available-map-${index}" class="event-map"></div>
                                ` : ''}
                                
                                <div class="detail-row">
                                    <span class="detail-label">Merit:</span>
                                    <span class="detail-value">
                                        <span class="merit-display ${event.meritApplication == 1 ? 'merit-approved' : 'merit-none'}">
                                            ${event.meritApplication == 1 ? 'Merit will be approved' : 'No merit application'}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
            } else {
                html += '<div class="no-events"><p>No events are currently available for registration.</p></div>';
            }
            
            container.innerHTML = html;
        }

        function toggleEventDetails(eventID, index) {
            const detailsDiv = document.getElementById(`details-${index}`);
            const button = document.getElementById(`btn-${index}`);
            const mapId = `map-${index}`;
            
            if (detailsDiv.classList.contains('active')) {
                detailsDiv.classList.remove('active');
                button.textContent = 'View Details';
                
                // Destroy map instance if exists
                if (mapInstances[mapId]) {
                    mapInstances[mapId].remove();
                    delete mapInstances[mapId];
                }
            } else {
                detailsDiv.classList.add('active');
                button.textContent = 'Hide Details';
                
                // Initialize map if coordinates exist
                setTimeout(() => {
                    const mapElement = document.getElementById(mapId);
                    if (mapElement) {
                        initializeEventMap(eventID, mapId, index);
                    }
                }, 100);
            }
        }

        function toggleAvailableEventDetails(eventID, index) {
            const detailsDiv = document.getElementById(`available-details-${index}`);
            const button = document.getElementById(`available-btn-${index}`);
            const mapId = `available-map-${index}`;
            
            if (detailsDiv.classList.contains('active')) {
                detailsDiv.classList.remove('active');
                button.textContent = 'View Details';
                
                // Destroy map instance if exists
                if (mapInstances[mapId]) {
                    mapInstances[mapId].remove();
                    delete mapInstances[mapId];
                }
            } else {
                detailsDiv.classList.add('active');
                button.textContent = 'Hide Details';
                
                // Initialize map if coordinates exist
                setTimeout(() => {
                    const mapElement = document.getElementById(mapId);
                    if (mapElement) {
                        initializeAvailableEventMap(eventID, mapId, index);
                    }
                }, 100);
            }
        }

        function initializeEventMap(eventID, mapId, index) {
            fetch(`api/get_student_event_details.php?eventID=${encodeURIComponent(eventID)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.event.latitude && data.event.longitude) {
                        const lat = parseFloat(data.event.latitude);
                        const lng = parseFloat(data.event.longitude);
                        
                        // Initialize map
                        const map = L.map(mapId).setView([lat, lng], 16);
                        
                        // Add tile layer
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors',
                            maxZoom: 19
                        }).addTo(map);
                        
                        // Add marker
                        const marker = L.marker([lat, lng]).addTo(map);
                        marker.bindPopup(`
                            <b>${escapeHtml(data.event.eventName)}</b><br>
                            ${escapeHtml(data.event.eventLocation)}<br>
                            Lat: ${lat}, Lng: ${lng}
                        `).openPopup();
                        
                        // Store map instance
                        mapInstances[mapId] = map;
                        
                        // Force map resize after a short delay
                        setTimeout(() => {
                            map.invalidateSize();
                        }, 250);
                    }
                })
                .catch(error => {
                    console.error('Error loading event details for map:', error);
                });
        }

        function initializeAvailableEventMap(eventID, mapId, index) {
            fetch(`api/get_available_event.php?id=${encodeURIComponent(eventID)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.event.latitude && data.event.longitude) {
                        const lat = parseFloat(data.event.latitude);
                        const lng = parseFloat(data.event.longitude);
                        
                        // Initialize map
                        const map = L.map(mapId).setView([lat, lng], 16);
                        
                        // Add tile layer
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors',
                            maxZoom: 19
                        }).addTo(map);
                        
                        // Add marker
                        const marker = L.marker([lat, lng]).addTo(map);
                        marker.bindPopup(`
                            <b>${escapeHtml(data.event.eventName)}</b><br>
                            ${escapeHtml(data.event.eventLocation)}<br>
                            Lat: ${lat}, Lng: ${lng}
                        `).openPopup();
                        
                        // Store map instance
                        mapInstances[mapId] = map;
                        
                        // Force map resize after a short delay
                        setTimeout(() => {
                            map.invalidateSize();
                        }, 250);
                    }
                })
                .catch(error => {
                    console.error('Error loading available event details for map:', error);
                });
        }

        function formatEventDate(dateString) {
            const date = new Date(dateString);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                           'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            return {
                month: months[date.getMonth()],
                day: date.getDate()
            };
        }

        function formatFullDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>