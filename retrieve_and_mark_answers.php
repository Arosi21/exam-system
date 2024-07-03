<?php
require "vendor/autoload.php";

use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;

function getDatabaseConnection() {
    $host = 'localhost';
    $dbname = 'examsystem';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Retrieve questions and answers from the database
function retrieveStudentAnswers() {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query("SELECT question_text, student_answer FROM student_answers");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mark answers using Gemini AI
function markAnswers($answers) {
    $client = new Client("AIzaSyAWcPRFlMTAy1PTEfMW1lVy8gYIGj65BRI"); // Replace with your Gemini API key
    $markedAnswers = [];

    foreach ($answers as $answer) {
        $questionText = $answer['question_text'];
        $answerText = $answer['student_answer'];
        $text = "Grade the following answer:\n\nQuestion: $questionText\n\nAnswer: $answerText";

        try {
            $response = $client->geminiPro()->generateContent(new TextPart($text));
            $markedAnswer = $response->text();
            $markedAnswers[] = $markedAnswer;
        } catch (Exception $e) {
            $markedAnswers[] = 'Failed to mark answer: ' . $e->getMessage();
        }
    }

    return $markedAnswers;
}

// Main logic
try {
    $answers = retrieveStudentAnswers();
    $markedAnswers = markAnswers($answers);
    echo json_encode(['markedAnswers' => $markedAnswers]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to mark answers: ' . $e->getMessage()]);
}
?>
