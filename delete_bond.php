<?php
session_start();
include 'db.php'; // Include your database connection

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Verify that 'bond_id' is set in the URL and is valid
if (!isset($_GET['bond_id']) || empty($_GET['bond_id'])) {
    echo "Error: Bond ID is missing or invalid.";
    exit();
}

$bond_id = intval($_GET['bond_id']);
$user_id = $_SESSION['user_id'];

// Fetch bond details to check if the user is the creator
$bond = $conn->query("SELECT * FROM bonds WHERE id = $bond_id")->fetch_assoc();

// Check if the bond exists and if the logged-in user is the creator
if (!$bond || $bond['created_by'] != $user_id) {
    echo "Error: You are not authorized to delete this bond.";
    exit();
}

// Begin a transaction to ensure data integrity
$conn->begin_transaction();

try {
    // First, fetch all posts associated with the bond to get their IDs
    $posts = $conn->query("SELECT id FROM posts WHERE bond_id = $bond_id");

    // Delete likes and comments associated with each post
    while ($post = $posts->fetch_assoc()) {
        $post_id = $post['id'];
        
        // Delete likes for the post
        $conn->query("DELETE FROM likes WHERE post_id = $post_id");

        // Delete comments for the post
        $conn->query("DELETE FROM comments WHERE post_id = $post_id");
    }

    // Now delete all posts associated with the bond
    $conn->query("DELETE FROM posts WHERE bond_id = $bond_id");

    // Delete all members associated with the bond
    $conn->query("DELETE FROM bond_members WHERE bond_id = $bond_id");

    // Finally, delete the bond itself
    if ($conn->query("DELETE FROM bonds WHERE id = $bond_id") === TRUE) {
        // Commit the transaction
        $conn->commit();
        echo "Bond deleted successfully.";
        // Redirect to a success page or back to the bond list
        header("Location: joined_bonds.php"); // Change to your bond list page
        exit();
    } else {
        throw new Exception("Error deleting the bond.");
    }
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
?>
