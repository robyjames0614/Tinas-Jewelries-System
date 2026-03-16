<?php
include('db_conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // Check kung may match sa database
    $query = "SELECT * FROM users WHERE fullname='$fullname' AND phone='$phone'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        // Kung match, dalhin sa page kung saan pwedeng palitan ang password
        // Pinapasa natin ang ID via Session para safe
        session_start();
        $_SERVER['reset_user_id'] = $user['id'];
        header("Location: new-password.php");
    } else {
        echo "<script>alert('Account not found. Please check your details.'); window.location='forgot-password.php';</script>";
    }
}
?>