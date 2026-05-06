<?php
session_start();
include('db_conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if ($password == $user['password'] || password_verify($password, $user['password'])) {
            
            // --- ETO ANG FIX: Dapat may admin_id ---
            $_SESSION['admin_id'] = $user['id']; 
            $_SESSION['username'] = $user['username'];
            
            $user_role = trim(strtolower($user['role']));
            $_SESSION['role'] = $user_role;

            if ($user_role === 'admin' || $user_role === 'staff') {
                echo "<script>
                    alert('Welcome Admin/Staff!');
                    window.location.href='dashboard.php';
                </script>";
            } else {
                echo "<script>
                    alert('Welcome Client! Redirecting to shop...');
                    window.location.href='../index.php';
                </script>";
            }
            exit();
        } else {
            echo "<script>alert('Mali ang Password!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Username not found!'); window.history.back();</script>";
    }
}
?>