<?php
include('db_conn.php'); 

// Bilangin ang Pending orders para sa notification badge
$count_query = "SELECT COUNT(*) as pending_count FROM orders WHERE status = 'Pending'";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$pending_orders = $count_row['pending_count'];

// Kunin ang kasalukuyang file name para sa active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<button class="mobile-nav-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar">
    <div class="sidebar-brand">
        <h2>TINA'S ADMIN</h2>
        <div class="brand-subtitle">Gold Trading System</div>
    </div>
    
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> <span>Dashboard</span>
        </a>
        
        <a href="view_orders.php" class="<?php echo $current_page == 'view_orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-bag"></i> <span>Orders</span>
            <?php if($pending_orders > 0): ?>
                <span class="notif-badge"><?php echo $pending_orders; ?></span>
            <?php endif; ?>
        </a>

        <a href="inventory.php" class="<?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>">
            <i class="fas fa-gem"></i> <span>Inventory</span>
        </a>
        
        <a href="sales_report.php" class="<?php echo $current_page == 'sales_report.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> <span>Sales Report</span>
        </a>
        
        <a href="customers.php" class="<?php echo $current_page == 'customers.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> <span>Customers</span>
        </a>

        <a href="view_inquiries.php" class="<?php echo $current_page == 'view_inquiries.php' ? 'active' : ''; ?>">
            <i class="fas fa-envelope"></i> <span>Inquiries</span>
        </a>

        <a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-shield"></i> <span>Settings</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-link">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    :root { 
        --gold: #d4af37; 
        --dark-bg: #121212; 
        --nav-hover: rgba(212, 175, 55, 0.1);
        --text-gray: #a0a0a0;
    }
    
    .sidebar { 
        width: 260px; 
        height: 100vh; 
        background: var(--dark-bg); 
        color: white; 
        position: fixed; 
        left: 0; 
        top: 0; 
        display: flex; 
        flex-direction: column; 
        padding: 30px 20px; 
        z-index: 9999;
        font-family: 'Inter', sans-serif;
        border-right: 1px solid #222;
    }

    .sidebar-brand {
        padding: 0 10px 30px 10px;
        text-align: left;
    }

    .sidebar-brand h2 { 
        color: var(--gold); 
        margin: 0;
        font-size: 20px; 
        font-weight: 800;
        letter-spacing: 1.5px;
    }

    .brand-subtitle {
        font-size: 11px;
        color: var(--text-gray);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 5px;
    }

    .sidebar-menu {
        flex-grow: 1; 
        margin-top: 20px;
    }

    .sidebar-menu a { 
        display: flex; 
        align-items: center;
        color: var(--text-gray); 
        padding: 14px 18px; 
        text-decoration: none; 
        border-radius: 12px; 
        margin-bottom: 8px; 
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 500;
    }

    /* Active & Hover State - Ito ang fix para hindi mag-iba ang button */
    .sidebar-menu a:hover, 
    .sidebar-menu a.active { 
        background: var(--nav-hover); 
        color: var(--gold) !important; 
    }

    .sidebar-menu a.active {
        background: linear-gradient(90deg, var(--nav-hover) 0%, transparent 100%);
        border-left: 3px solid var(--gold);
        border-radius: 0 12px 12px 0;
        padding-left: 15px; /* Adjust for border */
    }

    .sidebar-menu i { 
        margin-right: 15px; 
        font-size: 18px;
        width: 20px;
        text-align: center;
    }

    .notif-badge {
        background: #ff4d4d;
        color: white;
        font-size: 10px;
        padding: 2px 7px;
        border-radius: 8px;
        margin-left: auto;
    }

    .sidebar-footer { 
        padding-top: 20px;
        border-top: 1px solid #222;
    }

    .logout-link { 
        color: #ff6b6b !important; 
        text-decoration: none; 
        display: flex; 
        align-items: center;
        padding: 14px 18px;
        border-radius: 12px;
        transition: 0.3s;
    }

    .logout-link:hover {
        background: rgba(255, 107, 107, 0.1);
    }

    /* Mobile Setup */
    @media (max-width: 768px) {
        .sidebar { left: -260px; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar.active { left: 0; box-shadow: 10px 0 30px rgba(0,0,0,0.5); }
        .mobile-nav-toggle { display: block; position: fixed; top: 20px; left: 20px; z-index: 10001; background: var(--gold); border: none; padding: 10px 12px; border-radius: 8px; cursor: pointer; }
    }
</style>
