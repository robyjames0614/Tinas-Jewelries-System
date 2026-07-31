<?php
// 1. Ilagay ang session_start sa pinakataas
session_start();
include('../db_conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // Check kung may match sa database
    $query = "SELECT * FROM users WHERE fullname='$fullname' AND phone='$phone'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // 2. Gamitin ang $_SESSION, hindi $_SERVER
        $_SESSION['reset_user_id'] = $user['id'];
        
        header("Location: new-password.php");
        exit(); // Laging mag-exit pagkatapos ng header redirect
    } else {
        echo "<script>alert('Account not found. Please check your details.'); window.location='forgot-password.php';</script>";
        exit();
    }
}
?>