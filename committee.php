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
<html>
<head>
    <meta name="description" content="MyPetakom Committee Registration">
    <meta name="author" content="Wardah Wafin">
    <title>MyPetakom - Register Committee</title>
    <link rel="stylesheet" href="style/committee.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <style>
        /* Additional styles for the modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: #333;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            color: #000;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .readonly-field {
            background-color: #f8f9fa !important;
            cursor: not-allowed !important;
            color: #6c757d;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .cancel-btn, .submit-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .cancel-btn {
            background-color: #6c757d;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #5a6268;
        }

        .submit-btn {
            background-color: #007bff;
            color: white;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }
    </style>
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
        <h1>Register Committee</h1>
        
        <form id="committeeForm" class="committee-form">
            <div class="form-section">
                <h2>Committee Details</h2>
                
                <div class="form-group">
                    <label for="selectEvent">Select Events</label>
                    <select id="selectEvent" name="eventID" required>
                        <option value="">Select Event</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?php echo htmlspecialchars($event['eventID']); ?>">
                                <?php echo htmlspecialchars($event['eventName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="studentID">Student ID</label>
                    <input type="text" id="studentID" name="studentID" placeholder="Enter Student ID" required>
                </div>

                <div class="form-group">
                    <label for="position">Position/Role</label>
                    <select id="position" name="committeePosition" required>
                        <option value="">Select Position</option>
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

            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="window.location.href='advisor_dash.php'">Cancel</button>
                <button type="submit" class="submit-btn">Register Committee</button>
            </div>
        </form>

        <!-- Committee Members List -->
        <div class="committee-list-section">
            <h2>Committee Members</h2>
            
            <!-- Search and Filter Controls -->
            <div class="search-filter-container">
                <div class="filter-group">
                    <label for="filterEvent">Filter by Event:</label>
                    <select id="filterEvent">
                        <option value="">All Events</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?php echo htmlspecialchars($event['eventID']); ?>">
                                <?php echo htmlspecialchars($event['eventName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="search-group">
                    <label for="searchName">Search:</label>
                    <input type="text" id="searchName" placeholder="Search Name/ID." />
                    <button type="button" id="clearSearch" class="clear-btn" title="Clear search">×</button>
                </div>
            </div>
            
            <!-- Results summary -->
            <div id="resultsInfo" class="results-info"></div>
            
            <div id="committeeList" class="committee-list">
                <!-- Committee members will be loaded here via JavaScript -->
            </div>
        </div>
    </div>

    <script>
        let allCommitteeMembers = []; // Store all committee members for client-side filtering
        let currentEventFilter = '';
        let currentSearchTerm = '';

        document.addEventListener('DOMContentLoaded', function() {
            setupFormValidation();
            loadCommitteeMembers();
            setupEventFilter();
            setupSearchFunctionality();
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
                    if (field) field.style.borderColor = '#dc3545';
                    isValid = false;
                    console.log('Missing field:', fieldId, field ? field.value : 'field not found');
                } else {
                    field.style.borderColor = '';
                }
            });

            return isValid;
        }

        function submitForm() {
            const eventID = document.getElementById('selectEvent').value;
            const studentID = document.getElementById('studentID').value;
            const position = document.getElementById('position').value;

            // Debug: Log the values being sent
            console.log('Form values:', {
                eventID: eventID,
                studentID: studentID,
                committeePosition: position
            });

            const formData = new FormData();
            formData.append('eventID', eventID);
            formData.append('studentID', studentID);
            formData.append('committeePosition', position);

            // Debug: Log FormData contents
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            // Show loading
            const submitBtn = document.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Registering...';
            submitBtn.disabled = true;

            fetch('api/save_committee.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data);
                if (data.success) {
                    alert('Committee member registered successfully!');
                    document.getElementById('committeeForm').reset();
                    
                    // Force reload the committee list
                    console.log('Reloading committee list...');
                    loadCommitteeMembers();
                    
                    // Also reload after a small delay to ensure database has been updated
                    setTimeout(() => {
                        console.log('Delayed reload of committee list...');
                        loadCommitteeMembers();
                    }, 1000);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while registering committee member');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        }

        function loadCommitteeMembers() {
            // Always load all committee members and then filter client-side
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
                    document.getElementById('committeeList').innerHTML = '<p class="error-message">Error loading committee members</p>';
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
            const listContainer = document.getElementById('committeeList');
            
            if (committee.length === 0) {
                const noResultsMessage = currentSearchTerm || currentEventFilter ? 
                    'No committee members found matching your criteria.' : 
                    'No committee members found.';
                listContainer.innerHTML = `<p class="no-members">${noResultsMessage}</p>`;
                return;
            }

            let html = `
                <div class="committee-table-container">
                    <table class="committee-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Position</th>
                                <th>Event</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            committee.forEach(member => {
                html += `
                    <tr class="committee-row">
                        <td class="student-name">${member.studentName}</td>
                        <td class="student-id">${member.studentID}</td>
                        <td class="position">
                            <span class="position-badge">${member.committeePosition}</span>
                        </td>
                        <td class="event-name">${member.eventName}</td>
                        <td class="actions">
                            <button onclick="editCommittee(${member.committeeID})" class="edit-btn">Edit</button>
                            <button onclick="deleteCommittee(${member.committeeID})" class="delete-btn">Delete</button>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            listContainer.innerHTML = html;
        }

        function updateResultsInfo(count) {
            const resultsInfo = document.getElementById('resultsInfo');
            if (count === 0) {
                resultsInfo.textContent = '';
            } else {
                const totalMembers = allCommitteeMembers.length;
                if (currentSearchTerm || currentEventFilter) {
                    resultsInfo.textContent = `Showing ${count} of ${totalMembers} committee members`;
                } else {
                    resultsInfo.textContent = `Total: ${count} committee members`;
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
            
            // Real-time search as user types
            searchInput.addEventListener('input', function() {
                currentSearchTerm = this.value.trim();
                clearBtn.style.display = currentSearchTerm ? 'block' : 'none';
                applyFilters();
            });
            
            // Clear search functionality
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                currentSearchTerm = '';
                this.style.display = 'none';
                searchInput.focus();
                applyFilters();
            });
            
            // Hide clear button initially
            clearBtn.style.display = 'none';
        }

        function editCommittee(committeeID) {
            console.log('Edit committee clicked for ID:', committeeID);
            
            // Fetch current committee member details
            const url = `api/get_committee_member.php?committeeID=${encodeURIComponent(committeeID)}`;
            console.log('Fetching URL:', url);
            
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        showEditModal(data.member);
                    } else {
                        alert('Error loading committee member: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error loading committee member details');
                });
        }

        function showEditModal(member) {
            // Create modal HTML
            const modalHTML = `
                <div id="editModal" class="modal-overlay">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Edit Committee Member</h3>
                            <button onclick="closeEditModal()" class="close-btn">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Committee ID:</label>
                                <input type="text" value="${member.committeeID}" readonly class="readonly-field">
                            </div>
                            <div class="form-group">
                                <label>Student Name:</label>
                                <input type="text" value="${member.studentName} (${member.studentID})" readonly class="readonly-field">
                            </div>
                            <div class="form-group">
                                <label>Event:</label>
                                <input type="text" value="${member.eventName}" readonly class="readonly-field">
                            </div>
                            <div class="form-group">
                                <label for="editPosition">Position/Role:</label>
                                <select id="editPosition" required>
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
                        </div>
                        <div class="modal-footer">
                            <button onclick="closeEditModal()" class="cancel-btn">Cancel</button>
                            <button onclick="updateCommittee(${member.committeeID})" class="submit-btn">Update Position</button>
                        </div>
                    </div>
                </div>
            `;

            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            if (modal) {
                modal.remove();
            }
        }

        function updateCommittee(committeeID) {
            const newPosition = document.getElementById('editPosition').value;
            
            if (!newPosition) {
                alert('Please select a position');
                return;
            }

            const formData = new FormData();
            formData.append('committeeID', committeeID);
            formData.append('newPosition', newPosition);

            fetch('api/update_committee.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Committee member position updated successfully!');
                    closeEditModal();
                    loadCommitteeMembers();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating committee member');
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
                        alert('Committee member removed successfully!');
                        loadCommitteeMembers();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing committee member');
                });
            }
        }
    </script>
</body>
</html>