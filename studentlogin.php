<?php
// Include database connection file
require_once "connect.php";

session_start();

// Define variables and initialize with empty values
$registration_number = $password = "";
$registration_number_err = $password_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if registration number is empty
    if (empty(trim($_POST["registration_number"]))) {
        $registration_number_err = "Please enter your registration number.";
    } else {
        $registration_number = trim($_POST["registration_number"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($registration_number_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, registration_number, password FROM students WHERE registration_number = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_registration_number);

            // Set parameters
            $param_registration_number = $registration_number;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();

                // Check if registration number exists, if yes then verify password
                if ($stmt->num_rows == 1) {
                    // Bind result variables
                    $stmt->bind_result($id, $registration_number, $hashed_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["registration_number"] = $registration_number;

                            // Redirect user to student dashboard page
                            header("location: student_dashboard.php");
                        } else {
                            // Display an error message if password is not valid
                            $password_err = "The password you entered was not valid.";
                        }
                    }
                } else {
                    // Display an error message if registration number doesn't exist
                    $registration_number_err = "No account found with that registration number.";
                }
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
    <title>Student Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2>Student Login</h2>
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
            <input type="submit" value="Login">
        </div>
        <p>Don't have an account? <a href="studentsignup.php?type=student">Sign up now</a>.</p>
    </form>
</body>
</html>
