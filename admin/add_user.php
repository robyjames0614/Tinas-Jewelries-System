<?php
session_start();
include('db_conn.php');

// Siguraduhin na naka-login ang admin
if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

// Sidebar notification count (consistent sa dashboard)
$pending_count_res = mysqli_query($conn, "SELECT id FROM orders WHERE status='Pending'");
$pending_count = mysqli_num_rows($pending_count_res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Staff - Tina's Gold Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7f6; display: flex; min-height: 100vh; }

        /* --- SIDEBAR (EXACT MATCH SA DASHBOARD MO) --- */
        .sidebar { 
            width: 260px; height: 100vh; background: #1a1a1a; color: white; 
            position: fixed; left: 0; top: 0; padding: 20px; z-index: 1000;
        }
        .sidebar h2 { color: #d4af37; text-align: center; margin-bottom: 30px; font-size: 22px; letter-spacing: 2px; border-bottom: 1px solid #333; padding-bottom: 15px; }
        .sidebar a { display: flex; align-items: center; color: #bbb; padding: 12px 15px; text-decoration: none; border-radius: 8px; margin-bottom: 8px; transition: 0.3s; font-size: 14px; }
        .sidebar a i { margin-right: 12px; width: 20px; text-align: center; }
        .sidebar a:hover, .sidebar a.active { background: rgba(212, 175, 55, 0.1); color: #d4af37; font-weight: 600; border-left: 4px solid #d4af37; }
        .notif-badge { background: #ff4d4d; color: white; font-size: 10px; padding: 2px 7px; border-radius: 50%; margin-left: auto; }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; display: flex; justify-content: center; align-items: center; }
        
        /* --- ADD USER CARD --- */
        .form-card { 
            background: white; width: 100%; max-width: 450px; padding: 40px; 
            border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
            text-align: center; border-top: 5px solid #d4af37;
        }
        .form-card i.main-icon { font-size: 50px; color: #d4af37; margin-bottom: 15px; }
        .form-card h1 { font-size: 24px; color: #1a1a1a; margin-bottom: 10px; }
        .form-card p { color: #888; font-size: 14px; margin-bottom: 30px; }

        .input-group { text-align: left; margin-bottom: 20px; }
        .input-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 8px; text-transform: uppercase; }
        .input-group input, .input-group select { 
            width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; 
            outline: none; transition: 0.3s; font-size: 14px; background: #fafafa;
        }
        .input-group input:focus { border-color: #d4af37; box-shadow: 0 0 8px rgba(212, 175, 55, 0.2); background: white; }

        .btn-register { 
            width: 100%; padding: 14px; background: #1a1a1a; color: #d4af37; 
            border: none; border-radius: 10px; font-weight: 600; font-size: 16px; 
            cursor: pointer; transition: 0.3s; margin-top: 10px;
        }
        .btn-register:hover { background: #333; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }

        .back-link { display: inline-block; margin-top: 20px; color: #888; text-decoration: none; font-size: 13px; transition: 0.3s; }
        .back-link:hover { color: #d4af37; }

        @media (max-width: 992px) {
            .sidebar { left: -260px; }
            .main-content { margin-left: 0; width: 100%; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>TINA'S ADMIN</h2>
    <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="view_orders.php">
        <i class="fas fa-shopping-bag"></i> Orders 
        <?php if($pending_count > 0): ?><span class="notif-badge"><?php echo $pending_count; ?></span><?php endif; ?>
    </a>
    <a href="inventory.php"><i class="fas fa-gem"></i> Inventory</a>
    <a href="sales_report.php"><i class="fas fa-chart-bar"></i> Sales Report</a>
    <a href="users.php" class="active"><i class="fas fa-user-shield"></i> User Management</a>
    <a href="customers.php"><i class="fas fa-user-friends"></i> Customers</a>
    
    <div style="position: absolute; bottom: 20px; width: calc(100% - 40px);">
        <a href="logout.php" style="color: #ff4d4d;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="form-card">
        <i class="fas fa-user-plus main-icon"></i>
        <h1>Add New Staff</h1>
        <p>Register a new administrator for the system.</p>

        <form action="register_user_process.php" method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter username" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create a password" required>
            </div>

            <div class="input-group">
                <label>Role / Account Type</label>
                <select name="role">
                    <option value="Admin">Full Admin</option>
                    <option value="Staff">Staff / Inventory Manager</option>
                </select>
            </div>

            <button type="submit" class="btn-register">Register Account</button>
        </form>

        <a href="users.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to User List</a>
    </div>
</div>

</body>
</html>