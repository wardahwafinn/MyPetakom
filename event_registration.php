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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MyPetakom Register Event">
    <meta name="author" content="Wardah Wafin">
    <title>MyPetakom - Register New Event</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
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

        /* Custom form styling */
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* Map container */
        .map-container {
            height: 400px;
            border-radius: 8px;
            overflow: hidden;
        }

        /* File upload area */
        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }

        .file-upload-area:hover {
            border-color: #a90000;
            background-color: #fff;
        }

        .file-upload-area.has-file {
            border-color: #28a745;
            background-color: #f8fff9;
        }

        .upload-success-text {
            color: #28a745;
            font-weight: 500;
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

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .text-primary {
            color: #a90000 !important;
        }

        .border-primary {
            border-color: #a90000 !important;
        }

        /* PDF Modal custom styles */
        .pdf-modal {
            display: none;
            position: fixed;
            z-index: 1050;
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

        .pdf-viewer {
            width: 100%;
            height: calc(100% - 60px);
            border: none;
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

        /* Form section headers */
        .section-header {
            border-left: 4px solid #a90000;
            padding-left: 15px;
            margin-bottom: 1.5rem;
        }

        /* Custom file preview styles */
        .file-preview {
            display: none;
            margin-top: 15px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f9f9f9;
        }

        .file-preview.active {
            display: block;
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
        <div class="container-fluid p-4">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card form-container">
                        <div class="card-body text-center">
                            <h1 class="display-6 text-primary mb-0">Register New Event</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card form-container">
                        <div class="card-body p-4">
                            <form id="eventForm">
                                <!-- Event Details Section -->
                                <div class="mb-5">
                                    <h3 class="section-header text-secondary">
                                        <i class="bi bi-info-circle me-2"></i>Event Details
                                    </h3>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="eventID" class="form-label fw-semibold">
                                                Event ID <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="eventID" name="eventID" maxlength="10" required>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="eventLevel" class="form-label fw-semibold">
                                                Event Level <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="eventLevel" name="eventLevel" required>
                                                <option value="">Select Event Level</option>
                                                <option value="international">International</option>
                                                <option value="national">National</option>
                                                <option value="state">State</option>
                                                <option value="district">District</option>
                                                <option value="umpsa">UMPSA</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="eventName" class="form-label fw-semibold">
                                                Event Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="eventName" name="eventName" maxlength="100" required>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="eventDescription" class="form-label fw-semibold">Event Description</label>
                                            <textarea class="form-control" id="eventDescription" name="eventDescription" rows="4" maxlength="200"></textarea>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="startDate" class="form-label fw-semibold">
                                                Start Date <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="startDate" name="startDate" required>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="endDate" class="form-label fw-semibold">
                                                End Date <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="endDate" name="endDate" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Location Details Section -->
                                <div class="mb-5">
                                    <h3 class="section-header text-secondary">
                                        <i class="bi bi-geo-alt me-2"></i>Location Details
                                    </h3>
                                    
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="locationName" class="form-label fw-semibold">
                                                Location Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="locationName" name="locationName" 
                                                   placeholder="e.g., Main Hall, UMPSA" maxlength="100" required>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Geolocation (Pin the exact location on the map)</label>
                                            <div id="map" class="map-container border"></div>
                                            <input type="hidden" id="latitude" name="latitude">
                                            <input type="hidden" id="longitude" name="longitude">
                                            <div class="mt-2 p-2 bg-light rounded">
                                                <small class="text-muted" id="coordinatesText">
                                                    <i class="bi bi-cursor me-1"></i>Click on the map to set location
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Documents Section -->
                                <div class="mb-5">
                                    <h3 class="section-header text-secondary">
                                        <i class="bi bi-file-earmark-pdf me-2"></i>Documents
                                    </h3>
                                    
                                    <div class="col-12">
                                        <label for="approvalLetter" class="form-label fw-semibold">Approval Letter (PDF)</label>
                                        
                                        <div class="file-upload-area" id="approvalUploadArea">
                                            <div class="upload-icon mb-3">
                                                <i class="bi bi-cloud-upload display-3 text-muted"></i>
                                            </div>
                                            <div class="upload-text fs-5 fw-semibold">Click to upload or drag and drop</div>
                                            <div class="upload-subtext text-muted">PDF only, max 5MB</div>
                                            <input type="file" id="approvalLetter" name="approvalLetter" accept=".pdf" style="display: none;">
                                        </div>
                                        
                                        <!-- File Preview Section -->
                                        <div id="filePreview" class="file-preview">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="file-icon me-3">PDF</div>
                                                    <div>
                                                        <div id="fileName" class="fw-semibold"></div>
                                                        <div id="fileSize" class="text-muted small"></div>
                                                    </div>
                                                </div>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-primary" id="viewBtn">
                                                        <i class="bi bi-eye me-1"></i>View
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning" id="replaceBtn">
                                                        <i class="bi bi-arrow-repeat me-1"></i>Replace
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" id="deleteBtn">
                                                        <i class="bi bi-trash me-1"></i>Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Merit Application Section -->
                                <div class="mb-5">
                                    <h3 class="section-header text-secondary">
                                        <i class="bi bi-award me-2"></i>Merit Application
                                    </h3>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="applyMerit" name="applyMerit">
                                        <label class="form-check-label fw-semibold" for="applyMerit">
                                            Apply for Merit for this event
                                        </label>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                                    <button type="button" class="btn btn-secondary px-4" onclick="window.location.href='advisor_dash.php'">
                                        <i class="bi bi-x-circle me-2"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-check-circle me-2"></i>SUBMIT
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF Viewer Modal -->
    <div id="pdfModal" class="pdf-modal">
        <div class="pdf-modal-content">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                <h5 id="modalTitle" class="mb-0">Approval Letter</h5>
                <button class="btn-close" id="closeModal" aria-label="Close"></button>
            </div>
            <iframe id="pdfViewer" class="pdf-viewer"></iframe>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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
            document.getElementById('coordinatesText').innerHTML = 
                `<i class="bi bi-geo-alt me-1"></i>Coordinates: ${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`;

            // Add drag listener to marker
            marker.on('dragend', function(e) {
                const newLatLng = e.target.getLatLng();
                selectedLocation = newLatLng;
                document.getElementById('latitude').value = newLatLng.lat.toFixed(6);
                document.getElementById('longitude').value = newLatLng.lng.toFixed(6);
                document.getElementById('coordinatesText').innerHTML = 
                    `<i class="bi bi-geo-alt me-1"></i>Coordinates: ${newLatLng.lat.toFixed(6)}, ${newLatLng.lng.toFixed(6)}`;
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
                uploadArea.classList.add('border-primary');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('border-primary');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('border-primary');
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
                showAlert('Please upload only PDF files', 'warning');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                showAlert('File size must be less than 5MB', 'warning');
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
            uploadText.innerHTML = '<i class="bi bi-check-circle me-2"></i>File uploaded successfully!';
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
                showAlert('No PDF file to view', 'warning');
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
                uploadText.innerHTML = 'Click to upload or drag and drop';
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
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }
            });

            if (!selectedLocation) {
                showAlert('Please select a location on the map', 'warning');
                isValid = false;
            }

            // Validate start date is not in the past (only for new events)
            if (!isEditMode) {
                const startDate = new Date(document.getElementById('startDate').value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (startDate < today) {
                    showAlert('Start date cannot be in the past', 'warning');
                    document.getElementById('startDate').classList.add('is-invalid');
                    isValid = false;
                }
            }

            // Validate end date is not before start date
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);
            
            if (endDate < startDate) {
                showAlert('End date cannot be before start date', 'warning');
                document.getElementById('endDate').classList.add('is-invalid');
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
            const submitBtn = document.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            const loadingText = isEditMode ? 'Updating...' : 'Submitting...';
            const successMessage = isEditMode ? 'Event updated successfully!' : 'Event registered successfully!';
            
            submitBtn.innerHTML = `<div class="spinner-border spinner-border-sm me-2" role="status"></div>${loadingText}`;
            submitBtn.disabled = true;

            fetch('api/save_event.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(successMessage, 'success');
                    setTimeout(() => {
                        window.location.href = 'advisor_dash.php';
                    }, 1500);
                } else {
                    showAlert('Error: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMessage = isEditMode ? 'An error occurred while updating the event' : 'An error occurred while saving the event';
                showAlert(errorMessage, 'danger');
            })
            .finally(() => {
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            });
        }

        // Show Bootstrap alert
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv && alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }

        // Load event for editing
        function loadEventForEdit(eventId) {
            isEditMode = true;
            
            fetch(`api/get_event.php?id=${eventId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateForm(data.event);
                        
                        // Update page title and submit button for edit mode
                        document.querySelector('h1').textContent = 'Edit Event';
                        const submitBtn = document.querySelector('button[type="submit"]');
                        submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>UPDATE';
                        submitBtn.classList.remove('btn-primary');
                        submitBtn.classList.add('btn-success');
                        
                        // Make eventID field readonly during editing
                        const eventIdField = document.getElementById('eventID');
                        eventIdField.readOnly = true;
                        eventIdField.classList.add('bg-light');
                        
                    } else {
                        showAlert('Error loading event: ' + data.message, 'danger');
                        window.location.href = 'advisor_dash.php';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error loading event', 'danger');
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
                    uploadText.innerHTML = '<i class="bi bi-check-circle me-2"></i>File loaded successfully!';
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