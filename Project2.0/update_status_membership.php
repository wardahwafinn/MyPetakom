<?php
$conn = new mysqli("localhost", "root", "", "mypetakom");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $membershipID = $_POST["membershipID"];
    $action = $_POST["action"];

    $statusMap = [
        "accept" => "accepted",
        "reject" => "rejected"
    ];

    if (array_key_exists($action, $statusMap)) {
        $newStatus = $statusMap[$action];

        $stmt = $conn->prepare("UPDATE membership SET memberstatus = ? WHERE membershipID = ?");
        $stmt->bind_param("si", $newStatus, $membershipID);
        $stmt->execute();
        $stmt->close();

        echo $newStatus;  // ✅ Send actual status back to JS
    } else {
        echo "invalid action";
    }
}

$conn->close();
?>
