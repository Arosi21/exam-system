<?php
// Database configuration
$servername = "localhost"; // Replace with your database server hostname
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$database = "examsystem"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for question upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $quiz_id = $_POST['quiz_id'];
    $question = $conn->real_escape_string($_POST['question']);
    $option_a = $conn->real_escape_string($_POST['option_a']);
    $option_b = $conn->real_escape_string($_POST['option_b']);
    $option_c = $conn->real_escape_string($_POST['option_c']);
    $option_d = $conn->real_escape_string($_POST['option_d']);
    $correct_option = strtoupper($_POST['correct_option']);

    // Check if the quiz_id exists in quizzes table
    $check_quiz_sql = "SELECT id FROM quizzes WHERE id = $quiz_id";
    $check_quiz_result = $conn->query($check_quiz_sql);

    if ($check_quiz_result->num_rows == 0) {
        echo "Error: Quiz ID $quiz_id does not exist.";
    } else {
        // Insert question into questions table
        $insert_sql = "INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_option) 
                       VALUES ($quiz_id, '$question', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_option')";

        if ($conn->query($insert_sql) === TRUE) {
            echo "New question added successfully";
        } else {
            echo "Error: " . $insert_sql . "<br>" . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Administer Questions</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <h1>Upload Questions</h1>
    <form method="post" action="">
        <label>Quiz ID:</label><br>
        <input type="number" name="quiz_id" required><br>
        <label>Question:</label><br>
        <textarea name="question" required></textarea><br>
        <label>Option A:</label><br>
        <input type="text" name="option_a" required><br>
        <label>Option B:</label><br>
        <input type="text" name="option_b" required><br>
        <label>Option C:</label><br>
        <input type="text" name="option_c" required><br>
        <label>Option D:</label><br>
        <input type="text" name="option_d" required><br>
        <label>Correct Option (A/B/C/D):</label><br>
        <input type="text" name="correct_option" maxlength="1" required><br><br>
        <input type="submit" value="Upload Question">
    </form>
    <hr>
    <a href="examiner_dashboard.php" class="button">Back to Dashboard</a>
</body>
</html>
