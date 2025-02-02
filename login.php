<?php
session_start();
include 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; // Store user ID in session
            
            header("Location: home.php"); // Redirect to the homepage after successful login
            exit(); // Ensure no further code is executed after redirection
        } else {
            $error = "Invalid credentials!";
        }
    } else {
        $error = "Invalid credentials!";
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login to BondSpace</title>
    <link rel="stylesheet" type="text/css" href="css/login_or_register.css">
</head>

<body>
    <div class="container"> <!-- Added container for styling -->
        <h1>Login</h1>
        <form method="POST" action="login.php" onsubmit="return validateLoginForm()">
            <label>Username:</label><br>
            <input type="text" id="username" name="username" required><br>
            <label>Password:</label><br>
            <input type="password" id="password" name="password" required><br>
            <button type="submit">Login</button>
        </form>
        
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p> <!-- Added class for error styling -->
        <?php endif; ?>
        
        <p>New user? <a href="register.php">Sign up</a></p>
    </div>

    <script>
        function validateLoginForm() {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

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

            // All checks passed
            return true;
        }
    </script>
</body>
</html>
