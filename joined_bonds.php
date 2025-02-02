<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch bonds joined by the user
$joined_bonds = $conn->query("
    SELECT b.* 
    FROM bonds b 
    INNER JOIN bond_members bm ON b.id = bm.bond_id 
    WHERE bm.user_id = $user_id
");

// Fetch bonds created by the user
$created_bonds = $conn->query("
    SELECT * 
    FROM bonds 
    WHERE created_by = $user_id
");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Joined Bonds</title>
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
            <hr size="2" width="70%" color="6e0fb3" style="margin-top: 10px;">
            <ul>
                <li>
                    <div class="menu-item" onclick="window.location.href='home.php'"><i class="fas fa-home"></i>Home</div>
                </li>
                <li>
                    <div class="menu-item" onclick="window.location.href='explore.php'"><i class="fa-solid fa-arrow-trend-up"></i>Explore</div>
                </li>
                <li>
                    <div class="menu-item active" onclick="window.location.href='joined_bonds.php'"><i class="fa-solid fa-link active"></i>Joined Bonds</div>
                </li>
            </ul>
            <form method="POST" action="logout.php">
                <button type="submit">Logout</button>
            </form>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-title">
                <h1>
                    <button onclick="goBack()" style="background: none; border: none; cursor: pointer;" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="name">
                        <?php echo "Joined Bonds"; ?>
                    </div>

                </h1>
            </div>




            <?php if ($joined_bonds->num_rows > 0): ?>
                <div class="bond-list">
                    <?php while ($bond = $joined_bonds->fetch_assoc()): ?>
                        <div class="bond-item">
                            <a href="view_bond.php?bond_id=<?php echo htmlspecialchars($bond['id']); ?>">
                                <?php echo htmlspecialchars($bond['name']); ?>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-bonds-message">You have not joined any bonds yet.</p>
            <?php endif; ?>


        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="section">
                <h2>
                    Manage Your Bonds
                </h2>
                <a href="createbond.php" class="create-bond-link">
                    <i class="fas fa-plus create-bond-icon"></i> Create a Bond
                </a>
                <div class="menu-item">
                    <?php if ($created_bonds->num_rows > 0): ?>
                        <ul>
                            <?php while ($bond = $created_bonds->fetch_assoc()): ?>
                                <li>
                                    <a href="view_bond.php?bond_id=<?php echo htmlspecialchars($bond['id']); ?>"><?php echo htmlspecialchars($bond['name']); ?></a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p>You have not created any bonds yet.</p>
                    <?php endif; ?>

                </div>

            </div>
        </div>

    </div>
</body>

</html>