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

// Check if user is admin
if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'admin') {
    header("Location: login/loginForm.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Event Management for MyPetakom">
    <meta name="author" content="MyPetakom Admin">
    <title>MyPetakom - Admin Event Management</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="icon" type="image/png" href="images/petakom.png">
    
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
            list-style: none;
        }

        .listyle {
            list-style: none;
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
        .admin-main-content {
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

        .border-primary {
            border-color: #a90000 !important;
        }

        .form-control:focus, .form-select:focus {
            border-color: #a90000;
            box-shadow: 0 0 0 0.25rem rgba(169, 0, 0, 0.25);
        }

        /* Status and Merit Badges */
        .status-badge, .merit-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }

        /* Event Status Badges */
        .status-active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-completed {
            background-color: #e2e3e5;
            color: #495057;
            border: 1px solid #d6d8db;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-postponed {
            background-color: #e1ecf4;
            color: #0c5460;
            border: 1px solid #b8daff;
        }

        /* Merit Status Badges */
        .merit-not-applied {
            background-color: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .merit-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .merit-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Action Buttons */
        .action-btn {
            padding: 4px 8px;
            font-size: 0.75em;
            border-radius: 4px;
            margin: 0 2px 2px 0;
            min-width: 60px;
        }

        /* Search Highlighting */
        .search-highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
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
            padding: 15px 12px;
        }

        .table-custom tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table-custom tbody td {
            vertical-align: middle;
            padding: 12px;
        }

        /* Clear button for search */
        .search-clear-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 12px;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .search-clear-btn:hover {
            background: #495057;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .admin-main-content {
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
            
            .admin-main-content {
                margin-left: 0;
                margin-right: 0;
                padding: 10px;
            }

            .action-btn {
                font-size: 0.7em;
                padding: 3px 6px;
                min-width: 50px;
            }

            .status-badge, .merit-badge {
                font-size: 0.7em;
                padding: 4px 8px;
                min-width: 60px;
            }
        }
    </style>
</head>

<body>
    <!-- Original Sidebar -->
    <div class="sidebar">
        <li class="listyle"><a href="admin.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a></li>
        <hr>
        <li class="listyle"><a href="#" class="nav-item">Profile</a>
        <a href="admin_manage_profile.php" class="nav-item">Manage Profile</a>
        </li>
        <hr>
        <li class="listyle"><a href="admin.php" class="nav-item">Dashboard</a></li>
        <hr>
        <li class="listyle"><a href="adminMember.php" class="nav-item">Manage Membership</a></li>
        <hr>
        <li class="listyle"><a href="admin_view_event.php" class="nav-item active">View Event</a></li>
        <hr>
    </div>

    <!-- Main Content -->
    <div class="admin-main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <h1 class="display-6 text-primary mb-0">
                                <i class="bi bi-gear-fill me-3"></i>Event Management
                            </h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Events Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 text-primary">
                                <i class="bi bi-search me-2"></i>Search & Manage Events
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Search Section -->
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <label for="searchInput" class="form-label">Search Events</label>
                                    <div class="position-relative">
                                        <input type="text" id="searchInput" class="form-control" 
                                               placeholder="Search by Event ID or Event Name...">
                                        <button type="button" id="searchClearBtn" class="search-clear-btn" title="Clear search">×</button>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" id="refreshBtn" class="btn btn-outline-primary">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                    </button>
                                </div>
                            </div>

                            <!-- Search Results Info -->
                            <div id="searchResults" class="mb-3">
                                <small class="text-muted"></small>
                            </div>

                            <!-- Events Table -->
                            <div class="table-responsive">
                                <table class="table table-custom" id="eventsTable">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-hash me-1"></i>Event ID</th>
                                            <th><i class="bi bi-calendar-event me-1"></i>Event Name</th>
                                            <th><i class="bi bi-calendar-date me-1"></i>Date</th>
                                            <th><i class="bi bi-flag me-1"></i>Status</th>
                                            <th><i class="bi bi-star me-1"></i>Merit Status</th>
                                            <th class="text-center"><i class="bi bi-gear me-1"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="eventsTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2 mb-0 text-muted">Loading events...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <nav aria-label="Events pagination">
                                <ul class="pagination justify-content-center" id="pagination">
                                    <!-- Pagination will be generated here -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i id="modalIcon" class="bi bi-question-circle display-4 text-warning"></i>
                    </div>
                    <p id="modalMessage" class="text-center mb-0">Are you sure you want to perform this action?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmBtn">
                        <i class="bi bi-check-circle me-1"></i>Confirm
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
        let currentSearchTerm = '';
        let pendingAction = null;
        let confirmModal;
        const eventsPerPage = 15;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap modal
            confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            
            loadEvents();
            setupSearch();
            setupRefresh();
        });

        function setupSearch() {
            const searchInput = document.getElementById('searchInput');
            const clearBtn = document.getElementById('searchClearBtn');

            let searchTimeout;
            searchInput.addEventListener('input', function() {
                const value = this.value.trim();
                clearBtn.style.display = value ? 'flex' : 'none';
                
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    performSearch();
                }, 500);
            });

            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                this.style.display = 'none';
                searchInput.focus();
                performSearch();
            });

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') performSearch();
            });
        }

        function setupRefresh() {
            document.getElementById('refreshBtn').addEventListener('click', function() {
                loadEvents(currentPage);
            });
        }

        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            currentSearchTerm = searchTerm;
            currentPage = 1;
            loadEvents();
        }

        function loadEvents(page = 1) {
            currentPage = page;
            
            let url = `api/get_admin_event.php?page=${page}&limit=${eventsPerPage}`;
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
                console.log('Admin events response:', data);
                if (data.success) {
                    displayEvents(data.events);
                    updatePagination(data.totalPages, data.currentPage);
                    updateSearchResults(currentSearchTerm, data.totalEvents, data.events.length);
                } else {
                    displayError(data.message);
                    updatePagination(0, 1);
                    updateSearchResults(currentSearchTerm, 0, 0);
                }
            })
            .catch(error => {
                console.error('Error loading events:', error);
                displayError('Network error: ' + error.message);
            });
        }

        function updateSearchResults(searchTerm, totalEvents = 0, currentEvents = 0) {
            const resultsElement = document.getElementById('searchResults').querySelector('small');
            
            if (!searchTerm) {
                resultsElement.textContent = totalEvents > 0 ? `Showing ${currentEvents} of ${totalEvents} events` : '';
                resultsElement.className = 'text-muted';
            } else {
                if (totalEvents === 0) {
                    resultsElement.textContent = `No events found for "${searchTerm}"`;
                    resultsElement.className = 'text-danger';
                } else {
                    resultsElement.textContent = `Found ${totalEvents} event${totalEvents !== 1 ? 's' : ''} for "${searchTerm}"`;
                    resultsElement.className = 'text-success';
                }
            }
        }

        function displayEvents(events) {
            const tbody = document.getElementById('eventsTableBody');
            
            if (events.length === 0) {
                const message = currentSearchTerm ? 
                    `No events found matching "${currentSearchTerm}"` : 
                    'No events found';
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                            <p class="mb-0 text-muted">${message}</p>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            events.forEach(event => {
                const highlightedEventName = highlightSearchTerm(event.eventName, currentSearchTerm);
                const highlightedEventID = highlightSearchTerm(event.eventID, currentSearchTerm);
                
                const eventStatus = parseInt(event.eventStatus) || 0;
                const meritApplication = parseInt(event.meritApplication) || 0;
                
                html += `
                    <tr>
                        <td>
                            <code class="text-muted">${highlightedEventID}</code>
                        </td>
                        <td class="fw-bold">${highlightedEventName}</td>
                        <td>
                            <small class="text-muted">${formatDate(event.startdate)}</small>
                        </td>
                        <td>
                            <span class="status-badge ${getEventStatusClass(eventStatus)}">${getEventStatusText(eventStatus)}</span>
                        </td>
                        <td>
                            <span class="merit-badge ${getMeritClass(meritApplication)}">${getMeritText(meritApplication)}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                ${getAdminActionButtons(eventStatus, meritApplication, event.eventID, event.eventName)}
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }

        function getAdminActionButtons(eventStatus, meritApplication, eventID, eventName) {
            let buttons = '';
            
            // Approve Merit button - only show if merit is pending approval (1)
            if (meritApplication === 1) {
                buttons += `
                    <button onclick="approveMerit('${eventID}', '${escapeHtml(eventName)}')" 
                            class="btn btn-success btn-sm action-btn" title="Approve Merit">
                        <i class="bi bi-check-circle me-1"></i>Approve Merit
                    </button>
                `;
            }
            
            // Postpone button - only for active (1) or pending (2) events
            if (eventStatus === 1 || eventStatus === 2) {
                buttons += `
                    <button onclick="postponeEvent('${eventID}', '${escapeHtml(eventName)}')" 
                            class="btn btn-warning btn-sm action-btn" title="Postpone Event">
                        <i class="bi bi-pause-circle me-1"></i>Postpone
                    </button>
                `;
            }
            
            // Cancel button - only for active (1) or pending (2) events
            if (eventStatus === 1 || eventStatus === 2) {
                buttons += `
                    <button onclick="cancelEvent('${eventID}', '${escapeHtml(eventName)}')" 
                            class="btn btn-danger btn-sm action-btn" title="Cancel Event">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                `;
            }
            
            // Reactivate button - only for cancelled (4) or postponed (5) events
            if (eventStatus === 4 || eventStatus === 5) {
                buttons += `
                    <button onclick="reactivateEvent('${eventID}', '${escapeHtml(eventName)}')" 
                            class="btn btn-info btn-sm action-btn" title="Reactivate Event">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reactivate
                    </button>
                `;
            }
            
            // If no actions available, show status
            if (buttons === '') {
                const statusText = getEventStatusText(eventStatus);
                buttons = `<small class="text-muted fst-italic">${statusText}</small>`;
            }
            
            return buttons;
        }

        function highlightSearchTerm(text, searchTerm) {
            if (!searchTerm || !text) return escapeHtml(text);
            
            const escapedText = escapeHtml(text);
            const escapedSearchTerm = escapeHtml(searchTerm);
            
            const regex = new RegExp(`(${escapedSearchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return escapedText.replace(regex, '<mark class="search-highlight">$1</mark>');
        }

        function getEventStatusClass(eventStatus) {
            switch(eventStatus) {
                case 1: return 'status-active';
                case 2: return 'status-pending';
                case 3: return 'status-completed';
                case 4: return 'status-cancelled';
                case 5: return 'status-postponed';
                default: return 'status-unknown';
            }
        }

        function getEventStatusText(eventStatus) {
            switch(eventStatus) {
                case 1: return 'Active';
                case 2: return 'Pending';
                case 3: return 'Completed';
                case 4: return 'Cancelled';
                case 5: return 'Postponed';
                default: return 'Unknown';
            }
        }

        function getMeritClass(meritStatus) {
            switch(meritStatus) {
                case 0: return 'merit-not-applied';
                case 1: return 'merit-pending';
                case 2: return 'merit-approved';
                default: return 'merit-unknown';
            }
        }

        function getMeritText(meritStatus) {
            switch(meritStatus) {
                case 0: return 'Not Applied';
                case 1: return 'Pending';
                case 2: return 'Approved';
                default: return 'Unknown';
            }
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
            const paginationElement = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                paginationElement.innerHTML = '';
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
            
            const startPage = Math.max(1, currentPageNum - 2);
            const endPage = Math.min(totalPages, currentPageNum + 2);
            
            // First page + ellipsis
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
            
            // Page numbers
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
            
            // Last page + ellipsis
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
            
            paginationElement.innerHTML = html;
        }

        function displayError(message) {
            const tbody = document.getElementById('eventsTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="bi bi-exclamation-triangle display-4 text-danger mb-3"></i>
                        <p class="mb-0 text-danger">Error: ${escapeHtml(message)}</p>
                    </td>
                </tr>
            `;
        }

        // Admin Actions
        function approveMerit(eventID, eventName) {
            showConfirmModal(
                'Approve Merit Application',
                `Are you sure you want to approve merit for "${eventName}"? This will make the event active.`,
                'success',
                'bi-check-circle',
                () => performAdminAction('approve_merit', eventID)
            );
        }

        function postponeEvent(eventID, eventName) {
            showConfirmModal(
                'Postpone Event',
                `Are you sure you want to postpone "${eventName}"? You can reactivate it later.`,
                'warning',
                'bi-pause-circle',
                () => performAdminAction('postpone', eventID)
            );
        }

        function cancelEvent(eventID, eventName) {
            showConfirmModal(
                'Cancel Event',
                `Are you sure you want to cancel "${eventName}"? You can reactivate it later if needed.`,
                'danger',
                'bi-x-circle',
                () => performAdminAction('cancel', eventID)
            );
        }

        function reactivateEvent(eventID, eventName) {
            showConfirmModal(
                'Reactivate Event',
                `Are you sure you want to reactivate "${eventName}"? This will restore the event to its appropriate status.`,
                'info',
                'bi-arrow-clockwise',
                () => performAdminAction('reactivate', eventID)
            );
        }

        function showConfirmModal(title, message, type, icon, callback) {
            document.getElementById('confirmModalLabel').textContent = title;
            document.getElementById('modalMessage').textContent = message;
            
            // Update icon
            const modalIcon = document.getElementById('modalIcon');
            modalIcon.className = `bi ${icon} display-4 text-${type}`;
            
            // Update confirm button
            const confirmBtn = document.getElementById('confirmBtn');
            confirmBtn.className = `btn btn-${type}`;
            
            pendingAction = callback;
            confirmModal.show();
        }

        // Handle confirm button click
        document.getElementById('confirmBtn').addEventListener('click', function() {
            if (pendingAction) {
                pendingAction();
                confirmModal.hide();
                pendingAction = null;
            }
        });

        function performAdminAction(action, eventID) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('eventID', eventID);

            console.log('Performing action:', action, 'on event:', eventID);

            // Show loading in button
            const actionButtons = document.querySelectorAll(`button[onclick*="${eventID}"]`);
            actionButtons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Processing...';
            });

            fetch('api/admin_event_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Admin action response:', text);
                try {
                    const data = JSON.parse(text);
                    
                    if (data.success) {
                        showAlert(data.message, 'success');
                        // Reload current page to refresh data
                        setTimeout(() => {
                            loadEvents(currentPage);
                        }, 1000);
                    } else {
                        showAlert('Error: ' + data.message, 'danger');
                        // Re-enable buttons
                        loadEvents(currentPage);
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response text:', text);
                    showAlert('Invalid response from server', 'danger');
                    loadEvents(currentPage);
                }
            })
            .catch(error => {
                console.error('Action error:', error);
                showAlert('Network error: ' + error.message, 'danger');
                loadEvents(currentPage);
            });
        }

        function showAlert(message, type = 'info') {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.alert-floating');
            existingAlerts.forEach(alert => alert.remove());

            // Create new alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-floating`;
            alertDiv.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
            
            const icon = type === 'success' ? 'bi-check-circle' : 
                        type === 'danger' ? 'bi-exclamation-triangle' : 'bi-info-circle';
            
            alertDiv.innerHTML = `
                <i class="bi ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Auto-dismiss after 4 seconds
            setTimeout(() => {
                if (document.body.contains(alertDiv)) {
                    const bsAlert = bootstrap.Alert.getInstance(alertDiv);
                    if (bsAlert) bsAlert.close();
                }
            }, 4000);
        }
    </script>
</body>
</html>