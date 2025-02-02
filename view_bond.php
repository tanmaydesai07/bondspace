<?php
session_start();
include 'db.php';

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

// Fetch bond details using prepared statement
$stmt = $conn->prepare("SELECT * FROM bonds WHERE id = ?");
$stmt->bind_param("i", $bond_id);
$stmt->execute();
$bond = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$bond) {
    echo "Error: Bond not found.";
    exit();
}

// Check if user is a member of the bond
$stmt = $conn->prepare("SELECT * FROM bond_members WHERE user_id = ? AND bond_id = ?");
$stmt->bind_param("ii", $user_id, $bond_id);
$stmt->execute();
$is_member = $stmt->get_result()->num_rows > 0;
$stmt->close();

// Check if the user is the creator of the bond
$is_creator = $bond['created_by'] == $user_id;

// Handle "Join Bond" or "Leave Bond" action
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['join_bond']) && !$is_member) {
        $stmt = $conn->prepare("INSERT INTO bond_members (user_id, bond_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $bond_id);
        $stmt->execute();
        $stmt->close();
        $is_member = true;
    } elseif (isset($_POST['leave_bond']) && $is_member) {
        $stmt = $conn->prepare("DELETE FROM bond_members WHERE user_id = ? AND bond_id = ?");
        $stmt->bind_param("ii", $user_id, $bond_id);
        $stmt->execute();
        $stmt->close();
        $is_member = false;
    } elseif (isset($_POST['delete_bond']) && $is_creator) {
        echo "<script>
                if (confirm('Are you sure you want to delete this bond? This action cannot be undone.')) {
                    window.location.href = 'delete_bond.php?bond_id={$bond_id}';
                }
              </script>";
    }
}

// Fetch posts based on search
$search_term = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$query = "SELECT posts.*, users.username, 
          (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) AS comments_count 
          FROM posts 
          JOIN users ON posts.user_id = users.id 
          WHERE bond_id = ?";

if ($search_term) {
    $query .= " AND (posts.title LIKE ? OR posts.content LIKE ?)";
}

$query .= " ORDER BY posts.created_at DESC";

$stmt = $conn->prepare($query);
if ($search_term) {
    $like_term = "%$search_term%";
    $stmt->bind_param("iss", $bond_id, $like_term, $like_term);
} else {
    $stmt->bind_param("i", $bond_id);
}
$stmt->execute();
$matching_posts = $stmt->get_result();
$stmt->close();

// Fetch member count and creation date
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM bond_members WHERE bond_id = ?");
$stmt->bind_param("i", $bond_id);
$stmt->execute();
$member_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$creation_date = date("F j, Y", strtotime($bond['created_at']));

// Fetch member count and creation date
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM bond_members WHERE bond_id = ?");
$stmt->bind_param("i", $bond_id);
$stmt->execute();
$member_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$creation_date = date("F j, Y", strtotime($bond['created_at']));

// Fetch all members of the bond
$stmt = $conn->prepare("SELECT users.username FROM bond_members INNER JOIN users ON bond_members.user_id = users.id WHERE bond_members.bond_id = ?");
$stmt->bind_param("i", $bond_id);
$stmt->execute();
$members_result = $stmt->get_result();
$stmt->close();

// Store member usernames in an array
$members = [];
while ($row = $members_result->fetch_assoc()) {
    $members[] = htmlspecialchars($row['username']); // Escape output for security
}



// Assuming you have a likes table with 'user_id' and 'post_id' columns
$liked_posts = [];
$liked_stmt = $conn->prepare("SELECT post_id FROM likes WHERE user_id = ?");
$liked_stmt->bind_param("i", $user_id);
$liked_stmt->execute();
$liked_result = $liked_stmt->get_result();

while ($row = $liked_result->fetch_assoc()) {
    $liked_posts[] = $row['post_id'];
}

$liked_stmt->close();

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

// Fetch bond information along with creator's name
$query = "SELECT bonds.*, users.username AS creator_name 
          FROM bonds 
          JOIN users ON bonds.created_by = users.id 
          WHERE bonds.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bond_id); // Assuming $bond_id is defined
$stmt->execute();
$bond = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($bond['name']); ?></title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Modal background overlay */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            /* Adds a nice blur effect to the background */
            transition: opacity 0.3s ease;
        }

        /* Modal content */
        .modal-content {
            background-color: var(--tertiary-color);
            margin: 5% auto;
            padding: 20px;
            width: 90%;
            max-width: 500px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: slideDown 0.4s ease;
            /* Slide-down animation */
        }

        /* Slide-down animation */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Close button */
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            color: #888;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: var(--text-color);
        }

        /* Title styling */
        .modal-content h3 {
            font-size: 1.8em;
            color: #333;
            border-bottom: 2px solid #6200ea;
            padding-bottom: 8px;
            margin-bottom: 20px;
            color: var(--text-color);
        }
        .modal-content form{
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        /* Input fields */
        .modal-content input[type="text"],
        .modal-content textarea,
        .modal-content input[type="file"] {
            font-size: 1em;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 90%;
            align-items: center;
            background-color: var(--tertiary-color);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .modal-content input[type="text"]:focus,
        .modal-content textarea:focus,
        .modal-content input[type="file"]:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 4px 8px rgba(98, 0, 234, 0.2);
        }

        /* Textarea resize and styling */
        .modal-content textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
            border-color: var(--secondary-color);
        }

        /* Label styling */
        .modal-content label {
            font-size: 0.9em;
            color: #555;
            margin-top: 10px;
        }

        /* Submit button */
        .modal-content button[type="submit"] {
            padding: 12px;
            background-color: var(--secondary-color);
            color: #fff;
            font-size: 1em;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            align-items: center;
            width: 90%;
            transition: background-color 0.3s ease;
            margin-top: 15px;
            transition: transform 0.3s ease;
        }

        .modal-content button[type="submit"]:hover {
            cursor: pointer;
            transform: scale(1.05);
        }

        /* Placeholder text styling */
        .modal-content input[type="text"]::placeholder,
        .modal-content textarea::placeholder,
        .modal-content input[type="file"] {
            color: var(--text-color);
            font-size: 0.9em;
        }
        .modal-content input[type="text"],
        .modal-content textarea,
        .modal-content input[type="file"] {
            color: var(--text-color);
            font-size: 0.9em;
        }

        /* Media query for responsiveness */
        @media (max-width: 500px) {
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }

        .fa-trash {
            font-size: 18px;
            transition: color 0.3s;
            color: var(--secondary-text-color);
        }
        
        /* Change color on hover */
        .fa-trash:hover {
            color: #d9534f;
            
            /* Darker red for a visual effect */
        }

        .fa-pen {
            font-size: 18px;
            transition: color 0.3s;
            color: var(--secondary-text-color);
        }

        .fa-pen:hover {
            color: var(--secondary-color);
            /* Darker red for a visual effect */
        }

        .edit-button {
            background: none;
            border: none;
            cursor: pointer;
        }


        .right-sidebar button {
            background-color: var(--secondary-color);
            color: var(--text-color);
            border: none;
            padding: 10px 10px;
            border-radius: 10px;
            font-size: 12px;
            cursor: pointer;
            margin-left : 19px;
            display: block;
            width: 70%;
            color: white;
            transition: background-color 0.3s;
            align-items: center;
            text-decoration: none;
        }

        .right-sidebar button:hover {
            background-color: red;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        p,h3,ul {
            color: var(--text-color);
        }
        
    </style>
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
                        <i class="fas fa-home"></i> Home
                    </div>
                </li>
                <li>
                    <div class="menu-item" onclick="window.location.href='explore.php'">
                        <i class="fa-solid fa-arrow-trend-up"></i> Explore
                    </div>
                </li>
                <li>
                    <div class="menu-item" onclick="window.location.href='joined_bonds.php'">
                        <i class="fa-solid fa-link"></i> Joined Bonds
                    </div>
                </li>
                <li>
                    <div class="menu-item" onclick="window.location.href='view_bond.php?bond_id=<?php echo $bond_id; ?>&view_members=1'">
                        <i class="fa-solid fa-users"></i> View All Members
                    </div>
                </li>
            </ul>
            <form method="POST" action="logout.php">
                <button type="submit">Logout</button>
            </form>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <form action="result.php" method="GET" class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="query" placeholder="Search for Bonds, Posts" required />
                <button type="submit" style="display: none;"></button> <!-- Hidden button for form submission -->
            </form>
            <br>
            <!-- Search Bar for Posts -->
            <div class="page-title">

                <button onclick="goBack()" style="background: none; border: none; cursor: pointer;" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="name">
                    <h1>
                        <?php echo htmlspecialchars($bond['name']); ?>
                    </h1>

                </div>

                </h1>
            </div>
            <p style="margin-left: 15px;"><strong>Description:</strong> <?php echo htmlspecialchars($bond['description']); ?></p>



            <!-- Create New Post Form -->
            <?php if ($is_member || $is_creator): ?>
                <div class="create-post">
                    <a class="create-bond-link" onclick="openModal()">
                        <i class="fas fa-plus create-bond-icon"></i> Create a Post
                    </a>
                </div>

                <!-- Modal structure -->
                <div class="modal" id="postModal">
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <h3>Create a New Post</h3>
                        <form method="post" action="posts/create_posts.php" enctype="multipart/form-data">
                            <input type="hidden" name="bond_id" value="<?php echo $bond_id; ?>">
                            <input type="text" name="title" placeholder="Post Title" required>
                            <textarea name="content" placeholder="What's on your mind?" required></textarea><br>
                            <label for="image">Upload an image (optional):</label>
                            <input type="file" name="image"><br>
                            <button type="submit" name="create_post">Post</button>
                        </form>
                    </div>
                </div>

            <?php endif; ?>

            <h2>Posts</h2>

            <?php if ($matching_posts->num_rows > 0): ?>
                <?php while ($post = $matching_posts->fetch_assoc()): ?>
                    <div class="post-box">
                        <div class="user-info">
                            <?php echo htmlspecialchars($post['username']); ?> - <?php echo time_ago($post['created_at']); ?>
                        </div>
                        <div class="post-title" onclick="window.location.href='posts/post.php?id=<?php echo $post['id']; ?>'">
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        </div>
                        <div class="post-content">
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                        </div>
                        <?php if ($post['image']): ?>
                            <img src="uploads/<?php echo $post['image']; ?>" style="width: 100%; height: auto;" alt="Post image" onclick="window.location.href='posts/post.php?id=<?php echo $post['id']; ?>'">
                        <?php endif; ?>
                        <div class="post-actions">
                            <div class="action">
                                <i class="fas fa-heart"></i> <?php echo htmlspecialchars($post['likes']); ?>
                            </div>
                            <div class="action">
                                <i class="fas fa-comment"></i> <?php echo htmlspecialchars($post['comments_count']); ?>
                            </div>
                        </div>

                        <br>

                        <!-- Delete Button -->
                        <?php
                        $is_post_creator = $post['user_id'] == $user_id;
                        $is_admin = $is_creator;

                        if ($is_post_creator || $is_admin): ?>
                            <a href="posts/delete_post.php?post_id=<?php echo $post['id']; ?>"
                                onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.');"
                                style="text-decoration: none;">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php endif; ?>
                        <!-- Edit button -->
                        <?php if ($is_post_creator == $user_id) : ?>
                            <button class="edit-button" style="text-decoration: none;" onclick="window.location.href='posts/edit_post.php?post_id=<?php echo $post['id']; ?>'">
                                <i class="fas fa-pen"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <p>No posts available.</p>
            <?php endif; ?>
            <footer>
                <p>Created by your community team</p>
            </footer>
        </div>

        <!-- Footer -->

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="section">
                <h3>Bond Information</h3>
                <p><strong>Total Members:</strong> <?php echo $member_count; ?></p>
                <p><strong>Date Created:</strong> <?php echo $creation_date; ?></p>
                <p><strong>Creator:</strong> <?php echo htmlspecialchars($bond['creator_name']); ?></p>
                <!-- Join/Leave Bond Button -->
                <?php if ($is_creator): ?>
                    <form method="post">
                        <button type="submit" name="delete_bond">Delete Bond</button>
                    </form>
                <?php elseif ($is_member): ?>
                    <p>You are a member of this bond.</p>
                    <form method="post">
                        <button type="submit" name="leave_bond" class="">Leave Bond</button>
                    </form>
                <?php else: ?>
                    <form method="post">
                        <button type="submit" name="join_bond">Join Bond</button>
                    </form>
                <?php endif; ?>

            </div>
            <div class="section">
                <?php if (isset($_GET['view_members'])): ?>
                    <h3>Members List</h3>
                    <ul>
                        <?php foreach ($members as $member): ?>
                            <li><?php echo $member; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <script>
        function openModal() {
            document.getElementById("postModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("postModal").style.display = "none";
        }

        // Close the modal if the user clicks outside the modal content
        window.onclick = function(event) {
            var modal = document.getElementById("postModal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>

</html>





</body>

</html>