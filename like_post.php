<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in'], JSON_UNESCAPED_UNICODE);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id']);

// Check if the post exists
$post_stmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
$post_stmt->bind_param("i", $post_id);
$post_stmt->execute();
$post = $post_stmt->get_result()->fetch_assoc();
$post_stmt->close();

if ($post) {
    // Check if the user has already liked the post
    $like_check_stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
    $like_check_stmt->bind_param("ii", $user_id, $post_id);
    $like_check_stmt->execute();
    $like_check = $like_check_stmt->get_result();
    
    if ($like_check->num_rows == 0) {
        // If not already liked, add a like
        $conn->query("INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)");
        $conn->query("UPDATE posts SET likes = likes + 1 WHERE id = $post_id");
        $action = "liked";  // Track the action for frontend
    } else {
        // If already liked, remove the like
        $conn->query("DELETE FROM likes WHERE user_id = $user_id AND post_id = $post_id");
        $conn->query("UPDATE posts SET likes = likes - 1 WHERE id = $post_id");
        $action = "unliked"; // Track the action for frontend
    }

    // Get the updated likes count
    $likes_count_stmt = $conn->prepare("SELECT likes FROM posts WHERE id = ?");
    $likes_count_stmt->bind_param("i", $post_id);
    $likes_count_stmt->execute();
    $likes_count_stmt->bind_result($new_likes_count);
    $likes_count_stmt->fetch();
    $likes_count_stmt->close();

    // Return the new likes count and action as JSON
    echo json_encode(['success' => true, 'newLikesCount' => $new_likes_count, 'action' => $action], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'error' => 'Post not found'], JSON_UNESCAPED_UNICODE);
}
?>
