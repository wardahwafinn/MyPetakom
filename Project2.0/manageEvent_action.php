
<?php
$link = mysqli_connect("localhost", "root", "", "mypetakom");

// Step 1: Make sure QRCode is provided
$QRCode = $_GET['QRCode'] ?? "";  // Use null coalescing to avoid warning

// Step 2: Initialize fallback values
$eventID = $slotDate = $slotTime = $coordinate = $qrURL = "";

// Step 3: Only query if QRCode is present
if ($QRCode !== "") {
    $query = "SELECT * FROM attendanceslot WHERE QRCode = '$QRCode'";
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $eventID = $row["eventID"];
        $slotDate = $row["slotDate"];
        $slotTime = $row["slotTime"];
        $coordinate = $row["coordinate"];
    }
}

// Step 4: Generate QR code
$checkinURL = "http://localhost/checkIn.php?QRCode=$QRCode";
$qrURL = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($checkinURL);
?>
