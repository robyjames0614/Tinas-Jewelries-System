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
        <a href="profile.php"><i class="fas fa-cog"></i> Settings</a>

        <li>
    <a href="view_inquiries.php">
        <i class="fa fa-envelope"></i> <span>Customer Inquiries</span>
    </a>
</li>
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
        z-index: 9999 !important; /* PINAKAMAHALAGA: Para laging nasa ibabaw */
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
        flex-grow: 1; /* Para itulak ang footer pababa */
        overflow-y: auto; /* Para kung marami ang buttons, pwedeng i-scroll */
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
    }

    .sidebar-menu a:hover, .sidebar-menu a.active { 
        background: var(--gold); 
        color: var(--dark) !important; 
        transform: translateX(5px); 
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
</style>