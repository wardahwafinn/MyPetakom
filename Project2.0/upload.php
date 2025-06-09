<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";         // Replace if different
$pass = "";             // Replace if different
$dbname = "mypetakom";  // Your database name

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentID = $_POST['studentID'];
    $memberStatus = 'pending';
    $appliedDate = date('Y-m-d');
    $staffID = null; // Optional — change if needed

    // Validate file upload
    if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] == 0) {
        $file = $_FILES['fileUpload'];

        if (strpos($file['type'], 'image/') === 0) {
            $imageData = file_get_contents($file['tmp_name']);

            // 1. Update studentCardID in `student` table
            $stmt1 = $conn->prepare("UPDATE student SET studentCardID = ? WHERE studentID = ?");
            if (!$stmt1) {
                die("Prepare failed: " . $conn->error);
            }

            $null = NULL; // Placeholder for blob
            $stmt1->bind_param("bs", $null, $studentID);
            $stmt1->send_long_data(0, $imageData);

            if (!$stmt1->execute()) {
                echo "<script>alert('Failed to upload student card: " . addslashes($stmt1->error) . "'); window.history.back();</script>";
                $stmt1->close();
                $conn->close();
                exit;
            }
            $stmt1->close();

            // 2. Insert into `membership` table
            $stmt2 = $conn->prepare("INSERT INTO membership (studentID, staffID, memberStatus, appliedDate) VALUES (?, ?, ?, ?)");
            if (!$stmt2) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt2->bind_param("ssss", $studentID, $staffID, $memberStatus, $appliedDate);

            if ($stmt2->execute()) {
                echo "<script>
                    alert('Application and student card uploaded successfully!');
                    window.location.href = 'student.php';
                </script>";
            } else {
                echo "<script>
                    alert('Database error: " . addslashes($stmt2->error) . "');
                    window.history.back();
                </script>";
            }

            $stmt2->close();
        } else {
            echo "<script>alert('Invalid file type. Only image files are allowed.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('File upload failed or no file uploaded.'); window.history.back();</script>";
    }
}

$conn->close();
?>
