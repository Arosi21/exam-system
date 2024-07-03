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

$quiz_selected = false;
$quiz_id = null;
$student_name = '';
$registration_number = '';

// Handle form submission for answers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answers'])) {
    $quiz_id = $_POST['quiz_id'];
    $student_name = $_POST['student_name'];
    $registration_number = $_POST['registration_number'];
    $user_answers = $_POST['answers'];

    // Retrieve questions and correct answers
    $questions_sql = "SELECT * FROM questions WHERE quiz_id = $quiz_id";
    $questions_result = $conn->query($questions_sql);
    $score = 0;
    $total_questions = $questions_result->num_rows;

    while ($question_row = $questions_result->fetch_assoc()) {
        $question_id = $question_row['id'];
        $correct_option = $question_row['correct_option'];

        if (isset($user_answers[$question_id]) && strtoupper($user_answers[$question_id]) == strtoupper($correct_option)) {
            $score++;
        }
    }

    // Calculate percentage score
    $percentage = ($score / $total_questions) * 100;

    // Store quiz result in database
    $insert_sql = "INSERT INTO quiz_results (quiz_id, student_name, registration_number, score) 
                   VALUES ($quiz_id, '$student_name', '$registration_number', $score)";

    if ($conn->query($insert_sql) === TRUE) {
        echo "<h2>Your Score: $score out of $total_questions ($percentage%)</h2>";
    } else {
        echo "Error: " . $insert_sql . "<br>" . $conn->error;
    }
} elseif (isset($_POST['quiz_id']) && isset($_POST['student_name']) && isset($_POST['registration_number'])) {
    $quiz_selected = true;
    $quiz_id = $_POST['quiz_id'];
    $student_name = $_POST['student_name'];
    $registration_number = $_POST['registration_number'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Answer Quiz Questions</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <h1>Select a Quiz to Answer</h1>
    <?php if (!$quiz_selected): ?>
    <form method="post" action="">
        <label>Select Quiz:</label>
        <select name="quiz_id" required>
            <option value="">Select Quiz</option>
            <?php
            // Fetch all quizzes from the database
            $quizzes_sql = "SELECT * FROM quizzes";
            $quizzes_result = $conn->query($quizzes_sql);

            if ($quizzes_result->num_rows > 0) {
                while ($quiz_row = $quizzes_result->fetch_assoc()) {
                    echo '<option value="' . $quiz_row['id'] . '">' . $quiz_row['name'] . '</option>';
                }
            } else {
                echo '<option value="">No quizzes found</option>';
            }
            ?>
        </select>
        <br><br>
        <label>Student Name:</label><br>
        <input type="text" name="student_name" required><br><br>
        <label>Registration Number:</label><br>
        <input type="text" name="registration_number" required><br><br>
        <input type="submit" value="Start Quiz">
    </form>
    <?php else: ?>
    <form method="post" action="">
        <?php
        // Fetch quiz details
        $quiz_sql = "SELECT * FROM quizzes WHERE id = $quiz_id";
        $quiz_result = $conn->query($quiz_sql);
        $quiz_row = $quiz_result->fetch_assoc();

        echo "<h2>{$quiz_row['name']}</h2>";
        echo "<h3>Duration: {$quiz_row['duration']} seconds</h3>";

        // Fetch questions for the selected quiz
        $questions_sql = "SELECT * FROM questions WHERE quiz_id = $quiz_id";
        $questions_result = $conn->query($questions_sql);

        if ($questions_result->num_rows > 0) {
            echo '<input type="hidden" name="quiz_id" value="' . $quiz_id . '">';
            echo '<input type="hidden" name="student_name" value="' . $student_name . '">';
            echo '<input type="hidden" name="registration_number" value="' . $registration_number . '">';

            while ($question_row = $questions_result->fetch_assoc()) {
                $question_id = $question_row['id'];
                $question = $question_row['question'];
                $option_a = $question_row['option_a'];
                $option_b = $question_row['option_b'];
                $option_c = $question_row['option_c'];
                $option_d = $question_row['option_d'];
                echo "<h3>$question</h3>";
                echo '<input type="radio" name="answers[' . $question_id . ']" value="A"> ' . $option_a . '<br>';
                echo '<input type="radio" name="answers[' . $question_id . ']" value="B"> ' . $option_b . '<br>';
                echo '<input type="radio" name="answers[' . $question_id . ']" value="C"> ' . $option_c . '<br>';
                echo '<input type="radio" name="answers[' . $question_id . ']" value="D"> ' . $option_d . '<br>';
            }

            echo '<br><input type="submit" value="Submit Answers">';
        } else {
            echo "<p>No questions found for this quiz.</p>";
        }
        ?>
    </form>
    <?php endif; ?>

    <!-- Logout link -->
    <br><a href="index.php">Logout</a>

    <?php
    $conn->close();
    ?>
</body>
</html>
