<?php
// I-check kung nasa localhost o nasa online server
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    // LOCALHOST SETTINGS (XAMPP)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "tinas_jewelry"; // Inalis ang "_db" para mag-match sa phpMyAdmin mo
} else {
    // ONLINE HOSTING SETTINGS (InfinityFree)
    $servername = "sql207.infinityfree.com";
    $username = "if0_41887001";
    $password = "rjames0614";
    $dbname = "if0_41887001_tinasdb";
}

$conn = new mysqli($servername, $username, $password, $dbname);

// Para sa mga special characters (halimbawa yung ₱ symbol)
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>