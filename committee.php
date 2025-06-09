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

// Fetch events created by the current staff
$eventsQuery = "SELECT eventID, eventName FROM event WHERE staffID = ? ORDER BY eventName";
$eventsStmt = $data->prepare($eventsQuery);
$eventsStmt->bind_param("s", $currentStaffID);
$eventsStmt->execute();
$eventsResult = $eventsStmt->get_result();
$events = [];
while ($row = $eventsResult->fetch_assoc()) {
    $events[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MyPetakom Committee Registration">
    <meta name="author" content="Wardah Wafin">
    <title>MyPetakom - Register Committee</title>
    
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

        .form-control:focus, .form-select:focus {
            border-color: #a90000;
            box-shadow: 0 0 0 0.25rem rgba(169, 0, 0, 0.25);
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

        /* Position badges */
        .position-badge {
            background-color: #a90000;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            min-width: 80px;
            text-align: center;
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
            margin: 0 2px;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
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

        /* Clear button */
        .clear-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            display: none;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }

        .clear-btn:hover {
            background: #495057;
        }

        /* Search highlight */
        .search-highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
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
                                <i class="bi bi-people-fill me-3"></i>Register Committee
                            </h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 text-primary">
                                <i class="bi bi-person-plus me-2"></i>Committee Registration
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="committeeForm">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="selectEvent" class="form-label">
                                            <i class="bi bi-calendar-event me-1"></i>Select Event <span class="text-danger">*</span>
                                        </label>
                                        <select id="selectEvent" name="eventID" class="form-select" required>
                                            <option value="">Choose an event...</option>
                                            <?php foreach ($events as $event): ?>
                                                <option value="<?php echo htmlspecialchars($event['eventID']); ?>">
                                                    <?php echo htmlspecialchars($event['eventName']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="studentID" class="form-label">
                                            <i class="bi bi-person-badge me-1"></i>Student ID <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="studentID" name="studentID" class="form-control" 
                                               placeholder="Enter Student ID" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="position" class="form-label">
                                            <i class="bi bi-award me-1"></i>Position/Role <span class="text-danger">*</span>
                                        </label>
                                        <select id="position" name="committeePosition" class="form-select" required>
                                            <option value="">Choose position...</option>
                                            <option value="Director">Director</option>
                                            <option value="Vice Director">Vice Director</option>
                                            <option value="Secretary">Secretary</option>
                                            <option value="Vice Secretary">Vice Secretary</option>
                                            <option value="Treasurer">Treasurer</option>
                                            <option value="Vice Treasurer">Vice Treasurer</option>
                                            <option value="Head of Department">Head of Department</option>
                                            <option value="Activity Committee">Activity Committee</option>
                                            <option value="Volunteer">Volunteer</option>
                                            <option value="Member">Member</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='advisor_dash.php'">
                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>Register Committee
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Committee Members List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary">
                                <i class="bi bi-people me-2"></i>Committee Members
                            </h5>
                            <button class="btn btn-outline-primary btn-sm" onclick="loadCommitteeMembers()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Search and Filter Controls -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="filterEvent" class="form-label">Filter by Event:</label>
                                    <select id="filterEvent" class="form-select">
                                        <option value="">All Events</option>
                                        <?php foreach ($events as $event): ?>
                                            <option value="<?php echo htmlspecialchars($event['eventID']); ?>">
                                                <?php echo htmlspecialchars($event['eventName']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="searchName" class="form-label">Search:</label>
                                    <div class="position-relative">
                                        <input type="text" id="searchName" class="form-control" 
                                               placeholder="Search Name/ID..." />
                                        <button type="button" id="clearSearch" class="clear-btn" title="Clear search">×</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Results summary -->
                            <div id="resultsInfo" class="mb-3">
                                <small class="text-muted"></small>
                            </div>
                            
                            <!-- Committee Members Table -->
                            <div class="table-responsive">
                                <table class="table table-custom" id="committeeTable">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-person me-1"></i>Student Name</th>
                                            <th><i class="bi bi-person-badge me-1"></i>Student ID</th>
                                            <th><i class="bi bi-award me-1"></i>Position</th>
                                            <th><i class="bi bi-calendar-event me-1"></i>Event</th>
                                            <th class="text-center"><i class="bi bi-gear me-1"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="committeeList">
                                        <!-- Committee members will be loaded here via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Committee Modal -->
    <div class="modal fade" id="editCommitteeModal" tabindex="-1" aria-labelledby="editCommitteeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCommitteeModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Edit Committee Member
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editModalBody">
                    <!-- Modal content will be populated here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="updateCommitteeBtn">
                        <i class="bi bi-check-circle me-1"></i>Update Position
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let allCommitteeMembers = []; // Store all committee members for client-side filtering
        let currentEventFilter = '';
        let currentSearchTerm = '';
        let editCommitteeModal;
        let currentEditingCommitteeID = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap modal
            editCommitteeModal = new bootstrap.Modal(document.getElementById('editCommitteeModal'));
            
            setupFormValidation();
            loadCommitteeMembers();
            setupEventFilter();
            setupSearchFunctionality();
            setupModalHandlers();
        });

        function setupFormValidation() {
            const form = document.getElementById('committeeForm');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (validateForm()) {
                    submitForm();
                }
            });
        }

        function validateForm() {
            const requiredFields = ['selectEvent', 'studentID', 'position'];
            let isValid = true;

            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field || !field.value.trim()) {
                    if (field) {
                        field.classList.add('is-invalid');
                    }
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }
            });

            return isValid;
        }

        function submitForm() {
            const eventID = document.getElementById('selectEvent').value;
            const studentID = document.getElementById('studentID').value;
            const position = document.getElementById('position').value;

            const formData = new FormData();
            formData.append('eventID', eventID);
            formData.append('studentID', studentID);
            formData.append('committeePosition', position);

            // Show loading
            const submitBtn = document.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Registering...';
            submitBtn.disabled = true;

            fetch('api/save_committee.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Committee member registered successfully!', 'success');
                    document.getElementById('committeeForm').reset();
                    
                    // Remove validation classes
                    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
                        el.classList.remove('is-valid', 'is-invalid');
                    });
                    
                    loadCommitteeMembers();
                } else {
                    showAlert('Error: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while registering committee member', 'danger');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

        function loadCommitteeMembers() {
            fetch('api/get_committee.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allCommitteeMembers = data.committee;
                        applyFilters();
                    } else {
                        allCommitteeMembers = [];
                        displayCommitteeMembers([]);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('committeeList').innerHTML = 
                        '<tr><td colspan="5" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle me-2"></i>Error loading committee members</td></tr>';
                    updateResultsInfo(0);
                });
        }

        function applyFilters() {
            let filteredMembers = [...allCommitteeMembers];
            
            // Apply event filter
            if (currentEventFilter) {
                filteredMembers = filteredMembers.filter(member => 
                    member.eventID === currentEventFilter
                );
            }
            
            // Apply search filter
            if (currentSearchTerm) {
                const searchLower = currentSearchTerm.toLowerCase();
                filteredMembers = filteredMembers.filter(member =>
                    member.studentName.toLowerCase().includes(searchLower) ||
                    member.studentID.toLowerCase().includes(searchLower)
                );
            }
            
            displayCommitteeMembers(filteredMembers);
            updateResultsInfo(filteredMembers.length);
        }

        function displayCommitteeMembers(committee) {
            const tbody = document.getElementById('committeeList');
            
            if (committee.length === 0) {
                const noResultsMessage = currentSearchTerm || currentEventFilter ? 
                    'No committee members found matching your criteria.' : 
                    'No committee members found.';
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox me-2"></i>${noResultsMessage}</td></tr>`;
                return;
            }

            let html = '';
            committee.forEach(member => {
                // Highlight search terms
                const highlightedName = highlightSearchTerm(member.studentName, currentSearchTerm);
                const highlightedID = highlightSearchTerm(member.studentID, currentSearchTerm);
                
                html += `
                    <tr>
                        <td class="fw-bold">${highlightedName}</td>
                        <td><code>${highlightedID}</code></td>
                        <td class="text-center">
                            <span class="position-badge">${escapeHtml(member.committeePosition)}</span>
                        </td>
                        <td>${escapeHtml(member.eventName)}</td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button onclick="editCommittee(${member.committeeID})" class="action-btn btn-edit" title="Edit Position">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="deleteCommittee(${member.committeeID})" class="action-btn btn-delete" title="Remove Member">
                                    <i class="bi bi-trash"></i>
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
            
            const regex = new RegExp(`(${escapedSearchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return escapedText.replace(regex, '<mark class="search-highlight">$1</mark>');
        }

        function updateResultsInfo(count) {
            const resultsInfo = document.getElementById('resultsInfo').querySelector('small');
            if (count === 0) {
                resultsInfo.textContent = '';
            } else {
                const totalMembers = allCommitteeMembers.length;
                if (currentSearchTerm || currentEventFilter) {
                    resultsInfo.textContent = `Showing ${count} of ${totalMembers} committee members`;
                    resultsInfo.className = 'text-info';
                } else {
                    resultsInfo.textContent = `Total: ${count} committee members`;
                    resultsInfo.className = 'text-muted';
                }
            }
        }

        function setupEventFilter() {
            const filterSelect = document.getElementById('filterEvent');
            filterSelect.addEventListener('change', function() {
                currentEventFilter = this.value;
                applyFilters();
            });
        }

        function setupSearchFunctionality() {
            const searchInput = document.getElementById('searchName');
            const clearBtn = document.getElementById('clearSearch');
            
            searchInput.addEventListener('input', function() {
                currentSearchTerm = this.value.trim();
                clearBtn.style.display = currentSearchTerm ? 'flex' : 'none';
                applyFilters();
            });
            
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                currentSearchTerm = '';
                this.style.display = 'none';
                searchInput.focus();
                applyFilters();
            });
            
            clearBtn.style.display = 'none';
        }

        function setupModalHandlers() {
            document.getElementById('updateCommitteeBtn').addEventListener('click', function() {
                updateCommittee();
            });
        }

        function editCommittee(committeeID) {
            const url = `api/get_committee_member.php?committeeID=${encodeURIComponent(committeeID)}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showEditModal(data.member);
                    } else {
                        showAlert('Error loading committee member: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showAlert('Error loading committee member details', 'danger');
                });
        }

        function showEditModal(member) {
            currentEditingCommitteeID = member.committeeID;
            
            const modalBody = document.getElementById('editModalBody');
            modalBody.innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Committee ID:</label>
                    <input type="text" value="${member.committeeID}" readonly class="form-control-plaintext border rounded px-3 py-2 bg-light">
                </div>
                <div class="mb-3">
                    <label class="form-label">Student:</label>
                    <input type="text" value="${escapeHtml(member.studentName)} (${escapeHtml(member.studentID)})" readonly class="form-control-plaintext border rounded px-3 py-2 bg-light">
                </div>
                <div class="mb-3">
                    <label class="form-label">Event:</label>
                    <input type="text" value="${escapeHtml(member.eventName)}" readonly class="form-control-plaintext border rounded px-3 py-2 bg-light">
                </div>
                <div class="mb-3">
                    <label for="editPosition" class="form-label">Position/Role <span class="text-danger">*</span></label>
                    <select id="editPosition" class="form-select" required>
                        <option value="Director" ${member.committeePosition === 'Director' ? 'selected' : ''}>Director</option>
                        <option value="Vice Director" ${member.committeePosition === 'Vice Director' ? 'selected' : ''}>Vice Director</option>
                        <option value="Secretary" ${member.committeePosition === 'Secretary' ? 'selected' : ''}>Secretary</option>
                        <option value="Vice Secretary" ${member.committeePosition === 'Vice Secretary' ? 'selected' : ''}>Vice Secretary</option>
                        <option value="Treasurer" ${member.committeePosition === 'Treasurer' ? 'selected' : ''}>Treasurer</option>
                        <option value="Vice Treasurer" ${member.committeePosition === 'Vice Treasurer' ? 'selected' : ''}>Vice Treasurer</option>
                        <option value="Head of Department" ${member.committeePosition === 'Head of Department' ? 'selected' : ''}>Head of Department</option>
                        <option value="Activity Committee" ${member.committeePosition === 'Activity Committee' ? 'selected' : ''}>Activity Committee</option>
                        <option value="Volunteer" ${member.committeePosition === 'Volunteer' ? 'selected' : ''}>Volunteer</option>
                        <option value="Member" ${member.committeePosition === 'Member' ? 'selected' : ''}>Member</option>
                    </select>
                </div>
            `;
            
            editCommitteeModal.show();
        }

        function updateCommittee() {
            const newPosition = document.getElementById('editPosition').value;
            
            if (!newPosition) {
                showAlert('Please select a position', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('committeeID', currentEditingCommitteeID);
            formData.append('newPosition', newPosition);

            // Show loading
            const updateBtn = document.getElementById('updateCommitteeBtn');
            const originalText = updateBtn.innerHTML;
            updateBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Updating...';
            updateBtn.disabled = true;

            fetch('api/update_committee.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Committee member position updated successfully!', 'success');
                    editCommitteeModal.hide();
                    loadCommitteeMembers();
                } else {
                    showAlert('Error: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while updating committee member', 'danger');
            })
            .finally(() => {
                updateBtn.innerHTML = originalText;
                updateBtn.disabled = false;
            });
        }

        function deleteCommittee(committeeID) {
            if (confirm('Are you sure you want to remove this committee member?')) {
                fetch('api/delete_committee.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({committeeID: committeeID})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Committee member removed successfully!', 'success');
                        loadCommitteeMembers();
                    } else {
                        showAlert('Error: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while removing committee member', 'danger');
                });
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
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
    </script>
</body>
</html>