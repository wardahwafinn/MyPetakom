
<?php
date_default_timezone_set("Asia/Kuala_Lumpur");
$link = mysqli_connect("localhost", "root", "", "mypetakom");

// Step 1: Get QRCode from URL
$QRCode = $_GET['QRCode'] ?? '';

// Step 2: Find slotID from attendanceslot table
$getSlot = mysqli_query($link, "SELECT slotID, coordinate FROM attendanceslot WHERE QRCode='$QRCode'");
if (!$getSlot || mysqli_num_rows($getSlot) == 0) {
    die("Slot not found for QRCode");
}
$slot = mysqli_fetch_assoc($getSlot);
$slotID = $slot['slotID'];
$expectedGeo = $slot['coordinate']; // from slot

// Step 3: Get form input
$studentID = $_POST['studentID'];
$password = $_POST['password'];  // optional: check against students table
$checkInTime = date("H:i:s");

// Step 4: Simulate student geolocation (real project can use JavaScript)
$actualGeo = $expectedGeo;  // For now assume matched
$locationMatched = ($actualGeo === $expectedGeo) ? $actualGeo : 'Not Matched';

// Step 5: Insert into attendance_list table
$query = "INSERT INTO attendancelist (slotID, studentID, checkInTime, geolocation, listStatus)
          VALUES ('$slotID', '$studentID', '$checkInTime', '$locationMatched', 0)";

if (mysqli_query($link, $query)) {
    echo "<script>alert('Check-in recorded. Awaiting approval.'); window.location='checkIn.php';</script>";
} else {
    echo "Database error: " . mysqli_error($link);
}
?>
