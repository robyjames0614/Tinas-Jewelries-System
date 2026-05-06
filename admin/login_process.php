<?php
// 1. SAPILITANG IPAKITA ANG LAHAT NG ERROR
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. CHECK KUNG MAY SESSION NA NAUNA (IWAS ERROR)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. CHECK KUNG NAHAHANAP ANG DATABASE CONNECTION
if (!file_exists('db_conn.php')) {
    die("ERROR: Hindi mahanap ang db_conn.php sa loob ng admin folder!");
}

include('db_conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Siguraduhin na ang $conn ay galing sa db_conn.php
    if (!$conn) {
        die("ERROR: Database connection failed: " . mysqli_connect_error());
    }

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("ERROR sa Query: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
      // ... (sa loob ng password check)
        if ($password == $user['password'] || password_verify($password, $user['password'])) {
            
            // Eto ang kailangang idagdag para gumana ang dashboard check mo:
            $_SESSION['admin_id'] = $user['id']; // Siguraduhin na 'id' ang column name sa DB
            
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = strtolower(trim($user['role']));

            echo "Login Success! Redirecting to dashboard...";
            header("Location: dashboard.php");
            exit();
        }
         else {
            die("Maling Password! <a href='login.php'>Balik sa Login</a>");
        }
    } else {
        die("Username hindi nahanap! <a href='login.php'>Balik sa Login</a>");
    }
} else {
    die("Direct access is not allowed.");
}
?>