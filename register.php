<?php
session_start();
include 'db.php';

// Initialize error message variable
$error_msg = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // Get the confirm password

    // Check if the passwords match
    if ($password !== $confirm_password) {
        $error_msg = "Passwords do not match. Please try again.";
    } else {
        // Check if the username already exists
        $check_user = $conn->query("SELECT * FROM users WHERE username = '$username'");
        if ($check_user->num_rows > 0) {
            $error_msg = "Username already taken. Please choose another.";
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert the new user into the database
            $insert_user = $conn->prepare("INSERT INTO users (username, password, created_at) VALUES (?, ?, NOW())");
            $insert_user->bind_param("ss", $username, $hashed_password);

            if ($insert_user->execute()) {
                // Successful registration, redirect to the login page
                $_SESSION['user_id'] = $conn->insert_id; // Get the ID of the newly created user
                header("Location: login.php"); // Redirect to login page
                exit();
            } else {
                $error_msg = "Error registering user. Please try again.";
            }
        }
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for BondSpace</title>
    <link rel="stylesheet" type="text/css" href="css/login_or_register.css"> <!-- Link to the CSS file -->
</head>
<body>
    <div class="container"> <!-- Added container for styling -->
        <h1>Register</h1>

        <?php if ($error_msg): ?>
            <p class="error"><?php echo $error_msg; ?></p> <!-- Added class for error styling -->
        <?php endif; ?>

        <form method="POST" action="register.php" onsubmit="return validateForm()">
            <label>Username:</label><br>
            <input type="text" id="username" name="username" required><br>
            <label>Password:</label><br>
            <input type="password" id="password" name="password" required><br>
            <label>Confirm Password:</label><br> <!-- Added Confirm Password field -->
            <input type="password" id="confirm_password" name="confirm_password" required><br>
            <button type="submit">Register</button>
        </form>
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <script>
        function validateForm() {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Check if username is filled
            if (username === '') {
                alert('Username is required.');
                return false;
            }

            // Check if password meets a minimum length requirement
            if (password.length < 4) {
                alert('Password must be at least 4 characters long.');
                return false;
            }

            // Check if passwords match
            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                return false;
            }

            // If all checks pass, allow the form to be submitted
            return true;
        }
    </script>
</body>
</html>
