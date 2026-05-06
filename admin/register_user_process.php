<?php
session_start();
include('db_conn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password']; // Recommendation: Use password_hash() for security
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // I-check kung existing na ang username
    $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE username='$user'");

    if (mysqli_num_rows($checkUser) > 0) {
        header("Location: add_user.php?error=Username already exists");
    } else {
        // I-save sa database (Siguraduhin na 'users' ang table name mo)
        $sql = "INSERT INTO users (username, password, role) VALUES ('$user', '$pass', '$role')";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: users.php?success=New user registered");
        } else {
            header("Location: add_user.php?error=Registration failed");
        }
    }
}
?>