<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch unique categories from the bonds table
$categories = $conn->query("SELECT DISTINCT category FROM bonds");

// Function to fetch bonds for a specific category
function fetchBonds($conn, $category)
{
    $stmt = $conn->prepare("SELECT name FROM bonds WHERE category = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Explore Bonds</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
   
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
            <hr size="2" width="70%" color="#6e0fb3" style="margin-top: 10px;">
            <ul>
                <li>
                    <div class="menu-item" onclick="window.location.href='home.php'"><i class="fas fa-home"></i>Home</div>
                </li>
                <li>
                    <div class="menu-item active" onclick="window.location.href='explore.php'"><i class="fa-solid fa-arrow-trend-up active"></i>Explore</div>
                </li>
                <li>
                    <div class="menu-item" onclick="window.location.href='joined_bonds.php'"><i class="fa-solid fa-link"></i>Joined Bonds</div>
                </li>
            </ul>
            <form method="POST" action="logout.php">
                <button type="submit">Logout</button>
            </form>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="categories">
                <div class="page-title">
                    <h1>
                        <button onclick="goBack()" style="background: none; border: none; cursor: pointer;" class="back-button">
                            <i class="fas fa-arrow-left"></i>
                        </button>

                        <div class="name">
                            <?php echo "Explore Categories"; ?>
                        </div>


                    </h1>
                </div>

                <?php if ($categories->num_rows > 0): ?>
                    <div class="category-grid">
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <?php if (!empty($category['category'])): ?>
                                <div class="category-item" onclick="toggleBonds('<?php echo htmlspecialchars($category['category']); ?>', this)">
                                    <div class="category-header">
                                        <span><?php echo htmlspecialchars($category['category']); ?></span>

                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No categories available.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <h2>Bonds</h2>
            <div class="section">
                <div class="menu-item">
                    <ul id="bondList" class="bond-list">
                        <!-- Bond names will be populated here -->
                    </ul>
                </div>


            </div>

        </div>
    </div>

    <script>
        function toggleBonds(category, element) {
            const bondList = document.getElementById('bondList');
            bondList.innerHTML = ''; // Clear previous bond names

            // Fetch bond names using AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_bonds.php?category=' + encodeURIComponent(category), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const bonds = JSON.parse(xhr.responseText);
                    if (bonds.length > 0) {
                        bonds.forEach(bond => {
                            const li = document.createElement('li');
                            const a = document.createElement('a'); // Create an anchor element
                            a.href = 'view_bond.php?bond_id=' + bond.id; // Set the URL to bond details page
                            a.textContent = bond.name; // Set the text for the bond
                            a.style.textDecoration = 'none'; // Remove underline from link
                            

                            li.appendChild(a); // Append anchor to list item
                            bondList.appendChild(li); // Append list item to the bond list
                        });
                        bondList.style.display = 'block'; // Show the bond list
                    } else {
                        bondList.style.display = 'none'; // Hide if no bonds found
                    }
                }
            };
            xhr.send();
        }


        function goBack() {
            window.history.back(); // This will take the user back to the previous page
        }
    </script>
</body>

</html>