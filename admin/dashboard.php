<?php
session_start();
include('db_conn.php');

/** * SECURITY LAYER: 
 * Dito natin iba-block ang mga normal na user (tulad ni Kristina).
 * Dapat sa login_process.php ng Admin, nilagyan mo ng $_SESSION['role'] = 'admin';
 */
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Kung hindi admin, itatapon natin sila pabalik sa login page ng main site
    header("Location: ../login.html?error=unauthorized");
    exit();
}

// Queries
$total_orders = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM orders"));
$delivered_orders = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM orders WHERE status='Delivered'"));

// Notification ng Pending Orders
$pending_query = mysqli_query($conn, "SELECT id FROM orders WHERE status='Pending'");
$pending_orders_count = mysqli_num_rows($pending_query);

// Low Stock Alert
$low_stock_query = mysqli_query($conn, "SELECT id FROM products WHERE stock <= 5");
$low_stock_count = mysqli_num_rows($low_stock_query);

// Sales calculation
$sales_query = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as grand_total FROM orders WHERE status='Delivered' OR status='Paid'"));
$total_sales = $sales_query['grand_total'] ?? 0;
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
        body { background: #f4f7f6; display: flex; }

        /* Sidebar */
        .sidebar { width: 260px; height: 100vh; background: #1a1a1a; color: white; position: fixed; padding: 20px; transition: 0.3s; }
        .sidebar h2 { color: #d4af37; text-align: center; margin-bottom: 30px; font-size: 22px; letter-spacing: 2px; border-bottom: 1px solid #333; padding-bottom: 15px; }
        .sidebar a { display: flex; align-items: center; color: #bbb; padding: 12px 15px; text-decoration: none; border-radius: 8px; margin-bottom: 8px; transition: 0.3s; }
        .sidebar a i { margin-right: 12px; width: 20px; text-align: center; }
        .sidebar a:hover, .sidebar a.active { background: rgba(212, 175, 55, 0.1); color: #d4af37; font-weight: 600; border-left: 4px solid #d4af37; }

        /* Main Content */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .admin-profile { display: flex; align-items: center; gap: 10px; }
        .admin-profile img { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #d4af37; }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 25px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); position: relative; overflow: hidden; transition: 0.3s; border: 1px solid #eee; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
        .stat-card h3 { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        .stat-card p { font-size: 24px; font-weight: 700; margin-top: 10px; color: #1a1a1a; }
        .stat-card i { position: absolute; right: 20px; top: 20px; font-size: 25px; opacity: 0.2; }
        
        /* Specific Accents */
        .card-pending { border-top: 4px solid #ff9800; }
        .card-gold { border-top: 4px solid #d4af37; }
        .card-red { border-top: 4px solid #ff4d4d; }
        .card-green { border-top: 4px solid #2e7d32; }

        .notif-badge { 
            background: #ff4d4d; color: white; font-size: 9px; 
            padding: 3px 8px; border-radius: 20px; font-weight: bold;
            margin-left: auto; animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>TINA'S ADMIN</h2>
    <a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="view_orders.php">
        <i class="fas fa-shopping-bag"></i> Orders 
        <?php if($pending_orders_count > 0): ?>
            <span class="notif-badge"><?php echo $pending_orders_count; ?></span>
        <?php endif; ?>
    </a>
    <a href="inventory.php"><i class="fas fa-gem"></i> Inventory</a>
    <a href="sales_report.php"><i class="fas fa-chart-bar"></i> Sales Report</a>
    <a href="customers.php"><i class="fas fa-user-friends"></i> Customers</a>
    
    <div style="position: absolute; bottom: 20px; width: calc(100% - 40px);">
        <a href="logout.php" style="color: #ff4d4d; background: rgba(255, 77, 77, 0.05);"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="header">
        <div>
            <h1 style="font-size: 28px; color: #1a1a1a;">Dashboard Overview</h1>
            <p style="color: #888; font-size: 14px;">Hello Admin Tina, here's what's happening today.</p>
        </div>
        <div class="admin-profile">
            <div style="text-align: right;">
                <p style="font-size: 14px; font-weight: 600;">Admin Tina</p>
                <p style="font-size: 12px; color: #2e7d32;"><i class="fas fa-circle" style="font-size: 8px;"></i> Online</p>
            </div>
            <img src="../image/logo.png.jpg" alt="Admin">
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card card-pending">
            <h3>New Orders</h3>
            <p style="color: #ff9800;"><?php echo $pending_orders_count; ?></p>
            <i class="fas fa-clock"></i>
        </div>

        <div class="stat-card card-gold">
            <h3>Gross Revenue</h3>
            <p>₱<?php echo number_format($total_sales, 2); ?></p>
            <i class="fas fa-coins"></i>
        </div>

        <div class="stat-card card-red">
            <h3>Low Stock Items</h3>
            <p style="color: #ff4d4d;"><?php echo $low_stock_count; ?></p>
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <div class="stat-card card-green">
            <h3>Fulfilled</h3>
            <p><?php echo $delivered_orders; ?></p>
            <i class="fas fa-check-double"></i>
        </div>
    </div>

    </div>

</body>
</html>