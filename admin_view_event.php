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
<html>
<head>
    <meta name="description" content="Admin Event Management for MyPetakom">
    <meta name="author" content="MyPetakom Admin">
    <title>MyPetakom - Admin Event Management</title>
    <link rel="stylesheet" type="text/css" href="style/admin.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <meta charset="UTF-8">
    
    <style>
        /* Enhanced styles for the updated event management system */
        
        /* Admin Events Container */
        .admin-events-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Search Section Styling */
        .admin-search-section {
            margin-bottom: 25px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .search-input-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            position: relative;
        }

        .admin-search-input {
            flex: 1;
            padding: 10px 45px 10px 15px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .admin-search-input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        .admin-search-btn {
            position: absolute;
            right: 10px;;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #6c757d;
            padding: px;
            transition: color 0.3s ease;
        }

        .admin-search-btn:hover {
            color: #495057;
        }

        .search-results {
            color: #666;
            font-size: 14px;
            font-style: italic;
        }

        .search-results.no-results {
            color: #dc3545;
        }

        .search-results.found-results {
            color: #28a745;
        }

        /* Table Styling */
        .admin-events-table-wrapper {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        .admin-events-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .admin-events-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .admin-events-table th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-events-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .admin-events-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Status and Merit Badges */
        .status-badge, .merit-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8em;
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
        .admin-actions {
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8em;
            font-weight: 500;
            color: white;
            transition: all 0.2s ease;
            min-width: 70px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .approve-btn {
            background-color: #28a745;
        }

        .approve-btn:hover {
            background-color: #218838;
            transform: translateY(-1px);
        }

        .postpone-btn {
            background-color: #17a2b8;
        }

        .postpone-btn:hover {
            background-color: #138496;
            transform: translateY(-1px);
        }

        .cancel-btn {
            background-color: #dc3545;
        }

        .cancel-btn:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        .reactivate-btn {
            background-color: #6f42c1;
        }

        .reactivate-btn:hover {
            background-color: #5a32a3;
            transform: translateY(-1px);
        }

        .no-actions {
            color: #6c757d;
            font-style: italic;
            font-size: 0.9em;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
        }

        .page-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            color: #333;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .page-btn:hover {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .page-btn.current-page {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .page-ellipsis {
            padding: 8px 4px;
            color: #6c757d;
        }

        /* Modal Styling */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            padding: 0;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s ease;
        }

        .close-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .cancel-btn, .confirm-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .cancel-btn {
            background: #6c757d;
            color: white;
        }

        .cancel-btn:hover {
            background: #545b62;
        }

        .confirm-btn {
            background: #dc3545;
            color: white;
        }

        .confirm-btn.approve-confirm {
            background: #28a745;
        }

        .confirm-btn.postpone-confirm {
            background: #17a2b8;
        }

        .confirm-btn.reactivate-confirm {
            background: #6f42c1;
        }

        .confirm-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        /* Search Highlighting */
        .search-highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }

        /* Loading and Error States */
        .no-events, .error-message {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 30px;
        }

        .error-message {
            color: #dc3545;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-events-container {
                padding: 15px;
                margin: 10px 0;
            }

            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }

            .action-btn {
                font-size: 0.75em;
                padding: 4px 8px;
                min-width: 60px;
            }

            .status-badge, .merit-badge {
                font-size: 0.7em;
                padding: 3px 8px;
                min-width: 60px;
            }

            .admin-events-table th,
            .admin-events-table td {
                padding: 8px 6px;
                font-size: 0.9em;
            }

            .admin-search-input {
                padding: 8px 40px 8px 12px;
                font-size: 13px;
            }

            .admin-search-btn {
                font-size: 14px;
                right: 8px;
            }
        }

        /* Notification Styling */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 16px;
            border-radius: 8px;
            color: white;
            z-index: 10000;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 300px;
            max-width: 500px;
            transition: all 0.3s ease;
            animation: slideInFromRight 0.3s ease;
        }

        @keyframes slideInFromRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .notification-success {
            background-color: #28a745;
        }

        .notification-error {
            background-color: #dc3545;
        }

        .notification-info {
            background-color: #007bff;
        }

        .notification-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            margin-left: auto;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s ease;
        }

        .notification-close:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="background">
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

    <div class="top-right-bar">
        <a href="profile.html" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">HI, ADMIN
        </a>
        <a href="login/logout.php">
            <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
        </a>
    </div>

    <div class="admin-main-content">
        <div class="page-header">
            <h1>Event Management</h1>
        </div>

        <div class="admin-events-container">
            <!-- Search Section -->
            <div class="admin-search-section">
                <div class="search-input-group">
                    <input type="text" id="searchInput" placeholder="Search by Event ID or Event Name..." class="admin-search-input">
                    <button type="button" id="searchBtn" class="admin-search-btn">🔍</button>
                </div>
                <div class="search-results" id="searchResults"></div>
            </div>

            <!-- Events Table -->
            <div class="admin-events-table-wrapper">
                <table class="admin-events-table" id="eventsTable">
                    <thead>
                        <tr>
                            <th>Event ID</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Merit Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="eventsTableBody">
                        <!-- Events will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination" id="pagination">
                <!-- Pagination will be generated here -->
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Confirm Action</h3>
                <button onclick="closeModal()" class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modalMessage">Are you sure you want to perform this action?</p>
            </div>
            <div class="modal-footer">
                <button onclick="closeModal()" class="cancel-btn">Cancel</button>
                <button onclick="confirmAction()" class="confirm-btn" id="confirmBtn">Confirm</button>
            </div>
        </div>
    </div>

    <script>
let currentPage = 1;
let totalPages = 1;
let currentSearchTerm = '';
let pendingAction = null;
const eventsPerPage = 15;

document.addEventListener('DOMContentLoaded', function() {
    loadEvents();
    setupSearch();
});

function setupSearch() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');

    searchBtn.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') performSearch();
    });

    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch();
        }, 500);
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
        console.log('Admin events response:', data); // Debug log
        if (data.success) {
            displayEvents(data.events);
            updatePagination(data.totalPages, data.currentPage);
            updateSearchResults(currentSearchTerm, data.totalEvents, data.events.length);
        } else {
            document.getElementById('eventsTableBody').innerHTML = 
                '<tr><td colspan="6" class="no-events">Error: ' + data.message + '</td></tr>';
            updatePagination(0, 1);
            updateSearchResults(currentSearchTerm, 0, 0);
        }
    })
    .catch(error => {
        console.error('Error loading events:', error);
        document.getElementById('eventsTableBody').innerHTML = 
            '<tr><td colspan="6" class="error-message">Network error: ' + error.message + '</td></tr>';
    });
}

function updateSearchResults(searchTerm, totalEvents = 0, currentEvents = 0) {
    const resultsElement = document.getElementById('searchResults');
    
    if (!searchTerm) {
        resultsElement.textContent = totalEvents > 0 ? `Showing ${currentEvents} of ${totalEvents} events` : '';
        resultsElement.className = 'search-results';
    } else {
        if (totalEvents === 0) {
            resultsElement.textContent = `No events found for "${searchTerm}"`;
            resultsElement.className = 'search-results no-results';
        } else {
            resultsElement.textContent = `Found ${totalEvents} event${totalEvents !== 1 ? 's' : ''} for "${searchTerm}"`;
            resultsElement.className = 'search-results found-results';
        }
    }
}

function displayEvents(events) {
    const tbody = document.getElementById('eventsTableBody');
    
    if (events.length === 0) {
        const message = currentSearchTerm ? 
            `No events found matching "${currentSearchTerm}"` : 
            'No events found';
        tbody.innerHTML = `<tr><td colspan="6" class="no-events">${message}</td></tr>`;
        return;
    }

    let html = '';
    events.forEach(event => {
        console.log('Processing event:', event); // Debug log
        
        const highlightedEventName = highlightSearchTerm(event.eventName, currentSearchTerm);
        const highlightedEventID = highlightSearchTerm(event.eventID, currentSearchTerm);
        
        // Ensure we have integer values
        const eventStatus = parseInt(event.eventStatus) || 0;
        const meritApplication = parseInt(event.meritApplication) || 0;
        
        html += `
            <tr>
                <td class="event-id">${highlightedEventID}</td>
                <td class="event-name">${highlightedEventName}</td>
                <td class="event-date">${formatDate(event.startdate)}</td>
                <td class="event-status">
                    <span class="status-badge ${getEventStatusClass(eventStatus)}">${getEventStatusText(eventStatus)}</span>
                </td>
                <td class="merit-status">
                    <span class="merit-badge ${getMeritClass(meritApplication)}">${getMeritText(meritApplication)}</span>
                </td>
                <td class="admin-actions">
                    <div class="action-buttons">
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
                    class="action-btn approve-btn" title="Approve Merit">
                Approve Merit
            </button>
        `;
    }
    
    // Postpone button - only for active (1) or pending (2) events
    if (eventStatus === 1 || eventStatus === 2) {
        buttons += `
            <button onclick="postponeEvent('${eventID}', '${escapeHtml(eventName)}')" 
                    class="action-btn postpone-btn" title="Postpone Event">
                Postpone
            </button>
        `;
    }
    
    // Cancel button - only for active (1) or pending (2) events
    if (eventStatus === 1 || eventStatus === 2) {
        buttons += `
            <button onclick="cancelEvent('${eventID}', '${escapeHtml(eventName)}')" 
                    class="action-btn cancel-btn" title="Cancel Event">
                ✕ Cancel
            </button>
        `;
    }
    
    // Reactivate button - only for cancelled (4) or postponed (5) events
    if (eventStatus === 4 || eventStatus === 5) {
        buttons += `
            <button onclick="reactivateEvent('${eventID}', '${escapeHtml(eventName)}')" 
                    class="action-btn reactivate-btn" title="Reactivate Event">
                ↻ Reactivate
            </button>
        `;
    }
    
    // If no actions available, show status
    if (buttons === '') {
        const statusText = getEventStatusText(eventStatus);
        buttons = `<span class="no-actions">${statusText}</span>`;
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
    console.log('getEventStatusClass called with:', eventStatus, typeof eventStatus); // Debug
    switch(eventStatus) {
        case 1: return 'status-active';
        case 2: return 'status-pending';
        case 3: return 'status-completed';
        case 4: return 'status-cancelled';
        case 5: return 'status-postponed';
        default: 
            console.log('Unknown event status:', eventStatus); // Debug
            return 'status-unknown';
    }
}

function getEventStatusText(eventStatus) {
    console.log('getEventStatusText called with:', eventStatus, typeof eventStatus); // Debug
    switch(eventStatus) {
        case 1: return 'Active';
        case 2: return 'Pending';
        case 3: return 'Completed';
        case 4: return 'Cancelled';
        case 5: return 'Postponed';
        default: 
            console.log('Unknown event status text:', eventStatus); // Debug
            return 'Unknown';
    }
}

function getMeritClass(meritStatus) {
    console.log('getMeritClass called with:', meritStatus, typeof meritStatus); // Debug
    switch(meritStatus) {
        case 0: return 'merit-not-applied';
        case 1: return 'merit-pending';
        case 2: return 'merit-approved';
        default: 
            console.log('Unknown merit status:', meritStatus); // Debug
            return 'merit-unknown';
    }
}

function getMeritText(meritStatus) {
    console.log('getMeritText called with:', meritStatus, typeof meritStatus); // Debug
    switch(meritStatus) {
        case 0: return 'Not Applied';
        case 1: return 'Pending';
        case 2: return 'Approved';
        default: 
            console.log('Unknown merit status text:', meritStatus); // Debug
            return 'Unknown';
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
    const paginationDiv = document.getElementById('pagination');
    
    if (totalPages <= 1) {
        paginationDiv.innerHTML = '';
        return;
    }

    let html = '';
    
    if (currentPageNum > 1) {
        html += `<button onclick="loadEvents(${currentPageNum - 1})" class="page-btn prev-btn">&lt;</button>`;
    }
    
    const startPage = Math.max(1, currentPageNum - 2);
    const endPage = Math.min(totalPages, currentPageNum + 2);
    
    if (startPage > 1) {
        html += `<button onclick="loadEvents(1)" class="page-btn">1</button>`;
        if (startPage > 2) {
            html += `<span class="page-ellipsis">...</span>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        if (i === currentPageNum) {
            html += `<button class="page-btn current-page">${i}</button>`;
        } else {
            html += `<button onclick="loadEvents(${i})" class="page-btn">${i}</button>`;
        }
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<span class="page-ellipsis">...</span>`;
        }
        html += `<button onclick="loadEvents(${totalPages})" class="page-btn">${totalPages}</button>`;
    }
    
    if (currentPageNum < totalPages) {
        html += `<button onclick="loadEvents(${currentPageNum + 1})" class="page-btn next-btn">&gt;</button>`;
    }
    
    paginationDiv.innerHTML = html;
}

// Admin Actions
function approveMerit(eventID, eventName) {
    showConfirmModal(
        'Approve Merit Application',
        `Are you sure you want to approve merit for "${eventName}"? This will make the event active.`,
        'approve',
        () => performAdminAction('approve_merit', eventID)
    );
}

function postponeEvent(eventID, eventName) {
    showConfirmModal(
        'Postpone Event',
        `Are you sure you want to postpone "${eventName}"? You can reactivate it later.`,
        'postpone',
        () => performAdminAction('postpone', eventID)
    );
}

function cancelEvent(eventID, eventName) {
    showConfirmModal(
        'Cancel Event',
        `Are you sure you want to cancel "${eventName}"? You can reactivate it later if needed.`,
        'cancel',
        () => performAdminAction('cancel', eventID)
    );
}

function reactivateEvent(eventID, eventName) {
    showConfirmModal(
        'Reactivate Event',
        `Are you sure you want to reactivate "${eventName}"? This will restore the event to its appropriate status.`,
        'reactivate',
        () => performAdminAction('reactivate', eventID)
    );
}

function showConfirmModal(title, message, action, callback) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    
    const confirmBtn = document.getElementById('confirmBtn');
    confirmBtn.className = `confirm-btn ${action}-confirm`;
    
    pendingAction = callback;
    document.getElementById('confirmModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('confirmModal').style.display = 'none';
    pendingAction = null;
}

function confirmAction() {
    if (pendingAction) {
        pendingAction();
        closeModal();
    }
}

function performAdminAction(action, eventID) {
    const formData = new FormData();
    formData.append('action', action);
    formData.append('eventID', eventID);

    console.log('Performing action:', action, 'on event:', eventID); // Debug

    // Show loading indicator
    const actionButtons = document.querySelectorAll(`button[onclick*="${eventID}"]`);
    actionButtons.forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.6';
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
        console.log('Admin action response:', text); // Debug
        try {
            const data = JSON.parse(text);
            
            if (data.success) {
                showNotification(data.message, 'success');
                // Reload current page to refresh data
                setTimeout(() => {
                    loadEvents(currentPage);
                }, 1000);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Response text:', text);
            showNotification('Invalid response from server', 'error');
        }
    })
    .catch(error => {
        console.error('Action error:', error);
        showNotification('Network error: ' + error.message, 'error');
    })
    .finally(() => {
        // Re-enable buttons
        actionButtons.forEach(btn => {
            btn.disabled = false;
            btn.style.opacity = '1';
        });
    });
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => {
        if (document.body.contains(notification)) {
            document.body.removeChild(notification);
        }
    });

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Add icon based on type
    const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
    notification.innerHTML = `
        <span class="notification-icon">${icon}</span>
        <span class="notification-text">${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '12px 16px',
        borderRadius: '8px',
        color: 'white',
        zIndex: '10000',
        fontSize: '14px',
        fontWeight: '500',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        backgroundColor: type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff',
        display: 'flex',
        alignItems: 'center',
        gap: '8px',
        minWidth: '300px',
        maxWidth: '500px',
        transition: 'all 0.3s ease',
        animation: 'slideInFromRight 0.3s ease'
    });
    
    // Style the close button
    const closeBtn = notification.querySelector('.notification-close');
    Object.assign(closeBtn.style, {
        background: 'none',
        border: 'none',
        color: 'white',
        fontSize: '18px',
        cursor: 'pointer',
        marginLeft: 'auto',
        padding: '0',
        width: '20px',
        height: '20px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        borderRadius: '50%',
        transition: 'background-color 0.2s ease'
    });
    
    closeBtn.addEventListener('mouseenter', () => {
        closeBtn.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
    });
    
    closeBtn.addEventListener('mouseleave', () => {
        closeBtn.style.backgroundColor = 'transparent';
    });
    
    document.body.appendChild(notification);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        if (document.body.contains(notification)) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }
    }, 4000);
}

// Close modal when clicking outside
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInFromRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);
    </script>
</body>
</html>