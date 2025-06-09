<?php 
session_start();

// Connect to DB
$conn = new mysqli("localhost", "root", "", "mypetakom");

// Get user ID from session
$studentID = $_SESSION['userID']; // Assuming userID = studentID

// Initialize variables
$membershipStatus = null;

// Query the latest membership status
$sql = "SELECT memberstatus FROM membership WHERE studentID = ? ORDER BY appliedDate DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentID);
$stmt->execute();
$stmt->bind_result($membershipStatus);
$stmt->fetch();
$stmt->close();
$conn->close();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: loginForm.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="description" content="student membership for myPetakom">
    <meta name="author" content="UMI MAISARAH BINTI MOHD AFENDI">
    <title>MyPetakom</title>
    <link rel="stylesheet" type="text/css" href="style/member.css">
    <link rel="icon" type="image/png" href="images/petakom.png">
    <meta charset="UTF-8">
    <title>Student Page</title>
</head>

<body class="background">

    <div class="sidebar">
        <li class="listyle"><a href="student.php"><img src="images/petakom.png" alt="PETAKOM Logo" class="logo"></a></li>
        <hr>
        <li class="listyle"><a href="studProfile.php" class="nav-item">Profile</a></li>
        <hr>
        <li class="listyle"><a href="student.php" class="nav-item">Dashboard</a></li>
        <hr>
        <li class="listyle"><a class="active" href="studMembership.php" class="nav-item">Apply Membership</a></li>
        <hr>
        <li class="listyle"><a href="student_view_events.php" class="nav-item">View Event</a></li>
        <hr>
    </div>

    <div class="top-right-bar">
        <a href="studProfile.php" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">HI, STUDENT
        </a>
        <a href="logout.php">
            <img src="images/logout.png" alt="Logout Icon" class="logout-icon">
        </a>
    </div>

    <div class="h1text">
        <h1>MEMBERSHIP MANAGEMENT</h1>
    </div>

    <div class="h3text">
        <h2>Apply For Petakom Membership</h2>
    </div>

    <div class="h4text">
        <h2>Please upload a clear image of your STUDENT CARD for verification. Your application will be reviewed by the Petakom Coordinator</h2>
    </div>

    <?php if ($membershipStatus): ?>
    <div style="background-color: #f5f5f5; border-left: 5px solid; margin-left: 20%; margin-right: 9%; margin-bottom: 20px;
        <?= $membershipStatus === 'accepted' ? '#28a745' : ($membershipStatus === 'rejected' ? '#dc3545' : '#ffc107') ?>; 
        padding: 15px; font-size: 18px; border-radius: 5px; margin: left 9%;">
        <?php if ($membershipStatus === 'accepted'): ?>
            ✅ Your membership is <strong>accepted</strong>. You are now a <strong>PETAKOM member</strong>.
        <?php elseif ($membershipStatus === 'rejected'): ?>
            ❌ Your student card was <strong>rejected</strong>. Please upload it again.
        <?php else: ?>
            ⏳ Your application is <strong>under review</strong>. Please wait for approval.
        <?php endif; ?>
    </div>
<?php endif; ?>


    <div class="marginCard">
        <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">
            <?php if ($membershipStatus === 'accepted'): ?>
    <p style="color: green;">You are already a registered member. No further action is needed.</p>
<?php else: ?>
    <!-- show the form -->

            <div class="upload-area" id="dropArea">
                <img src="https://cdn-icons-png.flaticon.com/512/109/109612.png" alt="Upload Icon" class="upload-icon">
                <p>Drag and drop your STUDENT CARD image here</p>
                <p>or</p>
                <label class="browse-btn">
                    Browse Files
                    <input type="file" id="fileInput" name="fileUpload" accept="image/*" required>
                </label>
                <img id="preview" alt="Image Preview">
            </div>

            <input type="text" name="studentID" id="studentID" placeholder="Enter your Student ID" required>

            <button type="submit" class="submit-btn" id="submitBtn" disabled>Submit Application</button>
        </form>
        <?php endif; ?>


        <script>
            const dropArea = document.getElementById("dropArea");
            const fileInput = document.getElementById("fileInput");
            const preview = document.getElementById("preview");
            const submitBtn = document.getElementById("submitBtn");
            const studentID = document.getElementById("studentID");

            dropArea.addEventListener("dragover", (e) => {
                e.preventDefault();
                dropArea.classList.add("dragover");
            });

            dropArea.addEventListener("dragleave", () => {
                dropArea.classList.remove("dragover");
            });

            dropArea.addEventListener("drop", (e) => {
                e.preventDefault();
                dropArea.classList.remove("dragover");

                const file = e.dataTransfer.files[0];
                if (file && file.type.startsWith("image/")) {
                    fileInput.files = e.dataTransfer.files;
                    showPreview(file);
                    enableSubmit();
                }
            });

            fileInput.addEventListener("change", () => {
                const file = fileInput.files[0];
                if (file && file.type.startsWith("image/")) {
                    showPreview(file);
                    enableSubmit();
                }
            });

            studentID.addEventListener("input", enableSubmit);

            function showPreview(file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = "block";
                };
                reader.readAsDataURL(file);
            }

            function enableSubmit() {
                if (fileInput.files.length > 0 && studentID.value.trim() !== "") {
                    submitBtn.disabled = false;
                    submitBtn.classList.add("enabled");
                } else {
                    submitBtn.disabled = true;
                    submitBtn.classList.remove("enabled");
                }
            }
        </script>
    </div>
</body>
</html>
