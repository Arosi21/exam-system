<?php
// Include the database connection file
include("connect.php");

session_start();

// Fetch subjects from the database for the dropdown menu
$sql_subjects = "SELECT id, subject_name FROM subjects";
$result_subjects = $conn->query($sql_subjects);

// Initialize variables to avoid undefined variable notices
$registration_number = '';
$student_name = '';
$selected_subject_id = '';
$selected_subject_name = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['registration_number']) && isset($_POST['student_name'])) {
        // Capture registration_number and student_name
        $registration_number = $_POST['registration_number'];
        $student_name = $_POST['student_name'];

        // Save to session for persistence
        $_SESSION['registration_number'] = $registration_number;
        $_SESSION['student_name'] = $student_name;
    }

    if (isset($_POST['subject_id'])) {
        $selected_subject_id = intval($_POST['subject_id']);

        // Fetch the subject name for the selected subject_id
        $stmt_subject = $conn->prepare("SELECT subject_name FROM subjects WHERE id = ?");
        $stmt_subject->bind_param("i", $selected_subject_id);
        $stmt_subject->execute();
        $result_subject = $stmt_subject->get_result();
        $subject_data = $result_subject->fetch_assoc();
        $selected_subject_name = $subject_data['subject_name'];

        $sql_questions = "SELECT id, question_text FROM subjects_questions WHERE subject_id = ?";
        $stmt_questions = $conn->prepare($sql_questions);
        $stmt_questions->bind_param("i", $selected_subject_id);
        $stmt_questions->execute();
        $result_questions = $stmt_questions->get_result();
    }
}

// Check if registration number and student name are in session
if (isset($_SESSION['registration_number']) && isset($_SESSION['student_name'])) {
    $registration_number = $_SESSION['registration_number'];
    $student_name = $_SESSION['student_name'];
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['answers'])) {
    $answers = $_POST['answers'];

    // Prepare insert statement for answers
    $stmt = $conn->prepare("INSERT INTO student_answers (registration_number, student_name, question_id, question_text, student_answer, subject_id, subject_name) VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($answers as $question_id => $student_answer) {
        $question_id = intval($question_id); // Ensure question_id is integer for security

        // Fetch question text from subjects_questions table
        $stmt_question_text = $conn->prepare("SELECT question_text FROM subjects_questions WHERE id = ?");
        $stmt_question_text->bind_param("i", $question_id);
        $stmt_question_text->execute();
        $result_question_text = $stmt_question_text->get_result();
        $question_data = $result_question_text->fetch_assoc();
        $question_text = $question_data['question_text'];

        // Insert student's answer along with question details, subject_id, and subject_name
        $stmt->bind_param("ssissis", $registration_number, $student_name, $question_id, $question_text, $student_answer, $selected_subject_id, $selected_subject_name);
        $stmt->execute();
    }

    $stmt->close();
    echo "<p>Answers submitted successfully!</p>";

    // Clear session variables after submission
    unset($_SESSION['registration_number']);
    unset($_SESSION['student_name']);

    // Redirect to student dashboard after 3 seconds
    echo '<meta http-equiv="refresh" content="3;url=student_dashboard.php">';
    exit; // Stop further execution after redirection
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Answer Questions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select, .question textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .question {
            margin-bottom: 20px;
        }
        .submit-button {
            padding: 10px 20px;
            background-color: #008CBA;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-button:hover {
            background-color: #007bb5;
        }
        .logout-button {
            text-align: center;
            margin-top: 20px;
        }
        .logout-button .button {
            padding: 10px 20px;
            background-color: #f44336;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .logout-button .button:hover {
            background-color: #da190b;
        }
        #timer {
            font-size: 20px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
    <script>
        function startTimer(duration, display) {
            var timer = duration, minutes, seconds;
            setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    timer = 0;
                    alert("Time is up!");
                    document.getElementById('answerForm').submit();
                }
            }, 1000);
        }

        window.onload = function () {
            var timeLimit = 60 * 10, // 1 minute for example
                display = document.querySelector('#timer');
            startTimer(timeLimit, display);
        };
    </script>
</head>
<body>
    <div class="container">
        <h2>Start Answering Questions</h2>

        <?php if (empty($registration_number) || empty($student_name)) : ?>
            <form id="registrationForm" action="answer_questions.php" method="post">
                <div class="form-group">
                    <label for="registration_number">Registration Number:</label>
                    <input type="text" id="registration_number" name="registration_number" required>
                </div>
                <div class="form-group">
                    <label for="student_name">Student Name:</label>
                    <input type="text" id="student_name" name="student_name" required>
                </div>
                <input type="submit" class="submit-button" value="Start Answering">
            </form>
        <?php else : ?>
            <div id="timer">30:00</div> <!-- Timer display -->
            <form id="subjectForm" action="answer_questions.php" method="post">
                <div class="form-group">
                    <label for="subject_id">Select Subject:</label>
                    <select id="subject_id" name="subject_id" onchange="this.form.submit()" required>
                        <option value="">Select Subject</option>
                        <?php
                        if ($result_subjects->num_rows > 0) {
                            while($row_subject = $result_subjects->fetch_assoc()) {
                                echo '<option value="' . $row_subject["id"] . '"';
                                if (isset($_POST['subject_id']) && $_POST['subject_id'] == $row_subject['id']) {
                                    echo ' selected';
                                }
                                echo '>' . htmlspecialchars($row_subject["subject_name"]) . '</option>';
                            }
                        } else {
                            echo '<option value="">No subjects available</option>';
                        }
                        ?>
                    </select>
                </div>
            </form>

            <?php
            if (isset($result_questions) && $result_questions->num_rows > 0) {
                echo '<form id="answerForm" action="answer_questions.php" method="post">';
                echo '<input type="hidden" name="subject_id" value="' . $selected_subject_id . '">';
                echo '<input type="hidden" name="subject_name" value="' . htmlspecialchars($selected_subject_name) . '">';
                echo '<input type="hidden" name="registration_number" value="' . htmlspecialchars($registration_number) . '">';
                echo '<input type="hidden" name="student_name" value="' . htmlspecialchars($student_name) . '">';

                while($row = $result_questions->fetch_assoc()) {
                    echo '<div class="question">';
                    echo '<label for="question_' . $row["id"] . '">' . htmlspecialchars($row["question_text"]) . '</label>';
                    echo '<textarea id="question_' . $row["id"] . '" name="answers[' . $row["id"] . ']" rows="4" required></textarea>';
                    echo '</div>';
                }

                echo '<input type="submit" class="submit-button" value="Submit Answers">';
                echo '</form>';
            } elseif (isset($selected_subject_id)) {
                echo '<p>No questions available for the selected subject.</p>';
            }
            ?>
        <?php endif; ?>

        <div class="logout-button">
            <a href="student_dashboard.php" class="button">Logout</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
