<?php
// Database connection
$host = "localhost";
$user = "root";     // Replace with your DB username
$pass = "";     // Replace with your DB password
$dbname = "mypetakom";   // Replace with your DB name

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentID = $_POST['studentID'];
    $memberStatus = 'pending';
    $appliedDate = date('Y-m-d');
    $staffID = null; // Or use session if needed

    $stmt = $conn->prepare("INSERT INTO membership (studentID, staffID, memberStatus, appliedDate) VALUES (?, ?, ?, ?)");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);  // Shows the real issue
    }

    $stmt->bind_param("ssss", $studentID, $staffID, $memberStatus, $appliedDate);

if ($stmt->execute()) {
    echo "<script>
        alert('Application submitted successfully!');
        window.location.href = 'student.php';
    </script>";
} else {
    echo "<script>
        alert('Database error: " . addslashes($stmt->error) . "');
        window.history.back();
    </script>";
}

    $stmt->close();
}

$conn->close();
?>
