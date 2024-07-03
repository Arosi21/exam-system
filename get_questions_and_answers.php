<?php
require "vendor/autoload.php";

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

try {
    $pdo = getDatabaseConnection();
    
    // Example: Fetching questions and answers for a specific subject
    if (isset($_GET['subject'])) {
        $subject = $_GET['subject'];
        $stmt = $pdo->prepare("SELECT question,registration_number,student_name student_answer FROM student_answers WHERE subject_name = :subject");
        $stmt->execute(['subject' => $subject]);
        $questionsAndAnswers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Displaying questions and answers (you can integrate Gemini AI here)
        foreach ($questionsAndAnswers as $qa) {
            echo "<p><strong>Question:</strong> {$qa['question']}</p>";
            echo "<p><strong>Student Answer:</strong> {$qa['student_answer']}</p>";
            
            // Example integration with Gemini AI (replace with actual integration)
            // $geminiResult = GeminiAI::analyzeAnswer($qa['student_answer']);
            // echo "<p><strong>Gemini AI Analysis:</strong> {$geminiResult}</p>";
        }
        
    } else {
        echo json_encode(['error' => 'No subject specified']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve questions and answers: ' . $e->getMessage()]);
}
?>
