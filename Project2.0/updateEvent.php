
<?php
// Establish database connection
$link = mysqli_connect("localhost", "root", "", "mypetakom");

// Check connection
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the form data
    $eventID = $_POST['eventID'];
    $slotDate = $_POST['slotDate'];
    $slotTime = $_POST['slotTime'];
    $coordinate = $_POST['coordinate'];

    // Update query to update the event details
    $updateQuery = "UPDATE attendanceslot SET slotDate = ?, slotTime = ?, coordinate = ? WHERE eventID = ?";
    $stmt = mysqli_prepare($link, $updateQuery);

    // Bind the parameters to the prepared statement
    mysqli_stmt_bind_param($stmt, "ssss", $slotDate, $slotTime, $coordinate, $eventID);

    // Execute the query and check for success
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Event updated successfully!'); window.location.href = 'Project.php';</script>";
    } else {
        echo "<script>alert('Error updating event: " . mysqli_error($link) . "');</script>";
    }

    // Close the statement
    mysqli_stmt_close($stmt);
}

// Close the database connection
mysqli_close($link);
?>
