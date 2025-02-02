<?php
session_start();
include '../db.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Error: You must be logged in to comment.";
    exit();
}

// Check if the POST request contains the necessary data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_id']) && isset($_POST['content'])) {
    $user_id = $_SESSION['user_id'];
    $post_id = intval($_POST['post_id']);
    $content = trim($_POST['content']);

    // Validate the content
    if (empty($content)) {
        echo "Error: Comment cannot be empty.";
        exit();
    }

    // Prepare and execute the insertion of the comment
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $post_id, $user_id, $content);

    if ($stmt->execute()) {
        // Redirect back to the post page after adding the comment
        header("Location: post.php?id=" . $post_id);
        exit();
    } else {
        echo "Error: Could not add comment.";
    }
    
    $stmt->close();
} else {
    echo "Error: Invalid request.";
}

$conn->close();
?>
