<?php
// Siguraduhin na kasama ang db_conn.php para makapag-query tayo
include('db_conn.php'); 

// 1. Bilangin kung ilan ang 'Pending' orders
$count_query = "SELECT COUNT(*) as pending_count FROM orders WHERE status = 'Pending'";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$pending_orders = $count_row['pending_count'];
?>

<button class="mobile-nav-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar">
    <div class="sidebar-brand">
        <h2>Tina's Gold</h2>
    </div>
    
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        
        <a href="view_orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> Orders
            <?php if($pending_orders > 0): ?>
                <span class="notif-badge"><?php echo $pending_orders; ?></span>
            <?php endif; ?>
        </a>

        <a href="inventory.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>">
            <i class="fas fa-gem"></i> Inventory
        </a>
        
        <a href="sales_report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sales_report.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar"></i> Sales Report
        </a>
        
        <a href="customers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Customers
        </a>

        <a href="view_inquiries.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_inquiries.php' ? 'active' : ''; ?>">
            <i class="fa fa-envelope"></i> Customer Inquiries
        </a>

        <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Settings
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<style>
    :root { --gold: #d4af37; --dark: #1a1a1a; --gray: #bbb; }
    
    .sidebar { 
        width: 250px; 
        height: 100vh; 
        background: var(--dark); 
        color: white; 
        position: fixed; 
        left: 0; 
        top: 0; 
        display: flex; 
        flex-direction: column; 
        padding: 20px; 
        box-shadow: 4px 0 15px rgba(0,0,0,0.5); 
        z-index: 9999 !important;
        transition: 0.3s; /* Transition para sa sliding effect sa mobile */
    }

    .sidebar-brand h2 { 
        color: var(--gold); 
        text-align: center; 
        margin-bottom: 30px; 
        font-size: 22px; 
        border-bottom: 2px solid var(--gold); 
        padding-bottom: 10px; 
    }

    .sidebar-menu {
        flex-grow: 1; 
        overflow-y: auto; 
    }

    .sidebar-menu a { 
        display: flex; 
        align-items: center;
        color: var(--gray); 
        padding: 12px 15px; 
        text-decoration: none; 
        border-radius: 8px; 
        margin-bottom: 8px; 
        transition: 0.3s; 
        font-weight: 500; 
        font-size: 15px;
        position: relative;
    }

    .sidebar-menu a:hover, .sidebar-menu a.active { 
        background: var(--gold); 
        color: var(--dark) !important; 
        transform: translateX(5px); 
    }

    .notif-badge {
        background-color: #ff4d4d;
        color: white;
        font-size: 11px;
        font-weight: bold;
        padding: 2px 8px;
        border-radius: 20px;
        position: absolute;
        right: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .sidebar-menu i { margin-right: 12px; width: 20px; text-align: center; }

    .sidebar-footer { 
        margin-top: auto; 
        padding-top: 15px; 
        border-top: 1px solid #333; 
    }

    .logout-link { 
        color: #ff4d4d !important; 
        text-decoration: none; 
        display: flex; 
        align-items: center;
        padding: 12px 15px; 
    }

    /* MOBILE RESPONSIVE CSS */
    .mobile-nav-toggle {
        display: none; /* Default na tago sa desktop */
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 10000;
        background: var(--gold);
        color: var(--dark);
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 18px;
    }

    @media (max-width: 768px) {
        .mobile-nav-toggle {
            display: block; /* Lalabas lang ang button sa mobile */
        }

        .sidebar {
            left: -250px; /* Itago ang sidebar sa mobile by default */
        }

        .sidebar.active {
            left: 0; /* Slide in kapag 'active' class ay nadagdag */
        }
    }
</style>

<script>
// Function para i-show/hide ang sidebar sa mobile
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}
</script>