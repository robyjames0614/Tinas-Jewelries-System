<?php
session_start();
include('db_conn.php');

if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

// Queries
$total_orders = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM orders"));
$delivered_orders = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM orders WHERE status='Delivered'"));

// DAGDAG: Query para sa Notification ng Pending Orders
$pending_query = mysqli_query($conn, "SELECT id FROM orders WHERE status='Pending'");
$pending_orders_count = mysqli_num_rows($pending_query);

$low_stock_query = mysqli_query($conn, "SELECT id FROM products WHERE stock <= 5");
$low_stock_count = mysqli_num_rows($low_stock_query);

$sales_query = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as grand_total FROM orders WHERE status='Delivered' OR status='Paid'"));
$total_sales = $sales_query['grand_total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tina's Gold - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7f6; display: flex; }

        /* Sidebar */
        .sidebar { width: 250px; height: 100vh; background: #1a1a1a; color: white; position: fixed; padding: 20px; }
        .sidebar h2 { color: #d4af37; text-align: center; margin-bottom: 30px; font-size: 24px; }
        .sidebar a { display: block; color: #bbb; padding: 12px; text-decoration: none; border-radius: 5px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #d4af37; color: #1a1a1a; font-weight: bold; }

        /* Main Content */
        .main-content { margin-left: 250px; width: calc(100% - 250px); padding: 40px; }
        .header { margin-bottom: 30px; }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); position: relative; overflow: hidden; border-bottom: 5px solid #1a1a1a; }
        .stat-card h3 { font-size: 13px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card p { font-size: 28px; font-weight: 600; margin-top: 10px; color: #1a1a1a; }
        .stat-card i { position: absolute; right: 20px; bottom: 20px; font-size: 40px; color: rgba(0,0,0,0.03); }
        
        /* Notification Styling */
        .card-pending { border-bottom-color: #ff9800; background: #fffaf0; }
        .notif-dot { 
            position: absolute; top: 15px; right: 15px; 
            background: #ff4d4d; color: white; font-size: 10px; 
            padding: 2px 8px; border-radius: 10px; font-weight: bold;
            animation: pulse-red 1.5s infinite;
        }

        @keyframes pulse-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 77, 77, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(255, 77, 77, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 77, 77, 0); }
        }

        /* Specific Colors */
        .card-gold { border-bottom-color: #d4af37; }
        .card-red { border-bottom-color: #ff4d4d; }
        .card-green { border-bottom-color: #2e7d32; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Tina's Gold</h2>
    <a href="dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="view_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
    <a href="inventory.php"><i class="fas fa-gem"></i> Inventory</a>
    <a href="sales_report.php"><i class="fas fa-file-invoice-dollar"></i> Sales Report</a>
    <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
    <a href="logout.php" style="margin-top: 50px; color: #ff4d4d;"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Welcome, Admin Tina!</h1>
        <p style="color: #888;"><?php echo date('F d, Y'); ?></p>
    </div>

    <div class="stats-grid">
        <div class="stat-card <?php echo ($pending_orders_count > 0) ? 'card-pending' : ''; ?>">
            <h3>New Orders</h3>
            <p style="<?php echo ($pending_orders_count > 0) ? 'color: #ff9800;' : ''; ?>">
                <?php echo $pending_orders_count; ?>
            </p>
            <i class="fas fa-bell"></i>
            <?php if($pending_orders_count > 0): ?>
                <span class="notif-dot">ACTION REQUIRED</span>
            <?php endif; ?>
        </div>

        <div class="stat-card">
            <h3>Total Orders</h3>
            <p><?php echo $total_orders; ?></p>
            <i class="fas fa-box"></i>
        </div>

        <div class="stat-card card-red">
            <h3>Low Stock</h3>
            <p style="color: #ff4d4d;"><?php echo $low_stock_count; ?></p>
            <i class="fas fa-exclamation-circle"></i>
        </div>

        <div class="stat-card card-green">
            <h3>Delivered</h3>
            <p><?php echo $delivered_orders; ?></p>
            <i class="fas fa-check-circle"></i>
        </div>

        <div class="stat-card card-gold">
            <h3>Total Earnings</h3>
            <p>₱<?php echo number_format($total_sales, 2); ?></p>
            <i class="fas fa-coins"></i>
        </div>
    </div>
</div>

</body>
</html>