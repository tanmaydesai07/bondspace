<?php
session_start();
include '../db.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if bond_id is provided
if (!isset($_POST['bond_id'])) {
    echo "Error: Bond ID is missing.";
    exit();
}
$bond_id = intval($_POST['bond_id']);
$user_id = $_SESSION['user_id'];

function handlePostCreation($conn, $bond_id, $user_id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        
        // Initialize image variable
        $image = null;

        // Validate and upload image if it is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $image = $_FILES['image']['name'];
            $target = "../uploads/" . basename($image);
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                // Successfully uploaded the image
            } else {
                // Handle error in moving the uploaded file
                echo "Error: Failed to upload image.";
                exit();
            }
        }

        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO posts (title, content, bond_id, user_id, image) VALUES (?, ?, ?, ?, ?)");
        // Use 'ssiss' type specifier for prepared statement, where image can be null
        $stmt->bind_param("ssiss", $title, $content, $bond_id, $user_id, $image);
        $stmt->execute();
        $stmt->close();
        
        header("Location: ../view_bond.php?bond_id=$bond_id&message=Post created successfully");
        exit();
    }
}

handlePostCreation($conn, $bond_id, $user_id);

?>
