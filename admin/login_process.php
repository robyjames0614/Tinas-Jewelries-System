<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('../db_conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Check password (Plain text comparison)
        if ($password == $user['password'] || password_verify($password, $user['password'])) {
            
            // --- ETO ANG KRITIKAL NA LINYA ---
            // Siguraduhin na 'id' ang column name sa table mo. 
            // Kung 'user_id' ang nasa DB, palitan ito ng $user['user_id']
            $_SESSION['admin_id'] = $user['id']; 
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = strtolower(trim($user['role']));

            // Debugging: Tingnan natin kung na-save ba ang session
            if(isset($_SESSION['admin_id'])) {
                header("Location: dashboard.php");
                exit();
            } else {
                die("Session failed to save admin_id. Check your PHP session settings.");
            }
            
        } else {
            die("Maling Password! <a href='login.php'>Balik</a>");
        }
    } else {
        die("Username hindi nahanap sa database! <a href='login.php'>Balik</a>");
    }
}
?>