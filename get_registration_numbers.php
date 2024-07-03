<?php
include("connect.php");

$sql = "SELECT DISTINCT registration_number FROM student_answers";
$result = $conn->query($sql);

$registration_numbers = array();
while ($row = $result->fetch_assoc()) {
    $registration_numbers[] = array("registration_number" => $row["registration_number"]);
}

echo json_encode(array("registration_numbers" => $registration_numbers));

$conn->close();
?>
