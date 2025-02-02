<?php
session_start();
include '../db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Ensure post ID is in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Error: Post ID is missing.";
    exit();
}

$post_id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);

// Check if the user has liked this post
$isLikedStmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE user_id = ? AND post_id = ?");
$isLikedStmt->bind_param("ii", $user_id, $post_id);
$isLikedStmt->execute();
$isLikedResult = $isLikedStmt->get_result()->fetch_assoc();
$isLiked = $isLikedResult['like_count'] > 0; // true if user has liked the post
$isLikedStmt->close();

// Fetch post details with bond and user info
$stmt = $conn->prepare("
    SELECT p.*, b.name AS bond_name, u.username 
    FROM posts p
    JOIN bonds b ON p.bond_id = b.id
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check if post exists
if (!$post) {
    echo "Error: Post not found.";
    exit();
}

// Fetch comments for the post
$comments_stmt = $conn->prepare("
    SELECT c.*, u.username 
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ?
    ORDER BY c.created_at DESC
");
$comments_stmt->bind_param("i", $post_id);
$comments_stmt->execute();
$comments = $comments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$comments_stmt->close();

// Format time function
function time_ago($timestamp)
{
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);

    if ($seconds < 60) {
        return "Just Now";
    } else if ($minutes < 60) {
        return ($minutes == 1) ? "one minute ago" : "$minutes minutes ago";
    } else if ($hours < 24) {
        return ($hours == 1) ? "an hour ago" : "$hours hours ago";
    } else if ($days < 7) {
        return ($days == 1) ? "yesterday" : "$days days ago";
    } else {
        return ($weeks == 1) ? "a week ago" : "$weeks weeks ago";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['title']); ?> - BondSpace</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>

       p{
        color: var(--secondary-text-color);
       }
       p a{
        color: var(--text-color);
        font-weight: bold;
       }
       
        /* Comment Form */
        .comment-form {
            background-color: var(--tertiary-color);
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flexbox;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .comment-form textarea {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            resize: vertical;
            background-color: var(--tertiary-color);
            color: var(--text-color)
        }

        .comment-form .submit-btn {
            background-color: var(--secondary-color);
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .comment-form .submit-btn:hover {
            cursor: pointer;
        }

        /* Style for the delete icon */
        .delete-icon {
            background: none;
            border: none;
            cursor: pointer;
            color: #333;
            /* Red color for delete */
            font-size: 1em;
            transition: color 0.3s ease;
        }

        .delete-icon:hover {
            color: #c82333;
            /* Darker red on hover */
        }

        /* Style for the like icon */
        #like-icon {
            cursor: pointer;
          
            /* Default color for unliked */
            font-size: 1.5em;
            transition: color 0.3s ease;
        }

        
        

        #like-button .liked {
            color: #6e0fb3;
            /* Change to red or any preferred color */
        }
        .liked {
            color: #6e0fb3;
        }
        .comment-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .fa-trash {
            font-size: 20px;
        }
    </style>
        <script src="../script/dark-mode.js" defer></script>

</head>

<body>
    <div class="container">
        <!-- Left Sidebar -->
        <div class="left-sidebar">
            <div class="logo" onclick="window.location.href='../index.php'">
                <img src="../assets/bondspacelogo.png" alt="Logo">
                BondSpace
            </div>
            <hr size="2" width="70%" color="6e0fb3" style="margin-top: 10px;">
            <ul>
                <li>
                    <div class="menu-item" onclick="window.location.href='../home.php'">
                        <i class="fas fa-home">
                        </i>
                        Home

                    </div>
                </li>
                <li>
                    <div class="menu-item" onclick="window.location.href='../explore.php'">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                        Explore
                    </div>


                </li>
                <li>
                    <div class="menu-item" onclick="window.location.href='../joined_bonds.php'"><i class="fa-solid fa-link"></i>Joined Bonds
                    </div>
                </li>
                <!-- Add more menu items as needed -->
            </ul>
            <form method="POST" action="../logout.php">
                <button type="submit">Logout</button>
            </form>
        </div>


        <!-- Main Content -->
        <div class="main-content">

            <div class="post-box">

                <h1><?php echo htmlspecialchars($post['title']); ?></h1>

                <p><strong>Bond:</strong> <a href="../view_bond.php?bond_id=<?php echo $post['bond_id']; ?>"><?php echo htmlspecialchars($post['bond_name']); ?></a></p>
                <div class="user-info">
                    <br />
                    <p><strong>Posted by:</strong> <?php echo htmlspecialchars($post['username']); ?> - <?php echo time_ago($post['created_at']); ?>

                    </p>
                </div>
                <div class="post-content">
                    <?php echo htmlspecialchars($post['content']); ?>
                </div>
                <?php if ($post['image']): ?>

                <img src="../uploads/<?php echo htmlspecialchars($post['image']); ?>" style="width: 100%; height: auto;" alt="Post Image">

                <?php endif; ?>
                <p class="post-stats">
                    <i id="like-icon"
                    class="fa fa-thumbs-up <?php echo $isLiked ? 'liked' : ''; ?>"
                    onclick="likePost(<?php echo $post_id; ?>)">
                </i>
                <span id="like-count"><?php echo htmlspecialchars($post['likes']); ?></span>
                </p>
                
            </div>

            <h2>Comments</h2>
            <div class="post-box">

                <!-- Comment Form -->
                <form method="POST" action="add_comment.php" class="comment-form">
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                    <textarea name="content" required placeholder="Write your comment here..."></textarea>
                    <button type="submit" name="add_comment" class="submit-btn">Add Comment</button>
                </form>

                <?php if (empty($comments)): ?>
                    <p>No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-title">
                                <div class="item">


                                    <p><strong><?php echo htmlspecialchars($comment['username']); ?></strong> - <?php echo time_ago($comment['created_at']); ?></p>
                                </div>
                                <div class="item">

                                    <?php if ($comment['user_id'] == $_SESSION['user_id']): // Check if the logged-in user is the creator of the comment
                                    ?>
                                        <form method="POST" action="delete_comment.php" style="display:inline;">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        
                                            <button type="submit" name="delete_comment" class="delete-icon">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                </div>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                        </div>
                        
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Comment Form -->


        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="section">
                <h2>Popular Bonds</h2>
                <ul>
                    <?php
                    $popular_communities = $conn->query("SELECT * FROM bonds ORDER BY members_count DESC LIMIT 5");
                    while ($bond = $popular_communities->fetch_assoc()) { ?>
                        <li><a href="../view_bond.php?bond_id=<?php echo $bond['id']; ?>"><?php echo htmlspecialchars($bond['name']); ?></a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function likePost(postId) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "../like_post.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.getElementById("like-count").innerText = response.newLikesCount;
                            const likeIcon = document.getElementById("like-icon");
                            if (response.action === "liked") {
                                likeIcon.classList.add("liked");
                            } else if (response.action === "unliked") {
                                likeIcon.classList.remove("liked");
                            }
                        } else {
                            alert("Error: " + response.error);
                        }
                    } catch (e) {
                        console.error("Error parsing JSON response:", e);
                        alert("An unexpected error occurred.");
                    }
                }
            };

            xhr.send("post_id=" + postId);
        }
    </script>
</body>

</html>