<?php
session_start();
include('db_conn.php');

// Security Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Kunin ang ID mula sa URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
    $user = mysqli_fetch_assoc($result);
}

// Update Logic
if (isset($_POST['update'])) {
    $username = $_POST['username'];
    $role = $_POST['role'];
    
    $update_query = "UPDATE users SET username='$username', role='$role' WHERE id=$id";
    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('User updated!'); window.location.href='users.php';</script>";
    }
}
?>

<form method="POST">
    <input type="text" name="username" value="<?php echo $user['username']; ?>" required>
    <select name="role">
        <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
        <option value="staff" <?php if($user['role'] == 'staff') echo 'selected'; ?>>Staff</option>
    </select>
    <button type="submit" name="update">Update User</button>
</form>