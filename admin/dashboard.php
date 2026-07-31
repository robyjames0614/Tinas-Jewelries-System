<?php
// 1. Error reporting para makita agad kung may typo o missing files
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 2. FIX: Lumabas ng isang folder gamit ang '../' para mahanap ang db_conn.php
include('../db_conn.php');

// Security Layer 
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php?error=unauthorized");
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
        body { background: #f4f7f6; display: flex; min-height: 100vh; overflow-x: hidden; }

        /* --- SIDEBAR --- */
        .sidebar { 
            width: 260px; height: 100vh; background: #1a1a1a; color: white; 
            position: fixed; left: 0; top: 0; padding: 20px; transition: 0.3s; z-index: 1100;
        }

        /* --- MOBILE HEADER & OVERLAY --- */
        .mobile-header {
            display: none; width: 100%; background: #1a1a1a; color: #d4af37;
            padding: 15px 20px; position: fixed; top: 0; left: 0; z-index: 1000;
            justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 1050;
        }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; transition: 0.3s; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 20px; }
        .admin-profile { display: flex; align-items: center; gap: 10px; }
        .admin-profile img { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #d4af37; object-fit: cover; }

        /* --- STATS CARDS --- */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
        .stat-card { 
            background: white; padding: 25px; border-radius: 20px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); position: relative; 
            border-top: 5px solid #eee; transition: 0.3s; text-decoration: none; color: inherit; 
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .stat-card p { font-size: 24px; font-weight: 700; color: #1a1a1a; }
        .stat-card i { position: absolute; right: 20px; bottom: 20px; font-size: 30px; opacity: 0.1; }
        
        .card-pending { border-top-color: #ff9800; }
        .card-gold { border-top-color: #d4af37; background: #1a1a1a; color: #d4af37; }
        .card-gold p { color: #d4af37; }
        .card-red { border-top-color: #ff4d4d; }
        .card-blue { border-top-color: #2196f3; }

        /* --- QUICK ACTIONS --- */
        .quick-actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-top: 20px; }
        .action-btn { 
            padding: 18px; border-radius: 15px; text-decoration: none; font-weight: 600; font-size: 14px;
            display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .btn-gold { background: #d4af37; color: #1a1a1a; border: none; }
        .btn-gold:hover { background: #b8952e; transform: scale(1.02); }
        
        .btn-dark { background: #1a1a1a; color: #d4af37; border: 1px solid #d4af37; }
        .btn-dark:hover { background: #333; transform: scale(1.02); }

        .btn-white { background: white; color: #1a1a1a; border: 1px solid #eee; }
        .btn-white:hover { background: #f9f9f9; transform: scale(1.02); }

        /* --- RESPONSIVE --- */
        @media (max-width: 992px) {
            .sidebar { left: -260px; }
            .sidebar.active { left: 0; }
            .mobile-header { display: flex; }
            .main-content { margin-left: 0; width: 100%; padding: 100px 20px 40px; }
            .overlay.active { display: block; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 576px) {
            .stats-grid { grid-template-columns: 1fr; }
            .quick-actions-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="mobile-header">
    <div style="font-weight: bold; letter-spacing: 1px;">TINA'S ADMIN</div>
    <i class="fas fa-bars" style="font-size: 24px; cursor: pointer;" onclick="toggleSidebar()"></i>
</div>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<?php 
// 3. Siguraduhin na ang sidebar.php ay nasa loob din ng admin folder
include('sidebar.php'); 
?>

<div class="main-content">
    <div class="header">
        <div>
            <h1 style="font-size: 26px; color: #1a1a1a;">Dashboard Overview</h1>
            <p style="color: #888; font-size: 14px;">Welcome back, <strong>Admin Tina</strong>!</p>
        </div>
        <div class="admin-profile">
            <div style="text-align: right; display: block;">
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
            <i class="fas fa-clock"></i>
        </a>
        <a href="sales_report.php" class="stat-card card-gold">
            <h3>Gross Revenue</h3>
            <p>₱<?php echo number_format($total_sales, 2); ?></p>
            <i class="fas fa-coins"></i>
        </a>
        <a href="inventory.php" class="stat-card card-red">
            <h3>Low Stock</h3>
            <p style="color: #ff4d4d;"><?php echo $low_stock_count; ?></p>
            <i class="fas fa-exclamation-triangle"></i>
        </a>
        <a href="users.php" class="stat-card card-blue">
            <h3>System Users</h3>
            <p><?php echo $total_users; ?></p>
            <i class="fas fa-users-cog"></i>
        </a>
    </div>

    <h3 style="margin: 40px 0 15px; font-size: 18px; color: #1a1a1a; border-left: 4px solid #d4af37; padding-left: 10px;">Quick Actions</h3>
    
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
            <i class="fas fa-cog"></i> Account Settings
        </a>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('overlay');
        if(sidebar && overlay) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    }
</script>

</body>
</html>