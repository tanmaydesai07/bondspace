<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize input data
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $creator_id = $_SESSION['user_id'];

    // Prepare and bind statement to prevent SQL injection
    if ($stmt = $conn->prepare("INSERT INTO bonds (name, description, category, created_by) VALUES (?, ?, ?, ?)")) {
        $stmt->bind_param("sssi", $name, $description, $category, $creator_id);

        // Execute the statement
        if ($stmt->execute()) {
            // Get the ID of the newly created bond
            $new_bond_id = $stmt->insert_id;

            // Redirect to the newly created bond's page
            header("Location: view_bond.php?bond_id=" . $new_bond_id);
            exit();
        } else {
            echo "Error: Could not create bond. Please try again.";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error: Could not prepare statement.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create a Bond</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="script/dark-mode.js" defer></script>

</head>

<body>
    <div class="container">
        <!-- Left Sidebar -->
        <div class="left-sidebar">
            <div class="logo" onclick="window.location.href='index.php'">
                <img src="assets/bondspacelogo.png" alt="Logo">
                BondSpace
            </div>
            <hr size="2" width="70%" color="6e0fb3" style="margin-top: 10px;">
            <ul>
                <li>
                    <div class="menu-item" onclick="window.location.href='home.php'">
                        <i class="fas fa-home">
                        </i>
                        Home

                    </div>
                </li>
                <li>
                    <div class="menu-item" onclick="window.location.href='explore.php'">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                        Explore
                    </div>


                </li>
                <li>
                    <div class="menu-item" onclick="window.location.href='joined_bonds.php'"><i class="fa-solid fa-link"></i>Joined Bonds
                    </div>
                </li>
                <!-- Add more menu items as needed -->
            </ul>
            <form method="POST" action="logout.php">
                <button type="submit">Logout</button>
            </form>
        </div>
        <!-- Main Content -->
        <div class="main-content">
        <div class="page-title">
                <h1>
                    <button onclick="goBack()" style="background: none; border: none; cursor: pointer;" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    

                </h1>
            </div>
            <br>
            <div class="post-box">
                <h1>Create A New Bond</h1>
                <form method="POST" action="createbond.php" class="create-bond-form">
                    <label for="name">Bond Name:</label><br>
                    <input type="text" name="name" id="name" required><br>
                    <label for="description">Description:</label><br>
                    <textarea name="description" id="description" required></textarea><br>
                    <label for="category">Category:</label><br>
                    <select name="category" id="category" required>
                        <option value="Technology">Technology</option>
                        <option value="Health">Health</option>
                        <option value="Education">Education</option>
                        <option value="Sports">Sports</option>
                        <option value="Entertainment">Entertainment</option>
                        <!-- Add more categories as needed -->
                    </select><br>
                    <button type="submit">Create Bond</button>
                </form>
            </div>
        </div>



    </div>
</body>

</html>