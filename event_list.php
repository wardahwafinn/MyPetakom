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

// Check if user is advisor or admin
if (!isset($_SESSION['userType']) || ($_SESSION['userType'] != 'advisor' && $_SESSION['userType'] != 'admin')) {
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

// Get current staff ID
$currentStaffID = $_SESSION['userID'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MyPetakom Event List">
    <meta name="author" content="Wardah Wafin">
    <title>MyPetakom - Event List</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="icon" type="image/png" href="images/petakom.png">
    
    <style>
        /* Custom styles to complement Bootstrap */
        body {
            height: 100%;
            margin: 0;
            padding: 0;
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

        .event-title {
            color: #333;
            padding: 10px 80px;
            display: block;
        }

        .submenu a {
            display: block;
            color: #333;
            text-decoration: none;
            padding: 8px 55px 7px 55px;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .submenu a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Main content with original sidebar offset */
        .main-content {
            margin-left: 230px;
            margin-right: 20px;
            padding: 20px;
            min-height: 100vh;
            overflow-y: auto;
        }

        /* Custom Bootstrap overrides */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
        }

        /* Custom brand colors */
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

        .border-primary {
            border-color: #a90000 !important;
        }

        /* Custom table styling */
        .table-custom {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table-custom thead {
            background-color: #a90000;
            color: white;
        }

        .table-custom thead th {
            border-bottom: 2px solid #8b0000;
            font-weight: bold;
            font-size: 14px;
        }

        .table-custom tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Status badges */
        .status-badge, .merit-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            min-width: 80px;
            text-align: center;
            border: 2px solid;
            box-sizing: border-box;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .status-upcoming {
            background-color: #cce7ff;
            color: #004085;
            border-color: #b3d7ff;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
        }

        .status-completed {
            background-color: #e2e3e5;
            color: #495057;
            border-color: #d6d8db;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .status-postponed {
            background-color: #f4cccc;
            color: #0c5460;
            border-color: #b8daff;
        }

        .status-unknown {
            background-color: #f8f9fa;
            color: #6c757d;
            border-color: #dee2e6;
        }

        .merit-not-applied {
            background-color: #f8f9fa;
            color: #6c757d;
            border-color: #dee2e6;
        }

        .merit-pending {
            background-color: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
        }

        .merit-approved {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .merit-unknown {
            background-color: #f8f9fa;
            color: #6c757d;
            border-color: #dee2e6;
        }

        /* Search highlight */
        .search-highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }

        /* Action buttons */
        .action-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.3s;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
            color: #495057;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .btn-details:hover {
            background-color: #17a2b8;
            color: white;
            border-color: #17a2b8;
        }

        .btn-edit:hover {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }

        .btn-delete:hover {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        .btn-qr:hover {
            background-color: #6f42c1;
            color: white;
            border-color: #6f42c1;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 220px;
                margin-right: 10px;
            }
        }

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
        }

        /* Loading animation */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .action-btn:disabled .action-icon {
            animation: spin 1s linear infinite;
        }

        /* QR Modal custom styles */
        .qr-image {
            max-width: 300px;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background: white;
        }

        .qr-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        /* Event ID and Name styling */
        .event-id {
            font-family: monospace;
            color: #666;
            font-weight: bold;
        }

        .event-name {
            font-weight: bold;
            color: #333;
        }

        .event-date {
            color: #666;
            font-family: monospace;
        }

        .event-location {
            color: #495057;
        }
    </style>
</head>

<body>
    <!-- Original Sidebar -->
    <div class="sidebar">
        <a href="advisor_dash.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a>
        <hr>
        <a href="advisor_dash.php" class="nav-item">Home</a>
        <hr>
        <a href="profile.php" class="nav-item">Profile</a>
        <hr>
        <span class="nav-item event-title">Event</span>
        <div class="submenu">
            <a href="event_list.php">&gt; Event List</a>
            <a href="committee.php">&gt; Committee</a>
            <a href="event_registration.php">&gt; Registration</a>
            <a href="attendance.php">&gt; Attendance</a>
        </div>
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
                                <i class="bi bi-calendar-event me-3"></i>Upcoming & Recent Events
                            </h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Events Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- Search Section -->
                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" class="form-control" id="searchInput" 
                                               placeholder="Search by Event ID or Name...">
                                        <button class="btn btn-primary" type="button" id="searchBtn">
                                            Search
                                        </button>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mt-2 mt-lg-0">
                                        <small class="text-muted" id="searchResults"></small>
                                    </div>
                                </div>
                            </div>

                            <!-- Events Table -->
                            <div class="table-responsive">
                                <table class="table table-custom" id="eventsTable">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-hash me-1"></i>Event ID</th>
                                            <th><i class="bi bi-calendar-event me-1"></i>Event Name</th>
                                            <th><i class="bi bi-calendar3 me-1"></i>Date</th>
                                            <th><i class="bi bi-geo-alt me-1"></i>Location</th>
                                            <th><i class="bi bi-circle-fill me-1"></i>Status</th>
                                            <th class="text-center"><i class="bi bi-gear me-1"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="eventsTableBody">
                                        <!-- Events will be loaded here via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                <nav aria-label="Events pagination">
                                    <ul class="pagination" id="pagination">
                                        <!-- Pagination will be generated here -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventDetailsModalLabel">
                        <i class="bi bi-info-circle me-2"></i>Event Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="eventDetailsBody">
                    <!-- Event details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrCodeModalLabel">
                        <i class="bi bi-qr-code me-2"></i>QR Code for Event
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="qrCodeBody">
                    <!-- QR Code will be displayed here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" id="copyUrlBtn" style="display: none;">
                        <i class="bi bi-clipboard me-1"></i>Copy URL
                    </button>
                    <button type="button" class="btn btn-success" id="downloadQRBtn" style="display: none;">
                        <i class="bi bi-download me-1"></i>Download QR
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentPage = 1;
        let totalPages = 1;
        let currentQRData = null;
        let currentSearchTerm = '';
        const eventsPerPage = 10;

        // Bootstrap modal instances
        let eventDetailsModal;
        let qrCodeModal;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap modals
            eventDetailsModal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
            qrCodeModal = new bootstrap.Modal(document.getElementById('qrCodeModal'));
            
            loadEvents();
            setupSearch();
        });

        function setupSearch() {
            const searchInput = document.getElementById('searchInput');
            const searchBtn = document.getElementById('searchBtn');

            // Search on button click
            searchBtn.addEventListener('click', performSearch);

            // Search on Enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            // Real-time search as user types (with debounce)
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    performSearch();
                }, 500); // Wait 500ms after user stops typing
            });
        }

        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            currentSearchTerm = searchTerm;
            currentPage = 1; // Reset to first page for new search
            loadEvents();
        }

        function loadEvents(page = 1) {
            currentPage = page;
            
            // Build URL with search parameters
            let url = `api/get_event.php?page=${page}&limit=${eventsPerPage}`;
            if (currentSearchTerm) {
                url += `&search=${encodeURIComponent(currentSearchTerm)}`;
            }
            
            fetch(url, {
                cache: 'no-cache',
                headers: {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Events response:', data); // Debug
                if (data.success) {
                    displayEvents(data.events);
                    updatePagination(data.totalPages, data.currentPage);
                    updateSearchResults(currentSearchTerm, data.totalEvents, data.events.length);
                } else {
                    document.getElementById('eventsTableBody').innerHTML = 
                        '<tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-inbox me-2"></i>No events found</td></tr>';
                    updatePagination(0, 1);
                    updateSearchResults(currentSearchTerm, 0, 0);
                }
            })
            .catch(error => {
                console.error('Error loading events:', error);
                document.getElementById('eventsTableBody').innerHTML = 
                    '<tr><td colspan="6" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle me-2"></i>Error loading events</td></tr>';
                updatePagination(0, 1);
            });
        }

        function updateSearchResults(searchTerm, totalEvents = 0, currentEvents = 0) {
            const resultsElement = document.getElementById('searchResults');
            
            if (!searchTerm) {
                resultsElement.innerHTML = totalEvents > 0 ? 
                    `<i class="bi bi-info-circle me-1"></i>Showing ${currentEvents} of ${totalEvents} events` : '';
            } else {
                if (totalEvents === 0) {
                    resultsElement.innerHTML = `<i class="bi bi-search me-1"></i>No events found for "<strong>${searchTerm}</strong>"`;
                    resultsElement.className = 'text-danger';
                } else {
                    resultsElement.innerHTML = `<i class="bi bi-check-circle me-1"></i>Found ${totalEvents} event${totalEvents !== 1 ? 's' : ''} for "<strong>${searchTerm}</strong>"`;
                    resultsElement.className = 'text-success';
                }
            }
        }

        function displayEvents(events) {
            const tbody = document.getElementById('eventsTableBody');
            
            if (events.length === 0) {
                const message = currentSearchTerm ? 
                    `<i class="bi bi-search me-2"></i>No events found matching "${currentSearchTerm}"` : 
                    '<i class="bi bi-inbox me-2"></i>No events found';
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">${message}</td></tr>`;
                return;
            }

            let html = '';
            events.forEach(event => {
                console.log('Processing event:', event); // Debug
                
                const status = getEventStatus(event.startdate, event.enddate, event.eventStatus);
                const statusClass = getStatusClass(status);
                
                // QR button - always the same
                const qrIcon = 'qr-code';
                const qrTitle = 'QR Code';
                
                // Highlight search terms
                const highlightedEventName = highlightSearchTerm(event.eventName, currentSearchTerm);
                const highlightedEventID = highlightSearchTerm(event.eventID, currentSearchTerm);
                
                html += `
                    <tr>
                        <td class="event-id">${highlightedEventID}</td>
                        <td class="event-name">${highlightedEventName}</td>
                        <td class="event-date">${formatDate(event.startdate)}</td>
                        <td class="event-location">${escapeHtml(event.eventLocation)}</td>
                        <td><span class="status-badge ${statusClass}">${status}</span></td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button onclick="viewEventDetails('${event.eventID}')" class="action-btn btn-details" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                ${canEditEvent(event.eventStatus) ? `
                                    <button onclick="editEvent('${event.eventID}')" class="action-btn btn-edit" title="Edit Event">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                ` : ''}
                                ${canDeleteEvent(event.eventStatus) ? `
                                    <button onclick="deleteEvent('${event.eventID}')" class="action-btn btn-delete" title="Delete Event">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                ` : ''}
                                <button onclick="generateQR('${event.eventID}', '${escapeHtml(event.eventName)}')" class="action-btn btn-qr" title="${qrTitle}" data-event-id="${event.eventID}">
                                    <i class="bi bi-${qrIcon} action-icon"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }

        function highlightSearchTerm(text, searchTerm) {
            if (!searchTerm || !text) return escapeHtml(text);
            
            const escapedText = escapeHtml(text);
            const escapedSearchTerm = escapeHtml(searchTerm);
            
            // Case-insensitive highlighting
            const regex = new RegExp(`(${escapedSearchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return escapedText.replace(regex, '<mark class="search-highlight">$1</mark>');
        }

        function getEventStatus(startDate, endDate, eventStatus) {
            const today = new Date();
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            today.setHours(0, 0, 0, 0);
            start.setHours(0, 0, 0, 0);
            end.setHours(23, 59, 59, 999);

            // Status meanings: 1=active, 2=pending, 3=completed, 4=cancelled, 5=postponed
            const status = parseInt(eventStatus);
            console.log('Event status:', status, typeof status); // Debug
            
            switch(status) {
                case 1: // Active
                    if (end < today) {
                        return 'Completed';
                    } else if (start > today) {
                        return 'Upcoming';
                    } else {
                        return 'Active';
                    }
                case 2: // Pending
                    return 'Pending Approval';
                case 3: // Completed
                    return 'Completed';
                case 4: // Cancelled
                    return 'Cancelled';
                case 5: // Postponed
                    return 'Postponed';
                default:
                    console.log('Unknown status:', status, typeof status);
                    return 'Unknown';
            }
        }

        function getStatusClass(status) {
            switch(status.toLowerCase()) {
                case 'active': 
                    return 'status-active';
                case 'upcoming': 
                    return 'status-upcoming';
                case 'pending approval': 
                    return 'status-pending';
                case 'completed': 
                    return 'status-completed';
                case 'cancelled': 
                    return 'status-cancelled';
                case 'postponed': 
                    return 'status-postponed';
                default: 
                    return 'status-unknown';
            }
        }

        function getMeritStatus(meritApplication) {
            // Merit meanings: 0=not_applied, 1=pending, 2=approved
            const merit = parseInt(meritApplication);
            switch(merit) {
                case 0:
                    return 'Not Applied';
                case 1:
                    return 'Pending';
                case 2:
                    return 'Approved';
                default:
                    return 'Unknown';
            }
        }

        function getMeritClass(meritStatus) {
            switch(meritStatus.toLowerCase()) {
                case 'not applied':
                    return 'merit-not-applied';
                case 'pending':
                    return 'merit-pending';
                case 'approved':
                    return 'merit-approved';
                default:
                    return 'merit-unknown';
            }
        }

        // Helper functions to determine if actions are allowed
        function canEditEvent(eventStatus) {
            // Allow editing only for active, pending, and postponed events
            // Status meanings: 1=active, 2=pending, 3=completed, 4=cancelled, 5=postponed
            const status = parseInt(eventStatus);
            return [1, 2, 5].includes(status);
        }

        function canDeleteEvent(eventStatus) {
            // Allow deletion only for pending and postponed events
            // Don't allow deletion of active, completed, or cancelled events
            const status = parseInt(eventStatus);
            return [2, 5].includes(status);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function updatePagination(totalPagesCount, currentPageNum) {
            totalPages = totalPagesCount;
            const paginationUl = document.querySelector('#pagination');
            
            if (totalPages <= 1) {
                paginationUl.innerHTML = '';
                return;
            }

            let html = '';
            
            // Previous button
            if (currentPageNum > 1) {
                html += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="loadEvents(${currentPageNum - 1}); return false;">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                `;
            }
            
            // Page numbers (show max 5 pages around current page)
            const startPage = Math.max(1, currentPageNum - 2);
            const endPage = Math.min(totalPages, currentPageNum + 2);
            
            if (startPage > 1) {
                html += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="loadEvents(1); return false;">1</a>
                    </li>
                `;
                if (startPage > 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                if (i === currentPageNum) {
                    html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                } else {
                    html += `
                        <li class="page-item">
                            <a class="page-link" href="#" onclick="loadEvents(${i}); return false;">${i}</a>
                        </li>
                    `;
                }
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                html += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="loadEvents(${totalPages}); return false;">${totalPages}</a>
                    </li>
                `;
            }
            
            // Next button
            if (currentPageNum < totalPages) {
                html += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="loadEvents(${currentPageNum + 1}); return false;">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                `;
            }
            
            paginationUl.innerHTML = html;
        }

        function viewEventDetails(eventID) {
            fetch(`api/get_event_details.php?eventID=${encodeURIComponent(eventID)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showEventDetailsModal(data.event);
                    } else {
                        showAlert('Error loading event details: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error loading event details', 'danger');
                });
        }

        function showEventDetailsModal(event) {
            const body = document.getElementById('eventDetailsBody');
            
            const status = getEventStatus(event.startdate, event.enddate, event.eventStatus);
            const statusClass = getStatusClass(status);
            const meritStatus = getMeritStatus(event.meritApplication);
            const meritClass = getMeritClass(meritStatus);
            
            body.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Event ID:</label>
                        <div class="form-control-plaintext">${escapeHtml(event.eventID)}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Event Level:</label>
                        <div class="form-control-plaintext text-capitalize">${escapeHtml(event.eventLevel)}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Event Name:</label>
                        <div class="form-control-plaintext">${escapeHtml(event.eventName)}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Description:</label>
                        <div class="form-control-plaintext">${escapeHtml(event.description || 'No description')}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Start Date:</label>
                        <div class="form-control-plaintext">${formatDate(event.startdate)}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">End Date:</label>
                        <div class="form-control-plaintext">${formatDate(event.enddate)}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Location:</label>
                        <div class="form-control-plaintext">${escapeHtml(event.eventLocation)}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status:</label>
                        <div class="form-control-plaintext">
                            <span class="status-badge ${statusClass}">${status}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Merit Status:</label>
                        <div class="form-control-plaintext">
                            <span class="merit-badge ${meritClass}">${meritStatus}</span>
                        </div>
                    </div>
                    ${event.approvalLetter ? `
                    <div class="col-12">
                        <label class="form-label fw-bold">Approval Letter:</label>
                        <div class="form-control-plaintext">
                            <a href="api/uploads/${event.approvalLetter}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-file-earmark-pdf me-1"></i>View Document
                            </a>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            eventDetailsModal.show();
        }

        function editEvent(eventID) {
            window.location.href = `event_registration.php?edit=${encodeURIComponent(eventID)}`;
        }

        function deleteEvent(eventID) {
            if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                fetch('api/delete_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({eventID: eventID})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Event deleted successfully!', 'success');
                        loadEvents(currentPage);
                    } else {
                        showAlert('Error: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while deleting the event', 'danger');
                });
            }
        }

        function generateQR(eventID, eventName) {
            // Find the button that was clicked
            const button = document.querySelector(`[data-event-id="${eventID}"]`);
            if (!button) return;

            // Show loading state
            const originalIcon = button.querySelector('.action-icon').className;
            button.querySelector('.action-icon').className = 'bi bi-hourglass-split';
            button.disabled = true;

            fetch(`api/generate_qr.php?eventID=${encodeURIComponent(eventID)}`)
                .then(response => response.json())
                .then(data => {
                    console.log('QR Generation Response:', data);
                    
                    if (data.success) {
                        currentQRData = data;
                        showQRModal(data);
                        
                        // Show success message
                        if (!data.isExisting) {
                            showAlert('QR Code generated successfully!', 'success');
                        }
                    } else {
                        console.error('QR Generation Failed:', data.message);
                        showAlert('Error generating QR code: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('QR Generation Error:', error);
                    showAlert('An error occurred while generating the QR code', 'danger');
                })
                .finally(() => {
                    // Always restore original QR icon
                    button.querySelector('.action-icon').className = originalIcon;
                    button.disabled = false;
                });
        }

        function showQRModal(qrData) {
            const body = document.getElementById('qrCodeBody');
            const downloadBtn = document.getElementById('downloadQRBtn');
            const copyBtn = document.getElementById('copyUrlBtn');
            
            // Update modal header
            const headerTitle = document.querySelector('#qrCodeModalLabel');
            headerTitle.innerHTML = `<i class="bi bi-qr-code me-2"></i>QR Code for ${qrData.eventName}`;
            
            // Generate QR image URL
            const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&format=png&margin=10&data=${encodeURIComponent(qrData.qrUrl)}`;
            
            body.innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <div class="alert alert-info">
                            <h5 class="alert-heading">
                                <i class="bi bi-info-circle me-2"></i>${escapeHtml(qrData.eventName)}
                            </h5>
                            <hr>
                            <p class="mb-1"><strong>Event ID:</strong> ${escapeHtml(qrData.eventID)}</p>
                            <p class="mb-0"><strong>QR URL:</strong> 
                                <a href="${qrData.qrUrl}" target="_blank" class="text-break">${qrData.qrUrl}</a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="position-relative d-inline-block">
                                <img src="${qrImageUrl}" alt="QR Code for ${escapeHtml(qrData.eventName)}" 
                                     class="qr-image" onload="onQRImageLoad()" onerror="onQRImageError()">
                                <div class="qr-loading position-absolute top-50 start-50 translate-middle" id="qrLoading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Store the image URL for download
            currentQRData.qrImageUrl = qrImageUrl;
            
            // Show action buttons
            downloadBtn.style.display = 'inline-block';
            copyBtn.style.display = 'inline-block';
            
            qrCodeModal.show();
        }

        function onQRImageLoad() {
            const loading = document.getElementById('qrLoading');
            if (loading) {
                loading.style.display = 'none';
            }
        }

        function onQRImageError() {
            const loading = document.getElementById('qrLoading');
            if (loading) {
                loading.innerHTML = '<div class="text-danger"><i class="bi bi-exclamation-triangle"></i> Failed to load</div>';
            }
        }

        function downloadQR() {
            if (currentQRData && currentQRData.qrImageUrl) {
                // Create a temporary link to download the image
                const link = document.createElement('a');
                link.href = currentQRData.qrImageUrl;
                link.download = `QR_${currentQRData.eventID}_${currentQRData.eventName.replace(/[^a-zA-Z0-9]/g, '_')}.png`;
                link.target = '_blank';
                
                // Append to body, click, and remove
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showAlert('QR Code download started!', 'success');
            } else {
                showAlert('No QR code available for download', 'warning');
            }
        }

        function copyQRUrl() {
            if (currentQRData && currentQRData.qrUrl) {
                navigator.clipboard.writeText(currentQRData.qrUrl).then(() => {
                    showAlert('QR URL copied to clipboard!', 'success');
                }).catch(err => {
                    console.error('Failed to copy URL:', err);
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = currentQRData.qrUrl;
                    document.body.appendChild(textArea);
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        showAlert('QR URL copied to clipboard!', 'success');
                    } catch (err) {
                        showAlert('Failed to copy URL', 'danger');
                    }
                    document.body.removeChild(textArea);
                });
            } else {
                showAlert('No QR URL available to copy', 'warning');
            }
        }

        // Show Bootstrap alert
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Auto-dismiss after 4 seconds
            setTimeout(() => {
                if (alertDiv && alertDiv.parentNode) {
                    const bsAlert = bootstrap.Alert.getInstance(alertDiv);
                    if (bsAlert) bsAlert.close();
                }
            }, 4000);
        }

        // Event handlers for modal cleanup
        document.getElementById('qrCodeModal').addEventListener('hidden.bs.modal', function () {
            currentQRData = null;
            document.getElementById('downloadQRBtn').style.display = 'none';
            document.getElementById('copyUrlBtn').style.display = 'none';
        });
    </script>
</body>
</html>