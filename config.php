<?php
// Ilagay dito ang mga database credentials mo
$servername = "sqlxxx.infinityfree.com"; // Tignan sa iyong InfinityFree dashboard (MySQL Details)
$username   = "if0_xxxxxxx";            // MySQL User Name
$password   = "your_password";          // MySQL Password
$dbname     = "if0_xxxxxxx_tinas_db";   // MySQL Database Name

// Gumawa ng koneksyon
$conn = mysqli_connect($servername, $username, $password, $dbname);

// I-check kung may error
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>