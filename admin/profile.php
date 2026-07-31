<?php
session_start();
include('../db_conn.php');

if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

$user = $_SESSION['username'];

// --- BACKEND LOGIC: PROSESO NG PAG-UPDATE NG PROFILE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = mysqli_real_escape_string($conn, $_POST['new_username']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Kunin ang data ng admin para i-verify ang password
    $check_query = "SELECT * FROM users WHERE username='$user'";
    $check_result = mysqli_query($conn, $check_query);
    $admin_data = mysqli_fetch_assoc($check_result);

    if ($admin_data) {
        // I-verify kung tugma ang tinapeng password sa nasa database
        if ($current_password === $admin_data['password']) {
            
            // Kung may inilagay na bagong password, i-update pati password. Kung wala, username lang.
            if (!empty($new_password)) {
                $update_query = "UPDATE users SET username='$new_username', password='$new_password' WHERE username='$user'";
            } else {
                $update_query = "UPDATE users SET username='$new_username' WHERE username='$user'";
            }

            if (mysqli_query($conn, $update_query)) {
                // I-update ang kasalukuyang session para hindi ka ma-log out
                $_SESSION['username'] = $new_username;
                echo "<script>
                        alert('Profile updated successfully!'); 
                        window.location.href='profile.php';
                      </script>";
                exit();
            } else {
                echo "<script>alert('Error updating profile: " . mysqli_error($conn) . "');</script>";
            }
        } else {
            echo "<script>alert('Mali ang iyong Verify Current Password!'); window.location.href='profile.php';</script>";
            exit();
        }
    }
}

// --- KUNIN ANG REPORT NG USER PARA SA DISPLAY ---
$query = "SELECT * FROM users WHERE username='$user'"; 
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}

$admin = mysqli_fetch_assoc($result);
$display_username = $admin['username'] ?? $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Tina's Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; display: flex; min-height: 100vh; }

        /* --- MOBILE HEADER --- */
        .mobile-header {
            display: none;
            background: #1a1a1a;
            color: white;
            padding: 15px 20px;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1001;
        }
        .menu-btn {
            background: #d4af37;
            border: none;
            color: #1a1a1a;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
        }

        /* --- MAIN CONTENT --- */
        .main-content { 
            flex: 1; 
            margin-left: 260px; 
            padding: 40px; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            transition: 0.3s;
        }

        .profile-container { 
            background: white; 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            width: 100%; 
            max-width: 450px; 
            border-top: 5px solid #d4af37; 
        }

        .profile-header { text-align: center; margin-bottom: 30px; }
        .profile-header i { font-size: 50px; color: #d4af37; margin-bottom: 10px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #666; margin-bottom: 8px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: 0.3s; font-size: 14px; }
        input:focus { border-color: #d4af37; box-shadow: 0 0 5px rgba(212, 175, 55, 0.2); }

        .btn-update { 
            width: 100%; 
            padding: 14px; 
            background: #1a1a1a; 
            color: #d4af37; 
            border: none; 
            border-radius: 8px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: 0.3s; 
            margin-top: 10px; 
            letter-spacing: 1px; 
        }
        .btn-update:hover { background: #d4af37; color: white; transform: translateY(-2px); }

        /* --- MOBILE RESPONSIVE --- */
        @media (max-width: 992px) {
            .mobile-header { display: flex; }
            .main-content { 
                margin-left: 0; 
                padding: 100px 20px 40px; 
                width: 100%;
            }
            .profile-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="mobile-header">
        <span style="font-weight: 700; color: #d4af37; letter-spacing: 1px;">TINA'S ADMIN</span>
        <button class="menu-btn" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <?php include('sidebar.php'); ?>

    <div class="main-content">
        <div class="profile-container">
            <div class="profile-header">
                <i class="fas fa-user-circle"></i>
                <h2 style="color: #1a1a1a;">Account Settings</h2>
                <p style="font-size: 12px; color: #888;">Update your administrative credentials</p>
            </div>

            <form action="profile.php" method="POST">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Current Username</label>
                    <input type="text" name="new_username" value="<?php echo htmlspecialchars($display_username); ?>" required>
                </div>
                
                <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Verify Current Password</label>
                    <input type="password" name="current_password" placeholder="Required to save changes" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-key"></i> New Password (Optional)</label>
                    <input type="password" name="new_password" placeholder="Leave blank to keep current">
                </div>
                
                <button type="submit" class="btn-update">UPDATE PROFILE</button>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>

</body>
</html>