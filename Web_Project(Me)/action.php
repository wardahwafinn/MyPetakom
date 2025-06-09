<?php
// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $eventID = $_POST["eventID"];
    $slotDate = $_POST["slotDate"];
    $slotTime = $_POST["slotTime"];
    $coordinate = $_POST["coordinate"];

     $QRCode = uniqid("QRCode");
    // Database connection
    $link = mysqli_connect("localhost", "root", "") or die(mysqli_connect_error());
    
    // Select database
    mysqli_select_db($link, "mypetakom") or die(mysqli_error($link));

    // Create query to insert the data into the database
    $query = "INSERT INTO attendanceslot (eventID, slotDate, slotTime, coordinate, QRCode) 
              VALUES ('$eventID', '$slotDate', '$slotTime', '$coordinate', '$QRCode')";

    // Execute query
    $result = mysqli_query($link, $query);

    // Check result
    if ($result) {
        echo "<script>
                alert('Slot saved and QR code generated successfully!');
                window.location.href='Project.php'; // Redirect to another page after success
              </script>";
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($link);
    }

    // Close connection
    mysqli_close($link);
}
?>
