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
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="description" content="MyPetakom Register Event">
    <meta name="author" content="Wardah Wafin">
    <title>MyPetakom - Register New Event</title>
    <link rel="stylesheet" href="style/event_registration.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        /* Additional styles for PDF management */
        .file-preview {
            margin-top: 15px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f9f9f9;
            display: none;
        }

        .file-preview.active {
            display: block;
        }

        .file-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .file-details {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .file-icon {
            width: 32px;
            height: 32px;
            background: #dc3545;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }

        .file-meta {
            color: #666;
            font-size: 0.9em;
        }

        .file-actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: background-color 0.2s;
        }

        .view-btn {
            background: #007bff;
            color: white;
        }

        .view-btn:hover {
            background: #0056b3;
        }

        .edit-btn {
            background: #28a745;
            color: white;
        }

        .edit-btn:hover {
            background: #1e7e34;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .replace-btn {
            background: #ffc107;
            color: #212529;
        }

        .replace-btn:hover {
            background: #e0a800;
        }

        /* PDF Viewer Modal */
        .pdf-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }

        .pdf-modal.active {
            display: block;
        }

        .pdf-modal-content {
            position: relative;
            margin: 2% auto;
            width: 90%;
            height: 90%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .pdf-modal-header {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .close-modal:hover {
            color: #000;
        }

        .pdf-viewer {
            width: 100%;
            height: calc(100% - 60px);
            border: none;
        }

        /* File size formatting */
        .file-size {
            color: #888;
            font-size: 0.8em;
        }

        /* Upload area states */
        .file-upload-area.has-file {
            border-color: #28a745;
            background-color: #f8fff9;
        }

        .upload-success-text {
            color: #28a745;
            font-weight: 500;
        }

        /* Update button styling for edit mode */
        .submit-btn[data-editing="true"] {
            background: linear-gradient(135deg, #28a745, #20c997);
            border-color: #28a745;
        }

        .submit-btn[data-editing="true"]:hover {
            background: linear-gradient(135deg, #218838, #1aa085);
            border-color: #1e7e34;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }

        /* Readonly input styling */
        input[readonly] {
            background-color: #f8f9fa !important;
            cursor: not-allowed !important;
            color: #6c757d;
        }

        input[readonly]:focus {
            border-color: #ced4da;
            box-shadow: none;
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
        <h1>Register New Event</h1>
        
        <form id="eventForm" class="event-form">
            <div class="form-section">
                <h2>Event Details</h2>
                
                <div class="form-group">
                    <label for="eventID">Event ID *</label>
                    <input type="text" id="eventID" name="eventID" maxlength="10" required>
                </div>

                <div class="form-group">
                    <label for="eventName">Event Name *</label>
                    <input type="text" id="eventName" name="eventName" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="eventDescription">Event Description</label>
                    <textarea id="eventDescription" name="eventDescription" rows="4" maxlength="200"></textarea>
                </div>

                <div class="form-group">
                    <label for="startDate">Start Date *</label>
                    <input type="date" id="startDate" name="startDate" required>
                </div>

                <div class="form-group">
                    <label for="endDate">End Date *</label>
                    <input type="date" id="endDate" name="endDate" required>
                </div>

                <div class="form-group">
                    <label for="eventLevel">Event Level *</label>
                    <select id="eventLevel" name="eventLevel" required>
                        <option value="">Select Event Level</option>
                        <option value="international">International</option>
                        <option value="national">National</option>
                        <option value="state">State</option>
                        <option value="district">District</option>
                        <option value="umpsa">UMPSA</option>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h2>Location Details</h2>
                
                <div class="form-group">
                    <label for="locationName">Location Name *</label>
                    <input type="text" id="locationName" name="locationName" placeholder="e.g., Main Hall, UMPSA" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label>Geolocation (Pin the exact location on the map)</label>
                    <div id="map" class="map-container"></div>
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    <div class="coordinates-display">
                        <span id="coordinatesText">Click on the map to set location</span>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Documents</h2>
                
                <div class="form-group">
                    <label for="approvalLetter">Approval Letter (PDF)</label>
                    <div class="file-upload-area" id="approvalUploadArea">
                        <div class="upload-icon"><img src="images/cloud.png" alt="Upload"></div>
                        <div class="upload-text">Click to upload or drag and drop</div>
                        <div class="upload-subtext">PDF only, max 5MB</div>
                        <input type="file" id="approvalLetter" name="approvalLetter" accept=".pdf" style="display: none;">
                    </div>
                    
                    <!-- File Preview Section -->
                    <div id="filePreview" class="file-preview">
                        <div class="file-info">
                            <div class="file-details">
                                <div class="file-icon">PDF</div>
                                <div>
                                    <div id="fileName" class="file-name"></div>
                                    <div id="fileSize" class="file-size"></div>
                                </div>
                            </div>
                            <div class="file-actions">
                                <button type="button" class="action-btn view-btn" id="viewBtn">
                                    View
                                </button>
                                <button type="button" class="action-btn replace-btn" id="replaceBtn">
                                    Replace
                                </button>
                                <button type="button" class="action-btn delete-btn" id="deleteBtn">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Merit Application</h2>
                <div class="form-group">
                    <label for="applyMerit" class="simple-checkbox-label">
                        <input type="checkbox" id="applyMerit" name="applyMerit" class="simple-checkbox">
                        Apply for Merit for this event
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="window.location.href='advisor_dash.php'">Cancel</button>
                <button type="submit" class="submit-btn">SUBMIT</button>
            </div>
        </form>
    </div>

    <!-- PDF Viewer Modal -->
    <div id="pdfModal" class="pdf-modal">
        <div class="pdf-modal-content">
            <div class="pdf-modal-header">
                <h3 id="modalTitle">Approval Letter</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <iframe id="pdfViewer" class="pdf-viewer"></iframe>
        </div>
    </div>

    <script>
        let map;
        let marker;
        let selectedLocation = null;
        let currentFile = null;
        let currentFileBlob = null;
        let isEditMode = false;

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            initializeMap();
            setupFileUpload();
            setupMeritToggle();
            setupFormValidation();
            setupPDFViewer();
            
            // Check if editing existing event
            const urlParams = new URLSearchParams(window.location.search);
            const editId = urlParams.get('edit');
            if (editId) {
                loadEventForEdit(editId);
            }
        });

        // Initialize Leaflet Map
        function initializeMap() {
            // Default to UMPSA location (Pekan, Pahang)
            const defaultLocation = [3.5516, 103.4244];
            
            // Initialize map
            map = L.map('map', {
                center: defaultLocation,
                zoom: 16,
                zoomControl: true
            });

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Add click listener to map
            map.on('click', function(e) {
                setMarker(e.latlng);
            });

            // Try to get user's current location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLocation = [position.coords.latitude, position.coords.longitude];
                        map.setView(userLocation, 16);
                    },
                    function(error) {
                        console.log('Geolocation error:', error);
                        // Keep default location if geolocation fails
                    }
                );
            }
        }

        // Set marker on map
        function setMarker(latlng) {
            // Remove existing marker
            if (marker) {
                map.removeLayer(marker);
            }

            // Add new marker
            marker = L.marker(latlng, {
                draggable: true
            }).addTo(map);

            selectedLocation = latlng;
            document.getElementById('latitude').value = latlng.lat.toFixed(6);
            document.getElementById('longitude').value = latlng.lng.toFixed(6);
            document.getElementById('coordinatesText').textContent = 
                `Coordinates: ${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`;

            // Add drag listener to marker
            marker.on('dragend', function(e) {
                const newLatLng = e.target.getLatLng();
                selectedLocation = newLatLng;
                document.getElementById('latitude').value = newLatLng.lat.toFixed(6);
                document.getElementById('longitude').value = newLatLng.lng.toFixed(6);
                document.getElementById('coordinatesText').textContent = 
                    `Coordinates: ${newLatLng.lat.toFixed(6)}, ${newLatLng.lng.toFixed(6)}`;
            });

            // Add popup to marker
            marker.bindPopup(`
                <b>Event Location</b><br>
                Lat: ${latlng.lat.toFixed(6)}<br>
                Lng: ${latlng.lng.toFixed(6)}
            `).openPopup();
        }

        // Setup file upload with enhanced features
        function setupFileUpload() {
            const uploadArea = document.getElementById('approvalUploadArea');
            const fileInput = document.getElementById('approvalLetter');
            const filePreview = document.getElementById('filePreview');

            // Click to upload
            uploadArea.addEventListener('click', () => fileInput.click());

            // Drag and drop
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.style.backgroundColor = '#f0f0f0';
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.style.backgroundColor = '';
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.style.backgroundColor = '';
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileUpload(files[0]);
                }
            });

            // File input change
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFileUpload(e.target.files[0]);
                }
            });

            // Setup action buttons
            document.getElementById('viewBtn').addEventListener('click', viewPDF);
            document.getElementById('replaceBtn').addEventListener('click', () => fileInput.click());
            document.getElementById('deleteBtn').addEventListener('click', deletePDF);
        }

        // Handle file upload
        function handleFileUpload(file) {
            if (file.type !== 'application/pdf') {
                alert('Please upload only PDF files');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                return;
            }

            currentFile = file;
            currentFileBlob = URL.createObjectURL(file);
            
            // Update UI
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = formatFileSize(file.size);
            document.getElementById('filePreview').classList.add('active');
            document.getElementById('approvalUploadArea').classList.add('has-file');
            
            // Update upload area text
            const uploadText = document.querySelector('.upload-text');
            uploadText.textContent = 'File uploaded successfully!';
            uploadText.classList.add('upload-success-text');
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // View PDF
        function viewPDF() {
            if (!currentFileBlob) {
                alert('No PDF file to view');
                return;
            }
            
            const modal = document.getElementById('pdfModal');
            const viewer = document.getElementById('pdfViewer');
            const title = document.getElementById('modalTitle');
            
            title.textContent = currentFile.name;
            viewer.src = currentFileBlob;
            modal.classList.add('active');
        }

        // Delete PDF
        function deletePDF() {
            if (confirm('Are you sure you want to delete this file?')) {
                currentFile = null;
                if (currentFileBlob) {
                    URL.revokeObjectURL(currentFileBlob);
                    currentFileBlob = null;
                }
                
                // Reset UI
                document.getElementById('filePreview').classList.remove('active');
                document.getElementById('approvalUploadArea').classList.remove('has-file');
                document.getElementById('approvalLetter').value = '';
                
                // Reset upload area text
                const uploadText = document.querySelector('.upload-text');
                uploadText.textContent = 'Click to upload or drag and drop';
                uploadText.classList.remove('upload-success-text');
            }
        }

        // Setup PDF viewer modal
        function setupPDFViewer() {
            const modal = document.getElementById('pdfModal');
            const closeBtn = document.getElementById('closeModal');
            
            closeBtn.addEventListener('click', () => {
                modal.classList.remove('active');
                document.getElementById('pdfViewer').src = '';
            });
            
            // Close modal when clicking outside
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                    document.getElementById('pdfViewer').src = '';
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    modal.classList.remove('active');
                    document.getElementById('pdfViewer').src = '';
                }
            });
        }

        // Setup merit toggle
        function setupMeritToggle() {
            // Merit toggle functionality removed - now just a simple checkbox
        }

        // Setup form validation
        function setupFormValidation() {
            const form = document.getElementById('eventForm');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (validateForm()) {
                    submitForm();
                }
            });
        }

        // Validate form
        function validateForm() {
            const requiredFields = ['eventID', 'eventName', 'startDate', 'endDate', 'locationName', 'eventLevel'];
            let isValid = true;

            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    field.style.borderColor = '';
                }
            });

            if (!selectedLocation) {
                alert('Please select a location on the map');
                isValid = false;
            }

            // Validate start date is not in the past (only for new events)
            if (!isEditMode) {
                const startDate = new Date(document.getElementById('startDate').value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (startDate < today) {
                    alert('Start date cannot be in the past');
                    document.getElementById('startDate').style.borderColor = '#dc3545';
                    isValid = false;
                }
            }

            // Validate end date is not before start date
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);
            
            if (endDate < startDate) {
                alert('End date cannot be before start date');
                document.getElementById('endDate').style.borderColor = '#dc3545';
                isValid = false;
            }

            return isValid;
        }

        // Submit form
        function submitForm() {
            const formData = new FormData(document.getElementById('eventForm'));
            
            // Add coordinates
            formData.append('latitude', document.getElementById('latitude').value);
            formData.append('longitude', document.getElementById('longitude').value);

            // Add the actual file if it exists
            if (currentFile) {
                formData.set('approvalLetter', currentFile);
            }

            // Show loading with appropriate text
            const submitBtn = document.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;
            const loadingText = isEditMode ? 'Updating...' : 'Submitting...';
            const successMessage = isEditMode ? 'Event updated successfully!' : 'Event registered successfully!';
            
            submitBtn.textContent = loadingText;
            submitBtn.disabled = true;

            fetch('api/save_event.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(successMessage);
                    window.location.href = 'advisor_dash.php';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMessage = isEditMode ? 'An error occurred while updating the event' : 'An error occurred while saving the event';
                alert(errorMessage);
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        }

        // Load event for editing
        function loadEventForEdit(eventId) {
            isEditMode = true; // Set edit mode flag
            
            fetch(`api/get_event.php?id=${eventId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateForm(data.event);
                        
                        // Update page title and submit button for edit mode
                        document.querySelector('h1').textContent = 'Edit Event';
                        const submitBtn = document.querySelector('.submit-btn');
                        submitBtn.textContent = 'UPDATE';
                        submitBtn.setAttribute('data-editing', 'true');
                        
                        // Make eventID field readonly during editing
                        document.getElementById('eventID').readOnly = true;
                        document.getElementById('eventID').style.backgroundColor = '#f8f9fa';
                        document.getElementById('eventID').style.cursor = 'not-allowed';
                        
                    } else {
                        alert('Error loading event: ' + data.message);
                        window.location.href = 'advisor_dash.php';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading event');
                });
        }

        // Populate form with existing data
        function populateForm(event) {
            document.getElementById('eventID').value = event.eventID || '';
            document.getElementById('eventName').value = event.eventName || '';
            document.getElementById('eventDescription').value = event.description || '';
            document.getElementById('startDate').value = event.startdate || '';
            document.getElementById('endDate').value = event.enddate || '';
            document.getElementById('eventLevel').value = event.eventLevel || '';
            document.getElementById('locationName').value = event.eventLocation || '';
            
            if (event.latitude && event.longitude) {
                const latlng = L.latLng(parseFloat(event.latitude), parseFloat(event.longitude));
                setMarker(latlng);
                map.setView(latlng, 16);
            }
            
            if (event.meritApplication == 1) {
                document.getElementById('applyMerit').checked = true;
            }

            // Load existing PDF if available
            if (event.approvalLetter) {
                loadExistingPDF('uploads/' + event.approvalLetter, event.approvalLetter);
            }
        }

        // Load existing PDF for editing
        function loadExistingPDF(url, filename) {
            fetch(url)
                .then(response => response.blob())
                .then(blob => {
                    currentFileBlob = URL.createObjectURL(blob);
                    
                    // Create a File object for consistency
                    const file = new File([blob], filename, { type: 'application/pdf' });
                    currentFile = file;
                    
                    // Update UI
                    document.getElementById('fileName').textContent = filename;
                    document.getElementById('fileSize').textContent = formatFileSize(blob.size);
                    document.getElementById('filePreview').classList.add('active');
                    document.getElementById('approvalUploadArea').classList.add('has-file');
                    
                    const uploadText = document.querySelector('.upload-text');
                    uploadText.textContent = 'File loaded successfully!';
                    uploadText.classList.add('upload-success-text');
                })
                .catch(error => {
                    console.error('Error loading existing PDF:', error);
                });
        }

        // Cleanup blob URLs when page unloads
        window.addEventListener('beforeunload', () => {
            if (currentFileBlob) {
                URL.revokeObjectURL(currentFileBlob);
            }
        });
    </script>
</body>
</html>