<?php
$conn = new mysqli("localhost", "root", "", "mypetakom");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $membershipID = $_POST["membershipID"];
    $action = $_POST["action"];

    if (in_array($action, ["accept", "reject"])) {
        $stmt = $conn->prepare("UPDATE membership SET memberstatus = ? WHERE membershipID = ?");
        $stmt->bind_param("si", $action, $membershipID);
        $stmt->execute();
        $stmt->close();

        echo "success";
    } else {
        echo "invalid action";
    }
}

$conn->close();
?>
