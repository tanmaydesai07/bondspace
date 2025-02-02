<?php
session_start();
include '../db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Verify that 'post_id' is set in the URL and is valid
if (!isset($_GET['post_id']) || empty($_GET['post_id'])) {
    echo "Error: Post ID is missing or invalid.";
    exit();
}

$post_id = intval($_GET['post_id']);
$user_id = $_SESSION['user_id'];

// Fetch post details to check if the user is the admin or the author
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    echo "Error: Post not found.";
    exit();
}

// Fetch the bond creator to check admin status
$stmt = $conn->prepare("SELECT created_by FROM bonds WHERE id = ?");
$stmt->bind_param("i", $post['bond_id']);
$stmt->execute();
$bond = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check if the user is the post creator or the bond admin
$is_admin = ($bond['created_by'] == $user_id);
$is_creator = ($post['user_id'] == $user_id);

if (!$is_admin && !$is_creator) {
    echo "Error: You do not have permission to delete this post.";
    exit();
}

// Step 1: Delete likes associated with the post
$stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->close();

// Step 2: Now delete the post itself
$stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->close();

// Redirect back to the bond view page after deletion
header("Location: ../view_bond.php?bond_id=" . $post['bond_id']);
exit();
?>
