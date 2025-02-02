<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['post_id'])) {
    echo "Error: Post ID is missing.";
    exit();
}

$post_id = intval($_GET['post_id']);
$user_id = $_SESSION['user_id'];

// Fetch the post details
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    echo "Error: Post not found.";
    exit();
}

// Check if the user is the creator of the post
if ($post['user_id'] != $user_id) {
    echo "Error: You are not allowed to edit this post.";
    exit();
}

// Handle the update logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image_path = $post['image']; // Default to current image

    // Check if a new image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Validate and upload the new image
        $target_dir = "../uploads/"; // Directory where images will be stored
        $target_file = $target_dir . basename($_FILES['image']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file type (you can add more validation if needed)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file; // Update the image path if upload is successful
            } else {
                echo "Error: There was an error uploading the image.";
            }
        } else {
            echo "Error: Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }

    // Update the post
    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $content, $image_path, $post_id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../view_bond.php?bond_id=" . $post['bond_id']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Post</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/home.css">
    <style>
        /* General form styling */


        /* Form title */
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-family: Arial, sans-serif;
        }

        /* Input fields and textarea styling */

        input[type="file"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            color: var(--text-color);
            font-family: Arial, sans-serif;
        }


        input[type="file"]:focus {
            border-color: #4a90e2;
            outline: none;
        }

        /* Textarea specific styling */

        /* Current Image styling */
        img {
            width: 100%;
            height: auto;
            margin-top: 10px;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Labels and small titles */
        p {
            margin: 10px 0 5px;
            font-weight: bold;
            color: #666;
            font-family: Arial, sans-serif;
        }



        /* Responsive design adjustments */
        @media (max-width: 600px) {
            form {
                padding: 15px;
            }

            button[type="submit"] {
                font-size: 14px;
            }
        }
    </style>
    <script src="../script/dark-mode.js" defer></script>

</head>

<body>


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

    <div class="main-content">


        <div class="page-title">
            <h1>
                <button onclick="goBack()" style="background: none; border: none; cursor: pointer;" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                </button>


            </h1>
        </div>
     


        <div class="post-box">
            <h1>Edit Post</h1>
            <form method="POST" class="create-bond-form" enctype="multipart/form-data">
                <label for="name">Title: </label><br>

                <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                <label for="description">Content: </label><br>

                <textarea name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                <?php if ($post['image']): ?>
                    <p><strong>Current Image:</strong></p>
                    <img src="../uploads/<?php echo $post['image']; ?>" style="width: 100%; height: auto;" alt="Post image">
                <?php endif; ?>

                <p><strong>Change/Add Image:</strong></p>
                <input type="file" name="image" accept="image/*">
                <button type="submit">Update Post</button>
            </form>
        </div>
    </div>
</body>

</html>