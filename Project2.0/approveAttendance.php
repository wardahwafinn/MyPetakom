<?php
// Step 1: Connect to database
$link = mysqli_connect("localhost", "root", "", "mypetakom");

// Step 2: Check if form submitted and listID is present
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['listID'])) {
    $listID = $_POST['listID'];

    // Step 3: Update attendance status to 'Present' (1)
    $query = "UPDATE attendancelist SET listStatus = 1 WHERE listID = '$listID'";
    if (mysqli_query($link, $query)) {
        // Step 4: Redirect back to manageAttendance.php with success message
       $QRCode = $_GET['QRCode'] ?? '';
      header("Location: manageAttendance.php?QRCode=" . urlencode($QRCode) . "&success=1");

        exit();
    } else {
        echo "Database error: " . mysqli_error($link);
    }
} else {
    echo "Invalid access.";
}
?>