<?php
// Fetch eventID to delete
if (isset($_GET['eventID'])) {
    $eventID = $_GET['eventID'];

    // Database connection
    $link = mysqli_connect("localhost", "root", "") or die(mysqli_connect_error());
    mysqli_select_db($link, "mypetakom") or die(mysqli_error($link));

    // First, delete the related records from the attendancelist table
    $deleteAttendanceQuery = "DELETE FROM attendancelist WHERE slotID IN (SELECT slotID FROM attendanceslot WHERE eventID = '$eventID')";
    $resultAttendance = mysqli_query($link, $deleteAttendanceQuery);

    if ($resultAttendance) {
        // Then, delete the event from the attendanceslot table
        $query = "DELETE FROM attendanceslot WHERE eventID = '$eventID'";
        $result = mysqli_query($link, $query);

        if ($result) {
            // Redirect after successful deletion
            header("Location: Project.php"); // Redirect to the main page
            exit;
        } else {
            echo "Error deleting event from attendanceslot: " . mysqli_error($link);
        }
    } else {
        echo "Error deleting related attendance records: " . mysqli_error($link);
    }

    mysqli_close($link);
} else {
    // If no eventID is provided, redirect
    header("Location: Project.php");
    exit;
}
?>