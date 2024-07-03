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

// Retrieve questions, student answers, and registration numbers from the database for a specific subject
function retrieveStudentAnswers($subjectName) {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT registration_number, question_text, student_answer FROM student_answers WHERE subject_name = :subjectName");
    $stmt->execute(['subjectName' => $subjectName]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mark answers using Gemini AI
function markAnswers($answers) {
    $client = new Client("AIzaSyAWcPRFlMTAy1PTEfMW1lVy8gYIGj65BRI"); // Replace with your Gemini API key
    $markedAnswers = [];

    foreach ($answers as $answer) {
        $questionText = $answer['question_text'];
        $studentAnswer = $answer['student_answer'];
        $registrationNumber = $answer['registration_number'];
        $text = "Grade the following answer give grades only not explanation:\n\nQuestion: $questionText\n\nAnswer: $studentAnswer";

        try {
            $response = $client->geminiPro()->generateContent(new TextPart($text));
            $markedAnswer = $response->text();
            $markedAnswers[] = [
                'registration_number' => $registrationNumber,
                'question_text' => $questionText,
                'student_answer' => $studentAnswer,
                'marked_answer' => $markedAnswer
            ];
        } catch (Exception $e) {
            $markedAnswers[] = [
                'registration_number' => $registrationNumber,
                'question_text' => $questionText,
                'student_answer' => $studentAnswer,
                'marked_answer' => 'Failed to mark answer: ' . $e->getMessage()
            ];
        }
    }

    return $markedAnswers;
}

// Insert marked answers into the database
function insertMarkedAnswers($markedAnswers, $subjectName) {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("INSERT INTO marked_answers (registration_number, subject_name, question_text, student_answer, marked_answer) VALUES (:registrationNumber, :subjectName, :questionText, :studentAnswer, :markedAnswer)");

    foreach ($markedAnswers as $answer) {
        $stmt->execute([
            'registrationNumber' => $answer['registration_number'],
            'subjectName' => $subjectName,
            'questionText' => $answer['question_text'],
            'studentAnswer' => $answer['student_answer'],
            'markedAnswer' => $answer['marked_answer']
        ]);
    }
}

// Main logic
try {
    $input = json_decode(file_get_contents('php://input'), true);
    $subjectName = $input['subjectName'];

    $answers = retrieveStudentAnswers($subjectName);
    $markedAnswers = markAnswers($answers);
    insertMarkedAnswers($markedAnswers, $subjectName); // Insert the marked answers into the database
    echo json_encode(['markedAnswers' => $markedAnswers]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to mark answers: ' . $e->getMessage()]);
}
?>
