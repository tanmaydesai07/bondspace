<?php
// Include the database connection file
include 'includes/db.php';
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$username = $_SESSION['username'];
$email = '';

// Fetch user information from the database
$stmt = $conn->prepare("SELECT email FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

// Update profile information
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = $_POST['email'];

    // Simple validation
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        // Update the email in the database
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE username = ?");
        $stmt->bind_param("ss", $new_email, $username);
        if ($stmt->execute()) {
            $message = "Profile updated successfully.";
            $email = $new_email; // Update the displayed email
        } else {
            $message = "Error updating profile.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #f02849;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #d02039;
        }
        .message {
            color: red;
            text-align: center;
        }
        .success {
            color: green;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Profile</h2>
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
            <button type="submit">Update Profile</button>
        </form>
        <p style="text-align: center;">Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong></p>
        <p style="text-align: center;"><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>
