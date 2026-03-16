<?php session_start(); if(!isset($_SERVER['reset_user_id'])) header("Location: forgot-password.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Password</title>
    <link rel="stylesheet" href="index.css">
</head>
<body style="background-color: #fffaf5;">
    <div style="max-width: 400px; margin: 100px auto; padding: 30px; background: white; border: 1px solid #d4af37;">
        <h2>Set New Password</h2>
        <form action="update_password.php" method="POST">
            <input type="password" name="new_pass" placeholder="New Password" required style="width:100%; padding:10px; margin:10px 0;">
            <input type="password" name="confirm_pass" placeholder="Confirm Password" required style="width:100%; padding:10px; margin:10px 0;">
            <button type="submit" style="width:100%; padding:10px; background:#d4af37; color:white; border:none; cursor:pointer;">UPDATE PASSWORD</button>
        </form>
    </div>
</body>
</html>