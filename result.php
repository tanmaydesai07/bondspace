<?php
// Include database connection
require 'db.php'; // Adjust to your database connection file

// Retrieve the search query from URL parameter
$query = $_GET['query'] ?? '';
$sanitizedQuery = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');

// Initialize arrays to hold results for bonds and posts
$bonds = [];
$posts = [];

if ($query) {
    // SQL query for bonds
    $bondQuery = "SELECT id, name, description FROM bonds WHERE name LIKE ?";
    $stmt = $conn->prepare($bondQuery);
    $searchTerm = "%$query%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $bondResult = $stmt->get_result();
    while ($row = $bondResult->fetch_assoc()) {
        $bonds[] = $row;
    }
    $stmt->close();

    // SQL query for posts
    $postQuery = "SELECT id, title, content FROM posts WHERE title LIKE ?";
    $stmt = $conn->prepare($postQuery);
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $postResult = $stmt->get_result();
    while ($row = $postResult->fetch_assoc()) {
        $posts[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Tab Bar Styling */
        .tab {
            display: flex;
            background-color: var(--secondary-text-color);
            padding: 10px;
            border-bottom: 1px solid #333;
            align-items: center;
        }

        .tab-link {
            color: var(--secondary-text-color);
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
            margin-right: 10px;
            border-radius: 20px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .tab-link.active {
            color: white;
            background-color: var(--secondary-color);
            font-weight: bold;
        }

        .tab-link.active:hover {
            color: white;
            background-color: var(--secondary-color);
            font-weight: bold;
            cursor: pointer;
        }

        .tab-link:hover {
            cursor: pointer;
            background-color: var(--tertiary-color);
        }

        /* Tab Content Styling */
        .tab-content {
            padding: 20px;
            display: none;
        }

        .dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: var(--text-color);
            /* Choose a noticeable color, like red */
            border-radius: 50%;
            margin-left: 5px;
            /* Spacing between tab text and dot */
            vertical-align: middle;
            /* Align dot with text */
        }
        .dot.active  {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: white;
            /* Choose a noticeable color, like red */
            border-radius: 50%;
            margin-left: 5px;
            /* Spacing between tab text and dot */
            vertical-align: middle;
            /* Align dot with text */
        }
        .post-box ul li strong{
            text-decoration: none;
            color: var(--text-color);
        }
        .post-box p{
            text-decoration: none;
            color: var(--text-color);
        }
        .post-box ul li strong:hover{
            text-decoration: none;
            color: var(--secondary-color);
        }
        .post-box ul li p{
            text-decoration: none;
            color: var(--secondary-text-color);
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
                    <div class="menu-item active" onclick="window.location.href='home.php'">
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

        <div class="main-content">
            <form action="result.php" method="GET" class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="query" placeholder="Search for Bonds, Posts" required />
                <button type="submit" style="display: none;"></button> <!-- Hidden button for form submission -->
            </form>

            <br>
            <h1>Search Results for "<?php echo $sanitizedQuery; ?>"</h1>
            <!-- Tabs for Bonds and Posts -->
            <div class="tabs">
                <button class="tab-link active" onclick="showTab(event, 'bonds')">
                    Bonds
                    <?php if (!empty($bonds)): ?>
                        <span class="dot active"></span>
                    <?php endif; ?>
                </button>

                <button class="tab-link" onclick="showTab(event, 'posts')">
                    Posts
                    <?php if (!empty($posts)): ?>
                        <span class="dot"></span>
                    <?php endif; ?>
                </button>
            </div>
            <br>
            <!-- Tab Content -->
            <div id="bonds" class="post-box">
                <?php if (empty($bonds)): ?>
                    <p>No bonds found for "<?php echo $sanitizedQuery; ?>"</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($bonds as $bond): ?>
                            <li>
                                <a href="view_bond.php?bond_id=<?php echo $bond['id']; ?>">
                                    <strong style="text-decoration: none;"><?php echo htmlspecialchars($bond['name']); ?></strong>
                                </a>
                                <p><?php echo htmlspecialchars($bond['description']); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div id="posts" class="post-box" style="display: none;">
                <?php if (empty($posts)): ?>
                    <p>No posts found for "<?php echo $sanitizedQuery; ?>"</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($posts as $post): ?>
                            <li>
                                <a href="posts/post.php?id=<?php echo $post['id']; ?>">
                                    <strong style="text-decoration: none;"><?php echo htmlspecialchars($post['title']); ?></strong>
                                </a>
                                <p><?php echo htmlspecialchars($post['content']); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript for Tab Switching -->
    <script>
        function showTab(event, tabName) {
            // Hide all tab contents
            const contents = document.getElementsByClassName("post-box");
            for (let content of contents) {
                content.style.display = "none";
            }

            // Remove active class from all tab links
            const links = document.getElementsByClassName("tab-link");
            for (let link of links) {
                link.classList.remove("active");
            }
            const dots = document.getElementsByClassName("dot");
            for (let dot of dots) {
                dot.classList.remove("active");
            }

            // Show the selected tab content and add active class to the clicked tab
            document.getElementById(tabName).style.display = "block";
            event.currentTarget.classList.add("active");

            // Add active class to the corresponding dot
            const dot = event.currentTarget.querySelector(".dot");
            dot.classList.add("active");
            
        }

        // Automatically show the first tab (Bonds) when the page loads
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelector(".tab-link").click();
        });
    </script>

</body>

</html>