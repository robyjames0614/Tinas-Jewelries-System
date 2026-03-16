<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Tina's Jewelries</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .forgot-box { max-width: 400px; margin: 100px auto; padding: 30px; background: white; border: 1px solid #d4af37; border-radius: 8px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; }
        .reset-btn { background: #1a1a1a; color: white; border: none; padding: 12px; width: 100%; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body style="background-color: #fffaf5;">
    <div class="forgot-box">
        <h2 style="font-family: 'Playfair Display', serif;">Password Recovery</h2>
        <p style="font-size: 0.8rem; color: #666;">Enter your Full Name and Phone Number to reset your password.</p>
        
        <form action="reset_logic.php" method="POST">
            <input type="text" name="fullname" placeholder="Registered Full Name" required>
            <input type="tel" name="phone" placeholder="Registered Phone Number" required>
            <button type="submit" class="reset-btn">VERIFY ACCOUNT</button>
        </form>
        <br>
        <a href="login.html" style="font-size: 0.8rem; color: #d4af37;">Back to Login</a>
    </div>
</body>
</html>