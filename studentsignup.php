<?php
// Include database connection file
require_once "connect.php";

// Define variables and initialize with empty values
$registration_number = $password = "";
$registration_number_err = $password_err = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate registration number
    if (empty(trim($_POST["registration_number"]))) {
        $registration_number_err = "Please enter registration number.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM students WHERE registration_number = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_registration_number);

            // Set parameters
            $param_registration_number = trim($_POST["registration_number"]);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $registration_number_err = "This registration number is already taken.";
                } else {
                    $registration_number = trim($_POST["registration_number"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Check input errors before inserting into database
    if (empty($registration_number_err) && empty($password_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO students (registration_number, password) VALUES (?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ss", $param_registration_number, $param_password);

            // Set parameters
            $param_registration_number = $registration_number;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to login page
                header("location: studentlogin.php");
                exit;
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Signup</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2>Student Signup</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div>
            <label>Registration Number</label>
            <input type="text" name="registration_number" value="<?php echo htmlspecialchars($registration_number); ?>">
            <span><?php echo $registration_number_err; ?></span>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password">
            <span><?php echo $password_err; ?></span>
        </div>
        <div>
            <input type="submit" value="Sign Up">
        </div>
        <p>Already have an account? <a href="studentlogin.php">Login here</a>.</p>
    </form>
</body>
</html>
