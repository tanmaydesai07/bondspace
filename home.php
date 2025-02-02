<?php
session_start();
// Database connection
include 'db.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Fetch trending posts
$trending_posts = $conn->query("
    SELECT p.*, u.username, COUNT(c.id) AS comments_count
    FROM posts p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN comments c ON p.id = c.post_id
    GROUP BY p.id, u.username
    ORDER BY p.likes DESC
    LIMIT 10
");

if ($trending_posts->num_rows > 0) {
    $posts = $trending_posts->fetch_all(MYSQLI_ASSOC);
} else {
    $posts = [];
}

// Function to format time
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

// Fetch popular communities
$popular_communities = $conn->query("SELECT * FROM bonds ORDER BY members_count DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>BondSpace Homepage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/home.css">
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
                    <div class="menu-item active" onclick="window.location.href='home.php'">
                        <i class="fas fa-home active">
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
            <div class="top-bar">
                <form action="result.php" method="GET" class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" name="query" placeholder="Search for Bonds, Posts" required />
                    <button type="submit" style="display: none;"></button> <!-- Hidden button for form submission -->
                </form>
                <button id="themeToggleButton">
                    <i class="fas fa-moon" width="20px"></i>
                </button>

            </div>



            <h1>Trending Posts</h1>




            <?php if (empty($posts)): ?>
                <p>No posts available at this time.</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-box" onclick="window.location.href='posts/post.php?id=<?php echo htmlspecialchars($post['id']); ?>' ">

                        <div class="user-info">
                            Posted by:
                            <?php echo htmlspecialchars($post['username']); ?> - <?php echo time_ago($post['created_at']); ?>
                        </div>

                        <div class="post-title">
                            <a href="posts/post.php?id=<?php echo htmlspecialchars($post['id']); ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                        </div>

                        <div class="post-content">
                            <?php echo htmlspecialchars($post['content']); ?>
                        </div>
                        <?php if ($post['image']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="post-image" height="500" width="300">
                        <?php endif; ?>
                        <div class="post-actions">
                            <div class="action">
                                <i class="fas fa-heart">
                                </i>
                                <?php echo htmlspecialchars($post['likes']); ?>
                            </div>

                            <div class="action">
                                <i class="fas fa-comment">
                                </i>
                                <?php echo htmlspecialchars($post['comments_count']); ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="section">
                <h2>Popular Bonds</h2>
                <ul>
                    <?php while ($bond = $popular_communities->fetch_assoc()) { ?>
                        <li><a href="view_bond.php?bond_id=<?php echo $bond['id']; ?>"><?php echo $bond['name']; ?></a></li>
                    <?php } ?>
                </ul>

                <!-- </div>
            <div class="section">
                <h2>Friends</h2>
                
            </div> -->

            </div>
        </div>
</body>

</html>