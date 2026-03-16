<?php
session_start();
include('db_conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_session_user = $_SESSION['username'];
    $new_username = mysqli_real_escape_string($conn, $_POST['new_username']);
    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);

    // 1. I-verify kung tama ang password sa 'users' table
    $check_sql = "SELECT * FROM users WHERE username='$current_session_user' AND password='$current_password'";
    $result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($result) > 0) {
        // 2. I-prepare ang update query
        if (!empty($new_password)) {
            // Magpapalit ng username at password
            $update_sql = "UPDATE users SET username='$new_username', password='$new_password' WHERE username='$current_session_user'";
        } else {
            // Username lang ang papalitan
            $update_sql = "UPDATE users SET username='$new_username' WHERE username='$current_session_user'";
        }

        if (mysqli_query($conn, $update_sql)) {
            $_SESSION['username'] = $new_username; // I-update ang session para hindi ma-logout
            echo "<script>alert('Success: Profile updated!'); window.location='profile.php';</script>";
        } else {
            echo "<script>alert('Error updating database.'); window.history.back();</script>";
        }
    } else {
        // Maling password
        echo "<script>alert('Error: Incorrect current password.'); window.history.back();</script>";
    }
} else {
    header("Location: profile.php");
    exit();
}
?>