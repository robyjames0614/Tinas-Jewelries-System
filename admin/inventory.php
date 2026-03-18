<?php
session_start();
include('db_conn.php');

if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

// ITO ANG MAG-AAYOS NG FATAL ERROR SA INVENTORY
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

// Bilangin din ang low stock para sa sidebar
$low_stock_query = mysqli_query($conn, "SELECT id FROM products WHERE stock <= 5");
$low_stock_count = mysqli_num_rows($low_stock_query);

// Kunin din ang pending orders para sa sidebar notification
$pending_orders_query = mysqli_query($conn, "SELECT id FROM orders WHERE status = 'Pending'");
$pending_orders_count = mysqli_num_rows($pending_orders_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Tina's Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background: #f4f7f6; }
        
        /* Sidebar Styles (Consistent sa Sidebar.php mo) */
        .sidebar { width: 250px; height: 100vh; background: #1a1a1a; color: white; position: fixed; padding: 20px; transition: 0.3s; z-index: 999; }
        .sidebar h2 { color: #d4af37; text-align: center; margin-bottom: 30px; border-bottom: 2px solid #d4af37; padding-bottom: 10px; }
        .sidebar a { display: block; color: #bbb; padding: 12px; text-decoration: none; border-radius: 5px; margin-bottom: 5px; position: relative; }
        .sidebar a:hover, .sidebar a.active { background: #d4af37; color: #1a1a1a; font-weight: bold; }
        
        /* Main Content */
        .main-content { margin-left: 250px; width: calc(100% - 250px); padding: 40px; transition: 0.3s; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .add-btn { background: #d4af37; color: #1a1a1a; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; transition: 0.3s; }
        .add-btn:hover { background: #b8962d; transform: translateY(-2px); }

        /* Responsive Table Wrapper */
        .table-container { 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); 
            overflow: hidden; 
        }
        
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th { background: #1a1a1a; color: #d4af37; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .product-img { width: 55px; height: 55px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; }
        
        .low-stock-alert { color: #ff4d4d; font-weight: bold; background: #fff5f5; }

        /* Mobile Toggle Button */
        .mobile-nav-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1000;
            background: #d4af37;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        /* MEDIA QUERIES PARA SA CELLPHONE */
        @media (max-width: 768px) {
            .mobile-nav-toggle { display: block; }
            .sidebar { left: -250px; }
            .sidebar.active { left: 0; }
            .main-content { margin-left: 0; width: 100%; padding: 70px 20px 20px 20px; }
            .header h1 { font-size: 24px; }
            .add-btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

<button class="mobile-nav-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar">
    <h2>Tina's Gold</h2>
    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="view_orders.php"><i class="fas fa-shopping-cart"></i> Orders 
        <?php if($pending_orders_count > 0): ?>
            <span style="background: #ff4d4d; color: white; border-radius: 10px; padding: 2px 8px; font-size: 10px; float: right;"><?php echo $pending_orders_count; ?></span>
        <?php endif; ?>
    </a>
    <a href="inventory.php" class="active"><i class="fas fa-gem"></i> Inventory 
        <?php if($low_stock_count > 0): ?>
            <span style="background: #ff4d4d; color: white; border-radius: 10px; padding: 2px 8px; font-size: 10px; float: right;"><?php echo $low_stock_count; ?></span>
        <?php endif; ?>
    </a>
    <a href="sales_report.php"><i class="fas fa-file-invoice-dollar"></i> Sales Report</a>
    <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
    <a href="logout.php" style="margin-top: 50px; color: #ff4d4d;"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Jewelry Inventory</h1>
        <a href="add_product.php" class="add-btn"><i class="fas fa-plus"></i> Add New Item</a>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) { 
                            $is_low = ($row['stock'] <= 5);
                    ?>
                    <tr class="<?php echo $is_low ? 'low-stock-alert' : ''; ?>">
                        <td><img src="../uploads/<?php echo $row['image_path']; ?>" class="product-img" onerror="this.src='../image/logo.png.png'"></td>
                        <td><strong><?php echo htmlspecialchars($row['item_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td>₱<?php echo number_format($row['price'], 2); ?></td>
                        
                        <td>
                            <?php echo $row['stock']; ?> pcs
                            <?php if($is_low): ?>
                                <br><small style="color: #ff4d4d;"><i class="fas fa-exclamation-triangle"></i> Low Stock</small>
                            <?php endif; ?>
                        </td>

                        <td>
                            <a href="edit_product.php?id=<?php echo $row['id']; ?>" style="color: #007bff; margin-right: 15px;"><i class="fas fa-edit"></i></a>
                            <a href="delete_product.php?id=<?php echo $row['id']; ?>" style="color: #ff4d4d;" onclick="return confirm('Sigurado ka bang buburahin ito?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php 
                        } 
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; color:#888; padding: 50px;'>Walang items sa inventory.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }
</script>

</body>
</html>