<?php
session_start();
include('db_conn.php');

// Security check: Admin lang ang pwedeng maka-access nito
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php?error=unauthorized");
    exit();
}

// Fetch all users
$query = "SELECT * FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Tina's Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; display: flex; }
        
        .main-content { flex: 1; margin-left: 260px; padding: 40px; transition: 0.3s; }
        .admin-container { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #1a1a1a; color: #d4af37; padding: 15px; text-align: left; font-size: 12px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .role-badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .role-admin { background: #e1f5fe; color: #0288d1; }
        .role-staff { background: #f3e5f5; color: #7b1fa2; }

        .btn-add { background: #d4af37; color: #1a1a1a; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 20px; }
            .table-responsive { overflow-x: auto; }
        }
    </style>
</head>
<body>

    <?php include('sidebar.php'); ?>

    <div class="main-content">
        <div class="admin-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2>User Management</h2>
                <a href="add_user.php" class="btn-add"><i class="fas fa-user-plus"></i> Add New User</a>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Date Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['fullname']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>
                                <span class="role-badge <?php echo ($row['role'] == 'admin') ? 'role-admin' : 'role-staff'; ?>">
                                    <?php echo strtoupper($row['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $row['id']; ?>" style="color: #2980b9; margin-right: 10px;"><i class="fas fa-edit"></i></a>
                                <?php if($row['username'] !== $_SESSION['username']): ?>
                                    <a href="delete_user.php?id=<?php echo $row['id']; ?>" style="color: #e74c3c;" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>