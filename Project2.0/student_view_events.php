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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MyPetakom Student Events">
    <meta name="author" content="Wardah Wafin">
    <title>MyPetakom - My Events</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="icon" type="image/png" href="images/petakom.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-image: url("images/bg.png");
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* Original Sidebar Design */
        .sidebar {
            width: 210px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            box-sizing: border-box;
            z-index: 1000;
        }

        .logo {
            display: block;
            width: 125px;
            height: 125px;
            margin: 0 auto 15px;
        }

        hr {
            border: 0;
            height: 1px;
            background-color: white;
            margin: 10px 0;
        }

        .nav-item {
            display: block;
            color: #333;
            text-decoration: none;
            padding: 10px 80px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-item.active {
            background-color: rgba(217, 217, 217, 0.40);
        }

        /* Main content with sidebar offset */
        .main-content {
            margin-left: 230px;
            margin-right: 20px;
            padding: 20px;
            min-height: 100vh;
        }

        /* Top right bar */
        .top-right-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 15px;
            z-index: 1000;
        }

        .profilename {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border: 2px solid #4a4a4a;
            border-radius: 6px;
            text-decoration: none;
            color: #4a4a4a;
            font-weight: bold;
            background-color: rgba(255, 255, 255, 0.9);
            transition: background-color 0.2s;
        }

        .profile-icon, .logout-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }

        /* Custom Bootstrap overrides */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
        }

        .btn-primary {
            background-color: #a90000;
            border-color: #a90000;
        }

        .btn-primary:hover {
            background-color: #8b0000;
            border-color: #8b0000;
        }

        .text-primary {
            color: #a90000 !important;
        }

        /* Event card styling */
        .event-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #a90000;
        }

        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .event-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .role-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            display: inline-block;
        }

        .event-date {
            text-align: center;
            background: linear-gradient(135deg, #a90000, #d32f2f);
            color: white;
            border-radius: 15px;
            padding: 15px;
            min-width: 80px;
        }

        .month {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .day {
            font-size: 28px;
            font-weight: bold;
        }

        /* Merit badges */
        .merit-display {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            font-size: 14px;
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

        /* Map styling */
        .event-map {
            width: 100%;
            height: 300px;
            border-radius: 10px;
            margin-top: 15px;
            border: 1px solid #ddd;
        }

        /* Alert styling */
        .alert-section {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border-left: 4px solid #ffc107;
            border-radius: 10px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                margin-right: 0;
                padding: 10px;
            }

            .event-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .event-date {
                align-self: flex-end;
            }
        }
    </style>
</head>

<body>
    <!-- Original Sidebar -->
    <div class="sidebar">
        <a href="student_dash.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a>
        <hr>
        <a href="profile.php" class="nav-item">Profile</a>
        <hr>
        <a href="student_dash.php" class="nav-item">Apply<br>Membership</a>
        <hr>
        <a href="student_view_events.php" class="nav-item active">View Event</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <h1 class="display-6 text-primary mb-0">
                                <i class="bi bi-calendar-event me-3"></i>My Events
                            </h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Events Container -->
            <div class="row">
                <div class="col-12">
                    <div id="eventsContainer">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading your events...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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
                        displayNoEvents();
                    }
                } else {
                    displayError(data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.error('Error loading events:', error);
                displayError('Error loading events. Please try again.');
            });
        }

        function displayStudentEvents(events) {
            const container = document.getElementById('eventsContainer');
            
            if (events.length === 0) {
                displayNoEvents();
                return;
            }

            let html = '';
            events.forEach((event, index) => {
                const eventDate = formatEventDate(event.startdate);
                
                html += `
                    <div class="event-card" data-event-id="${event.eventID}">
                        <div class="event-header">
                            <div class="event-info">
                                <div class="event-title">${escapeHtml(event.eventName)}</div>
                                <span class="role-badge">${escapeHtml(event.committeePosition)}</span>
                            </div>
                            <div class="event-date">
                                <div class="month">${eventDate.month.toUpperCase()}</div>
                                <div class="day">${eventDate.day}</div>
                            </div>
                        </div>
                        
                        <button onclick="toggleEventDetails('${event.eventID}', ${index})" 
                                class="btn btn-primary" id="btn-${index}">
                            <i class="bi bi-eye me-2"></i>View Details
                        </button>
                        
                        <div id="details-${index}" class="collapse mt-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bi bi-award text-primary me-3 fs-5"></i>
                                                <div>
                                                    <small class="text-muted d-block">Role</small>
                                                    <span class="role-badge">${escapeHtml(event.committeePosition)}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bi bi-calendar-check text-primary me-3 fs-5"></i>
                                                <div>
                                                    <small class="text-muted d-block">Date</small>
                                                    <strong>${event.startdate === event.enddate ? formatFullDate(event.startdate) : formatFullDate(event.startdate) + ' - ' + formatFullDate(event.enddate)}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="d-flex align-items-start mb-3">
                                                <i class="bi bi-info-circle text-primary me-3 fs-5 mt-1"></i>
                                                <div>
                                                    <small class="text-muted d-block">Description</small>
                                                    <p class="mb-0">${escapeHtml(event.description || 'No description available')}</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bi bi-geo-alt text-primary me-3 fs-5"></i>
                                                <div>
                                                    <small class="text-muted d-block">Location</small>
                                                    <strong>${escapeHtml(event.eventLocation)}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bi bi-flag text-primary me-3 fs-5"></i>
                                                <div>
                                                    <small class="text-muted d-block">Event Level</small>
                                                    <strong>${escapeHtml(event.eventLevel)}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bi bi-star text-primary me-3 fs-5"></i>
                                                <div>
                                                    <small class="text-muted d-block">Merit Status</small>
                                                    <span class="merit-display ${event.meritApplication == 1 ? 'merit-approved' : 'merit-none'}">
                                                        ${event.meritApplication == 1 ? 'Approved by Coordinator' : 'No merit application'}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        ${event.latitude && event.longitude ? `
                                        <div class="col-12">
                                            <div id="map-${index}" class="event-map"></div>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
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
                <div class="alert alert-section alert-warning" role="alert">
                    <h4 class="alert-heading">
                        <i class="bi bi-info-circle me-2"></i>No Events Registered
                    </h4>
                    <p class="mb-0">You are not currently registered as a committee member for any events. Here are available events with advisor contact information.</p>
                </div>
            `;

            if (availableEvents.length > 0) {
                html += '<h2 class="text-primary mb-4"><i class="bi bi-calendar-plus me-2"></i>Available Events</h2>';
                
                availableEvents.forEach((event, index) => {
                    const eventDate = formatEventDate(event.startdate);
                    
                    html += `
                        <div class="event-card" style="border-left-color: #007bff;">
                            <div class="event-header">
                                <div class="event-info">
                                    <div class="event-title">${escapeHtml(event.eventName)}</div>
                                    <p class="text-muted mb-2">${escapeHtml(event.description || 'No description available')}</p>
                                    <div class="card bg-light p-3">
                                        <h6 class="card-title text-primary">
                                            <i class="bi bi-person-badge me-2"></i>Event Advisor
                                        </h6>
                                        <p class="card-text mb-1"><strong>Name:</strong> ${escapeHtml(event.advisorName)}</p>
                                        <p class="card-text mb-0"><strong>Email:</strong> 
                                            <a href="mailto:${escapeHtml(event.advisorEmail)}" class="text-decoration-none">
                                                ${escapeHtml(event.advisorEmail)}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="event-date">
                                    <div class="month">${eventDate.month.toUpperCase()}</div>
                                    <div class="day">${eventDate.day}</div>
                                </div>
                            </div>
                            
                            <button onclick="toggleAvailableEventDetails('${event.eventID}', ${index})" 
                                    class="btn btn-outline-primary" id="available-btn-${index}">
                                <i class="bi bi-eye me-2"></i>View Details
                            </button>
                            
                            <div id="available-details-${index}" class="collapse mt-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <i class="bi bi-calendar-check text-primary me-3 fs-5"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Date</small>
                                                        <strong>${event.startdate === event.enddate ? formatFullDate(event.startdate) : formatFullDate(event.startdate) + ' - ' + formatFullDate(event.enddate)}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <i class="bi bi-geo-alt text-primary me-3 fs-5"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Location</small>
                                                        <strong>${escapeHtml(event.eventLocation)}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <i class="bi bi-flag text-primary me-3 fs-5"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Event Level</small>
                                                        <strong>${escapeHtml(event.eventLevel)}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <i class="bi bi-star text-primary me-3 fs-5"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Merit Status</small>
                                                        <span class="merit-display ${event.meritApplication == 1 ? 'merit-approved' : 'merit-none'}">
                                                            ${event.meritApplication == 1 ? 'Merit will be approved' : 'No merit application'}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            ${event.latitude && event.longitude ? `
                                            <div class="col-12">
                                                <div id="available-map-${index}" class="event-map"></div>
                                            </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                html += `
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                            <p class="text-muted">No events are currently available for registration.</p>
                        </div>
                    </div>
                `;
            }
            
            container.innerHTML = html;
        }

        function displayNoEvents() {
            const container = document.getElementById('eventsContainer');
            container.innerHTML = `
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                        <h4 class="text-muted">No Events Found</h4>
                        <p class="text-muted">No events are available at this time.</p>
                    </div>
                </div>
            `;
        }

        function displayError(message) {
            const container = document.getElementById('eventsContainer');
            container.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">
                        <i class="bi bi-exclamation-triangle me-2"></i>Error
                    </h4>
                    <p class="mb-0">${escapeHtml(message)}</p>
                </div>
            `;
        }

        function toggleEventDetails(eventID, index) {
            const detailsElement = document.getElementById(`details-${index}`);
            const button = document.getElementById(`btn-${index}`);
            const bsCollapse = new bootstrap.Collapse(detailsElement, { toggle: false });
            
            if (detailsElement.classList.contains('show')) {
                bsCollapse.hide();
                button.innerHTML = '<i class="bi bi-eye me-2"></i>View Details';
            } else {
                bsCollapse.show();
                button.innerHTML = '<i class="bi bi-eye-slash me-2"></i>Hide Details';
                
                // Initialize map if coordinates exist
                setTimeout(() => {
                    const mapElement = document.getElementById(`map-${index}`);
                    if (mapElement) {
                        initializeEventMap(eventID, `map-${index}`, index);
                    }
                }, 300);
            }
        }

        function toggleAvailableEventDetails(eventID, index) {
            const detailsElement = document.getElementById(`available-details-${index}`);
            const button = document.getElementById(`available-btn-${index}`);
            const bsCollapse = new bootstrap.Collapse(detailsElement, { toggle: false });
            
            if (detailsElement.classList.contains('show')) {
                bsCollapse.hide();
                button.innerHTML = '<i class="bi bi-eye me-2"></i>View Details';
            } else {
                bsCollapse.show();
                button.innerHTML = '<i class="bi bi-eye-slash me-2"></i>Hide Details';
                
                // Initialize map if coordinates exist
                setTimeout(() => {
                    const mapElement = document.getElementById(`available-map-${index}`);
                    if (mapElement) {
                        initializeAvailableEventMap(eventID, `available-map-${index}`, index);
                    }
                }, 300);
            }
        }

        function initializeEventMap(eventID, mapId, index) {
            if (mapInstances[mapId]) {
                mapInstances[mapId].remove();
                delete mapInstances[mapId];
            }

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
                            <strong>${escapeHtml(data.event.eventName)}</strong><br>
                            ${escapeHtml(data.event.eventLocation)}<br>
                            <small>Lat: ${lat}, Lng: ${lng}</small>
                        `).openPopup();
                        
                        // Store map instance
                        mapInstances[mapId] = map;
                        
                        // Force map resize
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
            if (mapInstances[mapId]) {
                mapInstances[mapId].remove();
                delete mapInstances[mapId];
            }

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
                            <strong>${escapeHtml(data.event.eventName)}</strong><br>
                            ${escapeHtml(data.event.eventLocation)}<br>
                            <small>Lat: ${lat}, Lng: ${lng}</small>
                        `).openPopup();
                        
                        // Store map instance
                        mapInstances[mapId] = map;
                        
                        // Force map resize
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
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>