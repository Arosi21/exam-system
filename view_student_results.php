<?php
include("connect.php");

if (isset($_GET['download'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="exam_results.xls"');
    header('Cache-Control: max-age=0');

    echo "Registration Number\tSubject\tQuestion\tStudent Answer\tMarked Answer\n";

    $selected_subject = $_GET['subject'] ?? null;

    $sql = $selected_subject
        ? "SELECT registration_number, subject_name, question_text, student_answer, marked_answer FROM marked_answers WHERE subject_name = ? ORDER BY registration_number, subject_name"
        : "SELECT registration_number, subject_name, question_text, student_answer, marked_answer FROM marked_answers ORDER BY registration_number, subject_name";

    if ($selected_subject) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $selected_subject);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "{$row['registration_number']}\t{$row['subject_name']}\t{$row['question_text']}\t{$row['student_answer']}\t{$row['marked_answer']}\n";
        }
    }

    $conn->close();
    exit;
}

$selected_subject = $_GET['subject'] ?? null;

$sql = $selected_subject
    ? "SELECT registration_number, subject_name, question_text, student_answer, marked_answer FROM marked_answers WHERE subject_name = ? ORDER BY registration_number, subject_name"
    : "SELECT registration_number, subject_name, question_text, student_answer, marked_answer FROM marked_answers ORDER BY registration_number, subject_name";

if ($selected_subject) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_subject);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 20px;
        }
        label, select {
            font-size: 16px;
            margin-right: 10px;
        }
        select {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
            transition: background-color 0.3s;
        }
        button:hover {
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
        .button {
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
        .button:hover {
            background-color: #0056b3;
        }
        .logout-button {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Student Results</h2>

        <!-- Subject selection form -->
        <form method="GET">
            <label for="subject">Select Subject:</label>
            <select name="subject" id="subject">
                <option value="">All Subjects</option>
                <?php
                $distinct_subjects_query = "SELECT DISTINCT subject_name FROM marked_answers";
                $distinct_subjects_result = $conn->query($distinct_subjects_query);
                while ($row = $distinct_subjects_result->fetch_assoc()) {
                    $subject_name = $row['subject_name'];
                    $selected = ($subject_name == $selected_subject) ? 'selected' : '';
                    echo "<option value='$subject_name' $selected>$subject_name</option>";
                }
                ?>
            </select>
            <button type="submit">View Results</button>
            <button type="submit" name="download" value="true">Download Results</button>
        </form>

        <?php
        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>Registration Number</th><th>Subject</th><th>Question</th><th>Student Answer</th><th>Marked Answer</th></tr>";
            $current_registration_number = '';
            $current_subject = '';
            $rowspan = 0;
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
                if ($row['registration_number'] !== $current_registration_number || $row['subject_name'] !== $current_subject) {
                    $current_registration_number = $row['registration_number'];
                    $current_subject = $row['subject_name'];
                    $rowspan = count(array_filter($rows, function($r) use ($current_registration_number, $current_subject) {
                        return $r['registration_number'] === $current_registration_number && $r['subject_name'] === $current_subject;
                    }));
                    echo "<tr><td rowspan='$rowspan'>{$current_registration_number}</td><td rowspan='$rowspan'>{$current_subject}</td><td>{$row['question_text']}</td><td>{$row['student_answer']}</td><td>{$row['marked_answer']}</td></tr>";
                } else {
                    echo "<tr><td colspan='2'></td><td>{$row['question_text']}</td><td>{$row['student_answer']}</td><td>{$row['marked_answer']}</td></tr>";
                }
            }
            echo "</table>";
        } else {
            echo "<p>No results found.</p>";
        }
        $conn->close();
        ?>

        <div class="logout-button">
            <a href="index.php" class="button">Logout</a>
        </div>
    </div>
</body>
</html>
