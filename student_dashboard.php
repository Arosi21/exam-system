<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "examsystem";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 800px;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2, h3 {
            color: #333;
        }

        p {
            color: #666;
        }

        .button, .start-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            margin-top: 10px;
            margin-bottom: 10px;
            text-align: center;
            width: 200px;
            box-sizing: border-box;
        }

        .button:hover, .start-button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .download-button, .logout-button {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome to the Student Dashboard</h2>
        <a href="answer_questions.php" class="start-button">Start Answering Questions</a>
        <a href="quiz.php" class="start-button">Answer Questions</a>
        <a href="view_results.php" class="start-button">View Results</a>
        <a href="viewmarked results.php" class="button">View Marked Results</a>
        <div class="logout-button">
            <a href="index.php" class="button">Logout</a>
        </div>
    </div>
</body>
</html>
