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

// Bilangin din ang low stock para sa sidebar (optional but good for consistency)
$low_stock_query = mysqli_query($conn, "SELECT id FROM products WHERE stock <= 5");
$low_stock_count = mysqli_num_rows($low_stock_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Tina's Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background: #f4f7f6; }
        
        /* Sidebar Styles base sa dashboard mo */
        .sidebar { width: 250px; height: 100vh; background: #1a1a1a; color: white; position: fixed; padding: 20px; }
        .sidebar h2 { color: #d4af37; text-align: center; margin-bottom: 30px; }
        .sidebar a { display: block; color: #bbb; padding: 12px; text-decoration: none; border-radius: 5px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #d4af37; color: #1a1a1a; font-weight: bold; }
        
        /* Main Content */
        .main-content { margin-left: 250px; width: calc(100% - 250px); padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .add-btn { background: #d4af37; color: #1a1a1a; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }

        /* Table Styles */
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        th { background: #1a1a1a; color: #d4af37; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .product-img { width: 55px; height: 55px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; }
        
        /* Low Stock Warning Style */
        .low-stock-alert { color: #ff4d4d; font-weight: bold; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Tina's Gold</h2>
    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="view_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
    <a href="inventory.php" class="active"><i class="fas fa-gem"></i> Inventory 
        <?php if($low_stock_count > 0): ?>
            <span style="background: #ff4d4d; color: white; border-radius: 50%; padding: 2px 7px; font-size: 10px; margin-left: 5px;">
                <?php echo $low_stock_count; ?>
            </span>
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

    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // I-check kung may laman ang result
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) { 
                    $is_low = ($row['stock'] <= 5); // Warning logic
            ?>
            <tr>
                <td><img src="../uploads/<?php echo $row['image_path']; ?>" class="product-img" onerror="this.src='../image/logo.png.png'"></td>
                <td><strong><?php echo htmlspecialchars($row['item_name']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td>₱<?php echo number_format($row['price'], 2); ?></td>
                
                <td class="<?php echo $is_low ? 'low-stock-alert' : ''; ?>">
                    <?php echo $row['stock']; ?> pcs
                    <?php if($is_low): ?>
                        <br><small><i class="fas fa-exclamation-triangle"></i> Re-stock Needed</small>
                    <?php endif; ?>
                </td>

                <td>
                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" style="color: #007bff; margin-right: 10px;"><i class="fas fa-edit"></i></a>
                    <a href="delete_product.php?id=<?php echo $row['id']; ?>" style="color: #ff4d4d;" onclick="return confirm('Sigurado ka bang buburahin ito?')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php 
                } 
            } else {
                echo "<tr><td colspan='6' style='text-align:center; color:#888;'>Walang items sa inventory.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>