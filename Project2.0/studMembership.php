<?php 
session_start();

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
    <meta name="description" content="student Dashboard for myPetakom">
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
        <li class="listyle"><a href="studEvent.php" class="nav-item">View Event</a></li>
        <hr>
    </div>

    <div class="top-right-bar">
        <a href="profile.html" class="profilename">
            <img src="images/user.png" alt="User" class="profile-icon">HI, MAISARAH
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

    <div class="marginCard">
        <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="upload-area" id="dropArea">
                <img src="https://cdn-icons-png.flaticon.com/512/109/109612.png" alt="Upload Icon" class="upload-icon">
                <p>Drag and drop your STUDENT CARD image here</p>
                <p>or</p>
                <label class="browse-btn">
                    Browse Files
                    <input type="file" id="fileInput" name="fileUpload" accept="image/*">
                </label>
                <img id="preview" alt="Image Preview">
            </div>

            <input type="text" name="studentID" id="studentID" placeholder="Enter your Student ID" required>

            <button type="submit" class="submit-btn" id="submitBtn" disabled>Submit Application</button>
        </form>

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
