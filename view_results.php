<?php
// Include database connection file
include('connect.php');

// Retrieve exam results from the database
$sql = "SELECT * FROM exam_results";
$result = mysqli_query($conn, $sql);

// Check if there are results
if (mysqli_num_rows($result) > 0) {
    // Output data of each row
    echo "<h2>Exam Results</h2>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<p><strong>Student Name:</strong> " . $row["student_name"] . "</p>";
        echo "<p><strong>Score:</strong> " . $row["score"] . "</p>";
        echo "<p><strong>Exam Timestamp:</strong> " . $row["exam_timestamp"] . "</p>";
        // If responses are stored as JSON, you can decode and display them
        $responses = json_decode($row["responses"], true);
        echo "<p><strong>Responses:</strong><br>";
        foreach ($responses as $question => $response) {
            echo "- Question: $question, Response: $response<br>";
        }
        echo "</p>";
        echo "<hr>";
    }
} else {
    echo "<p>No exam results found.</p>";
}

// Close the database connection
mysqli_close($conn);
?>
