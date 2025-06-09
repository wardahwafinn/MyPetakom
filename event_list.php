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
<html>
<head>
    <meta name="description" content="MyPetakom Event List">
    <meta name="author" content="Wardah Wafin">
    <title>MyPetakom - Event List</title>
    <link rel="stylesheet" href="style/event_list.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
</head>

<body class="background">
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

    <div class="main-content">
        <h1>Upcoming & Recent Events</h1>
        
        <div class="events-container">
            <!-- Search Section -->
            <div class="search-section">
                <div class="search-container">
                    <div class="search-input-group">
                        <input type="text" id="searchInput" placeholder="Event ID/Name" class="search-input">
                        <button type="button" id="searchBtn" class="search-btn">
                            <span class="search-icon">🔍</span>
                        </button>
                    </div>
                    <div class="search-info">
                        <span id="searchResults" class="search-results"></span>
                    </div>
                </div>
            </div>

            <div class="events-table-wrapper">
                <table class="events-table" id="eventsTable">
                    <thead>
                        <tr>
                            <th>Event ID</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Status</th>
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

    <!-- Event Details Modal -->
    <div id="eventDetailsModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Event Details</h3>
                <button onclick="closeEventDetailsModal()" class="close-btn">&times;</button>
            </div>
            <div class="modal-body" id="eventDetailsBody">
                <!-- Event details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button onclick="closeEventDetailsModal()" class="cancel-btn">Close</button>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div id="qrCodeModal" class="modal-overlay">
        <div class="modal-content qr-modal">
            <div class="modal-header">
                <h3>QR Code for Event</h3>
                <button onclick="closeQRModal()" class="close-btn">&times;</button>
            </div>
            <div class="modal-body" id="qrCodeBody">
                <!-- QR Code will be displayed here -->
            </div>
            <div class="modal-footer">
                <button onclick="copyQRUrl()" class="action-btn copy-btn" id="copyUrlBtn" style="display: none;">Copy URL</button>
                <button onclick="downloadQR()" class="action-btn download-btn" id="downloadQRBtn" style="display: none;">Download QR</button>
                <button onclick="closeQRModal()" class="cancel-btn">Close</button>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let totalPages = 1;
        let currentQRData = null;
        let currentSearchTerm = '';
        const eventsPerPage = 10;

        document.addEventListener('DOMContentLoaded', function() {
            loadEvents();
            setupSearch();
        });

        function setupSearch() {
            const searchInput = document.getElementById('searchInput');
            const searchBtn = document.getElementById('searchBtn');
            const clearBtn = document.getElementById('clearBtn');

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

            // Clear search
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                currentSearchTerm = '';
                currentPage = 1;
                loadEvents();
                updateSearchResults('');
            });

            // Show/hide clear button
            searchInput.addEventListener('input', function() {
                clearBtn.style.display = this.value.trim() ? 'flex' : 'none';
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
                        '<tr><td colspan="6" class="no-events">No events found</td></tr>';
                    updatePagination(0, 1);
                    updateSearchResults(currentSearchTerm, 0, 0);
                }
            })
            .catch(error => {
                console.error('Error loading events:', error);
                document.getElementById('eventsTableBody').innerHTML = 
                    '<tr><td colspan="6" class="error-message">Error loading events</td></tr>';
                updatePagination(0, 1);
            });
        }

        function updateSearchResults(searchTerm, totalEvents = 0, currentEvents = 0) {
            const resultsElement = document.getElementById('searchResults');
            
            if (!searchTerm) {
                resultsElement.textContent = totalEvents > 0 ? `Showing ${currentEvents} of ${totalEvents} events` : '';
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
                console.log('Processing event:', event); // Debug
                
                const status = getEventStatus(event.startdate, event.enddate, event.eventStatus);
                const statusClass = getStatusClass(status);
                
                // Check QR code status
                const hasQR = event.qrCodePath && event.qrCodePath !== '';
                const qrIcon = hasQR ? '✅' : '📱';
                const qrTitle = hasQR ? 'QR Code Generated - Click to View/Download' : 'Generate QR Code';
                
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
                        <td class="actions-cell">
                            <div class="action-buttons">
                                <button onclick="viewEventDetails('${event.eventID}')" class="action-btn details-btn" title="View Details">
                                    <span class="action-icon">⋮</span>
                                </button>
                                ${canEditEvent(event.eventStatus) ? `
                                    <button onclick="editEvent('${event.eventID}')" class="action-btn edit-btn" title="Edit Event">
                                        <span class="action-icon">✏️</span>
                                    </button>
                                ` : ''}
                                ${canDeleteEvent(event.eventStatus) ? `
                                    <button onclick="deleteEvent('${event.eventID}')" class="action-btn delete-btn" title="Delete Event">
                                        <span class="action-icon">🗑️</span>
                                    </button>
                                ` : ''}
                                <button onclick="generateQR('${event.eventID}', '${escapeHtml(event.eventName)}')" class="action-btn qr-btn" title="${qrTitle}" data-event-id="${event.eventID}">
                                    <span class="action-icon">${qrIcon}</span>
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
            const paginationDiv = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                paginationDiv.innerHTML = '';
                return;
            }

            let html = '';
            
            // Previous button
            if (currentPageNum > 1) {
                html += `<button onclick="loadEvents(${currentPageNum - 1})" class="page-btn prev-btn">&lt;</button>`;
            }
            
            // Page numbers (show max 5 pages around current page)
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
            
            // Next button
            if (currentPageNum < totalPages) {
                html += `<button onclick="loadEvents(${currentPageNum + 1})" class="page-btn next-btn">&gt;</button>`;
            }
            
            paginationDiv.innerHTML = html;
        }

        function viewEventDetails(eventID) {
            fetch(`api/get_event_details.php?eventID=${encodeURIComponent(eventID)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showEventDetailsModal(data.event);
                    } else {
                        alert('Error loading event details: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading event details');
                });
        }

        function showEventDetailsModal(event) {
            const modal = document.getElementById('eventDetailsModal');
            const body = document.getElementById('eventDetailsBody');
            
            const status = getEventStatus(event.startdate, event.enddate, event.eventStatus);
            const statusClass = getStatusClass(status);
            const meritStatus = getMeritStatus(event.meritApplication);
            const meritClass = getMeritClass(meritStatus);
            
            body.innerHTML = `
                <div class="event-details">
                    <div class="detail-row">
                        <label>Event ID:</label>
                        <span>${escapeHtml(event.eventID)}</span>
                    </div>
                    <div class="detail-row">
                        <label>Event Name:</label>
                        <span>${escapeHtml(event.eventName)}</span>
                    </div>
                    <div class="detail-row">
                        <label>Description:</label>
                        <span>${escapeHtml(event.description || 'No description')}</span>
                    </div>
                    <div class="detail-row">
                        <label>Start Date:</label>
                        <span>${formatDate(event.startdate)}</span>
                    </div>
                    <div class="detail-row">
                        <label>End Date:</label>
                        <span>${formatDate(event.enddate)}</span>
                    </div>
                    <div class="detail-row">
                        <label>Event Level:</label>
                        <span>${escapeHtml(event.eventLevel)}</span>
                    </div>
                    <div class="detail-row">
                        <label>Location:</label>
                        <span>${escapeHtml(event.eventLocation)}</span>
                    </div>
                    <div class="detail-row">
                        <label>Status:</label>
                        <span class="status-badge ${statusClass}">${status}</span>
                    </div>
                    <div class="detail-row">
                        <label>Merit Status:</label>
                        <span class="merit-badge ${meritClass}">${meritStatus}</span>
                    </div>
                    ${event.approvalLetter ? `
                    <div class="detail-row">
                        <label>Approval Letter:</label>
                        <a href="api/uploads/${event.approvalLetter}" target="_blank" class="file-link">View Document</a>
                    </div>
                    ` : ''}
                </div>
            `;
            
            modal.style.display = 'flex';
        }

        function closeEventDetailsModal() {
            document.getElementById('eventDetailsModal').style.display = 'none';
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
                        alert('Event deleted successfully!');
                        loadEvents(currentPage);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the event');
                });
            }
        }

        function generateQR(eventID, eventName) {
            // Find the button that was clicked
            const button = document.querySelector(`[data-event-id="${eventID}"]`);
            if (!button) return;

            // Show loading state
            const originalIcon = button.querySelector('.action-icon').textContent;
            button.querySelector('.action-icon').textContent = '⏳';
            button.disabled = true;

            fetch(`api/generate_qr.php?eventID=${encodeURIComponent(eventID)}`)
                .then(response => response.json())
                .then(data => {
                    console.log('QR Generation Response:', data);
                    
                    if (data.success) {
                        currentQRData = data;
                        showQRModal(data);
                        
                        // Update the button icon to show QR is generated
                        button.querySelector('.action-icon').textContent = '✅';
                        button.title = 'QR Code Generated - Click to View/Download';
                        
                        // Show success message
                        if (!data.isExisting) {
                            showNotification('QR Code generated successfully!', 'success');
                        }
                    } else {
                        console.error('QR Generation Failed:', data.message);
                        alert('Error generating QR code: ' + data.message);
                        
                        // Restore original icon
                        button.querySelector('.action-icon').textContent = originalIcon;
                    }
                })
                .catch(error => {
                    console.error('QR Generation Error:', error);
                    alert('An error occurred while generating the QR code');
                    
                    // Restore original icon
                    button.querySelector('.action-icon').textContent = originalIcon;
                })
                .finally(() => {
                    button.disabled = false;
                });
        }

        function showQRModal(qrData) {
            const modal = document.getElementById('qrCodeModal');
            const body = document.getElementById('qrCodeBody');
            const downloadBtn = document.getElementById('downloadQRBtn');
            const copyBtn = document.getElementById('copyUrlBtn');
            
            // Update modal header
            const headerTitle = modal.querySelector('.modal-header h3');
            headerTitle.textContent = `QR Code for ${qrData.eventName}`;
            
            // Generate QR image URL
            const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&format=png&margin=10&data=${encodeURIComponent(qrData.qrUrl)}`;
            
            body.innerHTML = `
                <div class="qr-info">
                    <div class="qr-details">
                        <h4>${escapeHtml(qrData.eventName)}</h4>
                        <p><strong>Event ID:</strong> ${escapeHtml(qrData.eventID)}</p>
                        <p><strong>QR URL:</strong> <a href="${qrData.qrUrl}" target="_blank" class="qr-url-link">${qrData.qrUrl}</a></p>
                    </div>
                    
                    <div class="qr-code-display">
                        <div class="qr-image-container">
                            <img src="${qrImageUrl}" alt="QR Code for ${escapeHtml(qrData.eventName)}" class="qr-image" onload="onQRImageLoad()" onerror="onQRImageError()">
                            <div class="qr-loading" id="qrLoading">
                                <p>🔄 Loading QR Code...</p>
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
            
            modal.style.display = 'flex';
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
                loading.innerHTML = '<p>❌ Failed to load QR Code image</p>';
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
                
                showNotification('QR Code download started!', 'success');
            } else {
                alert('No QR code available for download');
            }
        }

        function copyQRUrl() {
            if (currentQRData && currentQRData.qrUrl) {
                navigator.clipboard.writeText(currentQRData.qrUrl).then(() => {
                    showNotification('QR URL copied to clipboard!', 'success');
                }).catch(err => {
                    console.error('Failed to copy URL:', err);
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = currentQRData.qrUrl;
                    document.body.appendChild(textArea);
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        showNotification('QR URL copied to clipboard!', 'success');
                    } catch (err) {
                        alert('Failed to copy URL');
                    }
                    document.body.removeChild(textArea);
                });
            } else {
                alert('No QR URL available to copy');
            }
        }

        function closeQRModal() {
            document.getElementById('qrCodeModal').style.display = 'none';
            currentQRData = null;
        }

        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            
            // Style the notification
            Object.assign(notification.style, {
                position: 'fixed',
                top: '20px',
                right: '20px',
                padding: '12px 20px',
                borderRadius: '6px',
                color: 'white',
                zIndex: '10000',
                fontSize: '14px',
                fontWeight: 'bold',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                backgroundColor: type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'
            });
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 3000);
        }

        // Close modals when clicking outside
        document.getElementById('eventDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEventDetailsModal();
            }
        });

        document.getElementById('qrCodeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQRModal();
            }
        });

        // Add keyboard support for closing modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEventDetailsModal();
                closeQRModal();
            }
        });
    </script>
</body>
</html>