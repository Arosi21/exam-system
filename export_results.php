<?php
include("connect.php");

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="exam_results.xls"');
header('Cache-Control: max-age=0');

echo "Student Name\tRegistration Number\tSubject\tScore\n";

$sql = "SELECT student_name, registration_number, subject, score FROM exam_results";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo $row["student_name"] . "\t" . $row["registration_number"] . "\t" . $row["subject"] . "\t" . $row["score"] . "\n";
    }
}

$conn->close();
?>
