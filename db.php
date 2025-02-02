<?php
// db.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bondspace";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
else {
    // echo "Connected successfully";
}
?>
