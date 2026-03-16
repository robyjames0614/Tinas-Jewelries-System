<?php
session_start();
include('db_conn.php');

if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

$user = $_SESSION['username'];
// In-update sa 'users' table base sa iyong database structure
$query = "SELECT * FROM users WHERE username='$user'"; 
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}

$admin = mysqli_fetch_assoc($result);

// Fallback para hindi mag-error ang UI kung walang nahanap na row
$display_username = $admin['username'] ?? $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile - Tina's Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; display: flex; min-height: 100vh; }
        .main-content { flex: 1; margin-left: 260px; padding: 40px; display: flex; justify-content: center; align-items: flex-start; }
        .profile-container { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 500px; border-top: 5px solid #d4af37; margin-top: 20px; }
        .profile-header { text-align: center; margin-bottom: 30px; }
        .profile-header i { font-size: 50px; color: #d4af37; margin-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #666; margin-bottom: 8px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: 0.3s; font-size: 14px; }
        input:focus { border-color: #d4af37; box-shadow: 0 0 5px rgba(212, 175, 55, 0.2); }
        .btn-update { width: 100%; padding: 14px; background: #1a1a1a; color: #d4af37; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: 10px; letter-spacing: 1px; }
        .btn-update:hover { background: #d4af37; color: white; transform: translateY(-2px); }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 13px; text-align: center; display: none; }
    </style>
</head>
<body>

    <?php include('sidebar.php'); ?>

    <div class="main-content">
        <div class="profile-container">
            <div class="profile-header">
                <i class="fas fa-user-circle"></i>
                <h2 style="color: #1a1a1a;">Account Settings</h2>
                <p style="font-size: 12px; color: #888;">Update your administrative credentials</p>
            </div>

            <form action="update_profile.php" method="POST">
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

</body>
</html>