<?php
session_start();
include('../db_conn.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($id == 0) {
    header("Location: users.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
$user = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    $update_query = "UPDATE users SET username='$username', role='$role' WHERE id=$id";
    
    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('User updated successfully!'); window.location.href='users.php';</script>";
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Tina's Gold Trading</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f0f2f5; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 400px; 
        }
        h2 { color: #333; margin-bottom: 20px; text-align: center; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input, select { 
            width: 100%; padding: 12px; margin-bottom: 20px; 
            border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;
            font-size: 16px;
        }
        button { 
            width: 100%; padding: 12px; background: #c5a059; /* Goldish color */
            color: white; border: none; border-radius: 6px; 
            font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s;
        }
        button:hover { background: #a68445; }
        .cancel-btn { 
            display: block; text-align: center; margin-top: 15px; 
            color: #888; text-decoration: none; font-size: 14px; 
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Edit User</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <label>Username</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        
        <label>Role</label>
        <select name="role">
            <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
            <option value="client" <?php if($user['role'] == 'client') echo 'selected'; ?>>Client</option>
            <option value="staff" <?php if($user['role'] == 'staff') echo 'selected'; ?>>Staff</option>
        </select>
        
        <button type="submit" name="update">Update User</button>
        <a href="users.php" class="cancel-btn">Cancel</a>
    </form>
</div>

</body>
</html>