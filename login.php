<?php
// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('db_conn.php'); 

$alert_script = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if ($password == $user['password'] || password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['admin_id'] = $user['id']; 
            $_SESSION['username'] = $user['username'];
            $user_role = trim(strtolower($user['role']));
            $_SESSION['role'] = $user_role;

            if ($user_role === 'admin' || $user_role === 'staff') {
                $alert_script = "
                    Swal.fire({
                        title: 'Welcome Admin/Staff!',
                        text: 'Successfully logged in as administrator.',
                        icon: 'success',
                        confirmButtonColor: '#d4af37',
                        confirmButtonText: 'Proceed to Dashboard'
                    }).then(() => {
                        window.location.href = 'admin/dashboard.php';
                    });
                ";
            } else {
                $alert_script = "
                    Swal.fire({
                        title: 'Welcome Client!',
                        text: 'Successfully logged in to Tina\'s Jewelries.',
                        icon: 'success',
                        confirmButtonColor: '#d4af37',
                        confirmButtonText: 'Continue Shopping'
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                ";
            }
        } else {
            $alert_script = "
                Swal.fire({
                    title: 'Access Denied',
                    text: 'Mali ang Password! Pakisubukan ulit.',
                    icon: 'error',
                    confirmButtonColor: '#d4af37',
                    confirmButtonText: 'Try Again'
                });
            ";
        }
    } else {
        $alert_script = "
            Swal.fire({
                title: 'User Not Found',
                text: 'Username does not exist in our system.',
                icon: 'warning',
                confirmButtonColor: '#d4af37',
                confirmButtonText: 'Try Again'
            });
        ";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tinas Jewelries</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- SweetAlert2 Library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Google Identity Services -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-image: url('image/gold-abstract.png'); display: flex; justify-content: center; align-items: center; min-height: 100vh; color: #fff; }
        .container { background: rgba(0, 0, 0, 0.6); padding: 50px 40px; border-radius: 8px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8); backdrop-filter: blur(15px); border: 1px solid rgba(212, 175, 55, 0.4); text-align: center; max-width: 420px; width: 90%; }
        h1 { font-family: 'Playfair Display', serif; margin-bottom: 10px; font-size: 2.5rem; color: #d4af37; letter-spacing: 3px; text-transform: lowercase; }
        .subtitle { font-size: 0.8rem; margin-bottom: 30px; color: #aaa; letter-spacing: 1.5px; text-transform: uppercase; }
        form { display: flex; flex-direction: column; gap: 15px; }
        input { padding: 15px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(212, 175, 55, 0.2); color: #fff; outline: none; font-size: 0.95rem; border-radius: 4px; }
        button { padding: 16px; margin-top: 10px; background: #1a1a1a !important; color: #d4af37 !important; border: 1px solid #d4af37; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; border-radius: 4px; transition: 0.3s; }
        button:hover { background: #d4af37 !important; color: #1a1a1a !important; transform: translateY(-3px); }
        .footer-links { margin-top: 30px; font-size: 0.85rem; display: flex; flex-direction: column; gap: 12px; }
        .footer-links a { color: #d4af37; text-decoration: none; font-weight: 600; }
        .google-btn-container { margin-top: 20px; display: flex; justify-content: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tinas Jewelries</h1>
        <p class="subtitle">Secure Client And ADMIN Access</p>
        
        <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login Now</button>
        </form>

        <!-- Google Login Button -->
        <div class="google-btn-container">
            <div id="g_id_onload"
                 data-client_id="959306304158-i6esltlelpntk1n2ecll3ui7433n3std.apps.googleusercontent.com"
                 data-callback="handleCredentialResponse"
                 data-auto_prompt="false"></div>
            <div class="g_id_signin" data-type="standard"></div>
        </div>

        <div class="footer-links">
            <a href="forgot-password.php">Forgot Password?</a>
            <p>Don't have an account? <a href="register.php">Register</a></p>
            <p><a href="index.php">Back to Dashboard</a></p>
        </div>
    </div>

    <script>
        // Trigger PHP dynamic SweetAlert2 popups
        <?php if (!empty($alert_script)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                <?php echo $alert_script; ?>
            });
        <?php endif; ?>

        function handleCredentialResponse(response) {
            const responsePayload = decodeJwtResponse(response.credential);

            fetch('auth_google.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    google_id: responsePayload.sub,
                    email: responsePayload.email,
                    fullname: responsePayload.name
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        title: 'Welcome ' + responsePayload.name + '!',
                        text: 'Google authentication successful.',
                        icon: 'success',
                        confirmButtonColor: '#d4af37',
                        confirmButtonText: 'Go to Cart'
                    }).then(() => {
                        window.location.href = 'cart.php';
                    });
                } else {
                    Swal.fire({
                        title: 'Login Failed',
                        text: 'Google Login was unsuccessful.',
                        icon: 'error',
                        confirmButtonColor: '#d4af37',
                        confirmButtonText: 'Try Again'
                    });
                }
            });
        }

        function decodeJwtResponse(token) {
            var base64Url = token.split('.')[1];
            var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            var jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
            return JSON.parse(jsonPayload);
        }
    </script>
</body>
</html>