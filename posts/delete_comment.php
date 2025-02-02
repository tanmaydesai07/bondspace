<?php
session_start();
include '../db.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Error: You must be logged in to delete comments.";
    exit();
}

// Check if the POST request contains the necessary data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_id'])) {
    $user_id = $_SESSION['user_id'];
    $comment_id = intval($_POST['comment_id']);
    $post_id = intval($_POST['post_id']); // Get post_id to redirect back later

    // Fetch the comment to check the owner
    $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();
    $stmt->close();

    // Check if the comment exists and if the user is the creator
    if ($comment && $comment['user_id'] == $user_id) {
        // Delete the comment
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $stmt->close();

        // Redirect back to the post page
        header("Location: post.php?id=" . $post_id);
        exit();
    } else {
        echo "Error: You are not allowed to delete this comment.";
    }
} else {
    echo "Error: Invalid request.";
}

$conn->close();
?>
