<?php

require "vendor/autoload.php";

use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;

// Function to establish database connection
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

// Function to insert subject and questions into subjects table if not already present
function insertSubjectAndQuestions($subject, $topic, $numQuestions) {
    try {
        // Generate questions using Gemini API
        $text = "Generate $numQuestions questions on the topic '$topic' for the subject '$subject'.";
        $client = new Client("AIzaSyAWcPRFlMTAy1PTEfMW1lVy8gYIGj65BRI"); // Replace with your Gemini API key
        $response = $client->geminiPro()->generateContent(new TextPart($text));
        $generatedQuestions = explode("\n", $response->text());

        // Insert subject into subjects table if not already present
        $pdo = getDatabaseConnection();
        $stmt_check_subject = $pdo->prepare("SELECT id FROM subjects WHERE subject_name = ?");
        $stmt_check_subject->execute([$subject]);
        $subject_id = $stmt_check_subject->fetchColumn();

        if (!$subject_id) {
            $stmt_insert_subject = $pdo->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
            $stmt_insert_subject->execute([$subject]);
            $subject_id = $pdo->lastInsertId();
        }

        // Insert questions into subjects_questions table
        $stmt_insert_questions = $pdo->prepare("INSERT INTO subjects_questions (subject_id, question_text) VALUES (:subject_id, :question_text)");

        foreach ($generatedQuestions as $question) {
            $stmt_insert_questions->execute([
                'subject_id' => $subject_id,
                'question_text' => $question
            ]);
        }

        return ['questions' => $generatedQuestions];
    } catch (Exception $e) {
        throw new Exception('Failed to generate questions: ' . $e->getMessage());
    }
}

// Main execution starts here
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['subject']) || !isset($data['topic']) || !isset($data['numQuestions'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$subject = htmlspecialchars($data['subject']);
$topic = htmlspecialchars($data['topic']);
$numQuestions = (int)$data['numQuestions'];

try {
    $result = insertSubjectAndQuestions($subject, $topic, $numQuestions); // Insert subject and questions into subjects table

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
