<?php
session_start();
include('db_conn.php');

/** * UPDATED SECURITY LAYER 
 * Gumagamit na tayo ng admin_id para hindi mag-conflict sa client sessions.
 */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}

// Queries para sa Dashboard Stats
$total_orders = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM orders"));
$delivered_orders = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM orders WHERE status='Delivered'"));

// Notification ng Pending Orders
$pending_query = mysqli_query($conn, "SELECT id FROM orders WHERE status='Pending'");
$pending_orders_count = mysqli_num_rows($pending_query);

// Low Stock Alert
$low_stock_query = mysqli_query($conn, "SELECT id FROM products WHERE stock <= 5");
$low_stock_count = mysqli_num_rows($low_stock_query);

// Sales calculation
$sales_result = mysqli_query($conn, "SELECT SUM(total_amount) as grand_total FROM orders WHERE status='Delivered' OR status='Paid'");
$sales_query = mysqli_fetch_assoc($sales_result);
$total_sales = $sales_query['grand_total'] ?? 0;

// User Management Count
$user_count_res = mysqli_query($conn, "SELECT COUNT(id) as total_users FROM users");
$user_count_row = mysqli_fetch_assoc($user_count_res);
$total_users = $user_count_row['total_users'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tina's Gold - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7f6; display: flex; overflow-x: hidden; min-height: 100vh; }

        /* --- SIDEBAR --- */
        .sidebar { 
            width: 260px; height: 100vh; background: #1a1a1a; color: white; 
            position: fixed; left: 0; top: 0; padding: 20px; transition: 0.3s; z-index: 1000;
        }
        .sidebar h2 { color: #d4af37; text-align: center; margin-bottom: 30px; font-size: 22px; letter-spacing: 2px; border-bottom: 1px solid #333; padding-bottom: 15px; }
        .sidebar a { display: flex; align-items: center; color: #bbb; padding: 12px 15px; text-decoration: none; border-radius: 8px; margin-bottom: 8px; transition: 0.3s; font-size: 14px; }
        .sidebar a i { margin-right: 12px; width: 20px; text-align: center; }
        .sidebar a:hover, .sidebar a.active { background: rgba(212, 175, 55, 0.1); color: #d4af37; font-weight: 600; border-left: 4px solid #d4af37; }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; transition: 0.3s; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .admin-profile { display: flex; align-items: center; gap: 10px; }
        .admin-profile img { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #d4af37; object-fit: cover; }

        /* --- STATS CARDS --- */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); position: relative; border-top: 4px solid #eee; transition: 0.3s; text-decoration: none; color: inherit; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .stat-card h3 { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .stat-card p { font-size: 22px; font-weight: 700; color: #1a1a1a; }
        .stat-card i { position: absolute; right: 20px; top: 20px; font-size: 22px; opacity: 0.15; }
        
        .card-pending { border-top-color: #ff9800; }
        .card-gold { border-top-color: #d4af37; }
        .card-red { border-top-color: #ff4d4d; }
        .card-blue { border-top-color: #2196f3; }
        .card-green { border-top-color: #2e7d32; }

        /* --- QUICK ACTIONS (UNIFORM BUTTONS) --- */
        .quick-actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .action-btn { 
            padding: 15px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 14px;
            display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s;
        }
        .btn-gold { background: #d4af37; color: #1a1a1a; box-shadow: 0 4px 10px rgba(212, 175, 55, 0.2); }
        .btn-dark { background: #1a1a1a; color: #d4af37; border: 1px solid #d4af37; }
        .btn-white { background: white; color: #1a1a1a; border: 1px solid #eee; }
        
        .action-btn:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.1); }

        .notif-badge { background: #ff4d4d; color: white; font-size: 10px; padding: 2px 7px; border-radius: 50%; margin-left: auto; }

        @media (max-width: 992px) {
            .sidebar { left: -260px; }
            .sidebar.active { left: 0; }
            .main-content { margin-left: 0; padding: 20px; width: 100%; }
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <h2>TINA'S ADMIN</h2>
    <a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="view_orders.php">
        <i class="fas fa-shopping-bag"></i> Orders 
        <?php if($pending_orders_count > 0): ?><span class="notif-badge"><?php echo $pending_orders_count; ?></span><?php endif; ?>
    </a>
    <a href="inventory.php"><i class="fas fa-gem"></i> Inventory</a>
    <a href="sales_report.php"><i class="fas fa-chart-bar"></i> Sales Report</a>
    <a href="users.php"><i class="fas fa-user-shield"></i> User Management</a>
    <a href="customers.php"><i class="fas fa-user-friends"></i> Customers</a>
    
    <div style="position: absolute; bottom: 20px; width: calc(100% - 40px);">
        <a href="logout.php" style="color: #ff4d4d; background: rgba(255, 77, 77, 0.05);"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="header">
        <div>
            <h1 style="font-size: 26px; color: #1a1a1a;">Dashboard Overview</h1>
            <p style="color: #888; font-size: 14px;">Welcome back, <strong>Admin Tina</strong>!</p>
        </div>
        <div class="admin-profile">
            <div style="text-align: right;">
                <p style="font-size: 13px; font-weight: 600;">Administrator</p>
                <p style="font-size: 11px; color: #2e7d32;"><i class="fas fa-circle" style="font-size: 7px;"></i> Online</p>
            </div>
            <img src="../image/logo.png.jpg" alt="Admin">
        </div>
    </div>

    <div class="stats-grid">
        <a href="view_orders.php" class="stat-card card-pending">
            <h3>New Orders</h3>
            <p style="color: #ff9800;"><?php echo $pending_orders_count; ?></p>
            <i class="fas fa-clock" style="color: #ff9800;"></i>
        </a>
        <a href="sales_report.php" class="stat-card card-gold">
            <h3>Gross Revenue</h3>
            <p>₱<?php echo number_format($total_sales, 2); ?></p>
            <i class="fas fa-coins" style="color: #d4af37;"></i>
        </a>
        <a href="inventory.php" class="stat-card card-red">
            <h3>Low Stock</h3>
            <p style="color: #ff4d4d;"><?php echo $low_stock_count; ?></p>
            <i class="fas fa-exclamation-triangle" style="color: #ff4d4d;"></i>
        </a>
        <a href="users.php" class="stat-card card-blue">
            <h3>System Users</h3>
            <p><?php echo $total_users; ?></p>
            <i class="fas fa-users-cog" style="color: #2196f3;"></i>
        </a>
    </div>

    <h3 style="margin: 40px 0 15px; font-size: 18px; color: #1a1a1a;">Quick Actions</h3>
    <div class="quick-actions-grid">
        <a href="add_product.php" class="action-btn btn-gold">
            <i class="fas fa-plus-circle"></i> Add Product
        </a>
        <a href="add_user.php" class="action-btn btn-dark">
            <i class="fas fa-user-plus"></i> New Staff
        </a>
        <a href="view_orders.php" class="action-btn btn-white">
            <i class="fas fa-list"></i> View All Orders
        </a>
        <a href="profile.php" class="action-btn btn-white">
            <i class="fas fa-cog"></i> Settings
        </a>
    </div>
</div>

</body>
</html>