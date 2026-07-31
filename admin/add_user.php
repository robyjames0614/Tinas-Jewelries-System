<?php
session_start();

// Ito ang tamang paraan para tawagin ang db_conn.php mula sa loob ng admin/ folder
// Siguraduhin na ang filename ay 'db_conn.php' (o kung ano man ang totoong pangalan ng file mo)
include('../db_conn.php');

// Check kung gumagana ang connection
if (!isset($conn)) {
    die("Error: Connection file not found or \$conn variable is not defined.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Tina's Gold Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7f6; display: flex; min-height: 100vh; }
        
        .main-content { width: 100%; padding: 40px; display: flex; justify-content: center; align-items: center; }
        .form-card { background: white; width: 100%; max-width: 450px; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); text-align: center; border-top: 5px solid #d4af37; }
        .input-group { text-align: left; margin-bottom: 20px; }
        .input-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 8px; text-transform: uppercase; }
        .input-group input, .input-group select { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; outline: none; font-size: 14px; background: #fafafa; }
        .btn-register { width: 100%; padding: 14px; background: #1a1a1a; color: #d4af37; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>

<div class="main-content">
    <div class="form-card">
        <h1>Edit User</h1>
        <form action="update_user_process.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
            
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="input-group">
                <label>Role</label>
                <select name="role">
                    <option value="Admin" <?php if($user['role'] == 'Admin') echo 'selected'; ?>>Admin</option>
                    <option value="Staff" <?php if($user['role'] == 'Staff') echo 'selected'; ?>>Staff</option>
                </select>
            </div>

            <button type="submit" class="btn-register">Update Account</button>
        </form>
        <a href="users.php" style="display:block; margin-top:15px; color:#888; text-decoration:none; font-size:13px;">Back to User List</a>
    </div>
</div>

</body>
</html>