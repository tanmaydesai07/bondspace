<?php
session_start();
include 'db.php';

if (isset($_GET['category'])) {
    $category = $_GET['category'];
// Modify the SQL query in fetch_bonds.php to fetch the id and name
$stmt = $conn->prepare("SELECT id, name FROM bonds WHERE category = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bonds = [];
    while ($row = $result->fetch_assoc()) {
        $bonds[] = $row;
    }

    echo json_encode($bonds);
}
?>
