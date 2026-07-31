<?php
// 1. Error Reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('db_conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kunin at linisin ang data
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash ang password para secure

    // I-insert sa database
    $sql = "INSERT INTO users (fullname, username, email, password, role) VALUES ('$fullname', '$username', '$email', '$password', 'client')";
    
    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    exit(); // Hihinto ang script dito para hindi ma-render ang HTML
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Tinas Jewelries</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('image/gold-abstract.png') no-repeat center center/cover;
            display: flex; justify-content: center; align-items: center; min-height: 100vh; color: #fff;
        }
        .container {
            background: rgba(0, 0, 0, 0.6); padding: 50px 40px; border-radius: 8px; 
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8); backdrop-filter: blur(15px);
            border: 1px solid rgba(212, 175, 55, 0.4); text-align: center; max-width: 420px; width: 90%;
        }
        h1 { font-family: 'Playfair Display', serif; margin-bottom: 10px; font-size: 2.5rem; color: #d4af37; letter-spacing: 3px; }
        .subtitle { font-size: 0.8rem; margin-bottom: 30px; color: #aaa; letter-spacing: 1.5px; text-transform: uppercase; }
        form { display: flex; flex-direction: column; gap: 15px; }
        input { padding: 15px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(212, 175, 55, 0.2); color: #fff; outline: none; font-size: 0.95rem; border-radius: 4px; }
        input:focus { border-color: #d4af37; }
        button { padding: 16px; margin-top: 10px; background: #1a1a1a; color: #d4af37; border: 1px solid #d4af37; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; border-radius: 4px; transition: 0.3s; }
        button:hover { background: #d4af37; color: #1a1a1a; transform: translateY(-3px); }
        .footer-links { margin-top: 30px; font-size: 0.85rem; display: flex; flex-direction: column; gap: 12px; }
        .footer-links a { color: #d4af37; text-decoration: none; font-weight: 600; }
        .footer-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Account</h1>
        <p class="subtitle">Join Tinas Jewelries Today</p>
        
        <form id="registerForm" onsubmit="handleRegister(event)">
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register Now</button>
        </form>

        <div class="footer-links">
            <p>Already have an account? <a href="login.php">Log In</a></p>
           
        </div>
    </div>

    <script>
    async function handleRegister(event) {
        event.preventDefault();
        const form = event.target;
        if (form.password.value !== form.confirm_password.value) {
            alert("Oops! Hindi magkamukha ang password mo.");
            return;
        }
        let formData = new FormData(form);
        let response = await fetch('register.php', { method: 'POST', body: formData });
        let result = await response.text();

        if (result.trim() === "success") {
            alert("Registration Successful! ✨ Pwede ka na mag-login.");
            window.location.href = "login.php";
        } else {
            alert("Error: " + result);
        }
    }
    </script>
</body>
</html>