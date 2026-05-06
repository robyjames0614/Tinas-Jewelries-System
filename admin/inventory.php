<?php
session_start();
include('db_conn.php');

// Security check
if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

// 1. Kunin ang lahat ng products
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

// 2. Bilangin ang low stock para sa badge (Stock <= 5)
$low_stock_query = mysqli_query($conn, "SELECT id FROM products WHERE stock <= 5");
$low_stock_count = mysqli_num_rows($low_stock_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Tina's Gold Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: #f8f9fa; min-height: 100vh; }
        
        /* Main Content Area */
        .main-content { 
            margin-left: 260px; /* Sakto sa width ng sidebar */
            width: calc(100% - 260px); 
            padding: 40px; 
            transition: 0.3s; 
        }
        
        /* Header Styling */
        .header-section { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
        }
        
        .header-section h1 { font-size: 24px; color: #1a1a1a; font-weight: 700; }
        
        .add-btn { 
            background: #d4af37; 
            color: #1a1a1a; 
            padding: 12px 24px; 
            text-decoration: none; 
            border-radius: 10px; 
            font-weight: 600; 
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s; 
        }
        .add-btn:hover { background: #b8962d; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3); }

        /* Inventory Card & Table */
        .inventory-card { 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); 
            overflow: hidden; 
            padding: 20px;
        }

        .table-responsive { width: 100%; overflow-x: auto; }
        
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th { background: #fdfdfd; color: #888; padding: 15px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #eee; }
        td { padding: 18px 15px; border-bottom: 1px solid #f9f9f9; vertical-align: middle; font-size: 14px; }

        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
        
        /* Status Badges */
        .badge-category { background: #f1f3f5; color: #666; padding: 4px 10px; border-radius: 6px; font-size: 12px; }
        .price-text { font-weight: 600; color: #d4af37; }
        
        .stock-tag { font-weight: 600; }
        .low-stock-alert { color: #ff4d4d; background: rgba(255, 77, 77, 0.05); }
        .low-stock-label { color: #ff4d4d; font-size: 11px; display: block; margin-top: 4px; font-weight: 700; }

        /* Actions */
        .action-links a { margin-right: 12px; font-size: 16px; transition: 0.2s; }
        .edit-icon { color: #3498db; }
        .delete-icon { color: #e74c3c; }
        .edit-icon:hover, .delete-icon:hover { opacity: 0.7; }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .main-content { margin-left: 0; width: 100%; padding: 80px 20px 20px; }
            .header-section { flex-direction: column; align-items: flex-start; gap: 15px; }
            .add-btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <?php include('sidebar.php'); ?>

    <div class="main-content">
        <div class="header-section">
            <div>
                <h1>Jewelry Inventory</h1>
                <p style="color: #888; font-size: 13px;">You have <?php echo mysqli_num_rows($result); ?> total items in your collection.</p>
            </div>
            <a href="add_product.php" class="add-btn">
                <i class="fas fa-plus"></i> Add New Item
            </a>
        </div>

        <div class="inventory-card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Item Details</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) { 
                                $is_low = ($row['stock'] <= 5);
                        ?>
                        <tr class="<?php echo $is_low ? 'low-stock-alert' : ''; ?>">
                            <td>
                                <img src="../uploads/<?php echo $row['image_path']; ?>" 
                                     class="product-img" 
                                     onerror="this.src='../assets/img/no-item.png'">
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #333;"><?php echo htmlspecialchars($row['item_name']); ?></div>
                                <small style="color: #999;">ID: #<?php echo $row['id']; ?></small>
                            </td>
                            <td><span class="badge-category"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td><span class="price-text">₱<?php echo number_format($row['price'], 2); ?></span></td>
                            <td>
                                <span class="stock-tag"><?php echo $row['stock']; ?> pcs</span>
                                <?php if($is_low): ?>
                                    <span class="low-stock-label"><i class="fas fa-exclamation-circle"></i> REORDER SOON</span>
                                <?php endif; ?>
                            </td>
                            <td class="action-links">
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="edit-icon" title="Edit Item"><i class="fas fa-edit"></i></a>
                                <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="delete-icon" title="Delete Item" onclick="return confirm('Sigurado ka bang buburahin ang item na ito?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 50px; color: #999;'>No items found in inventory.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Toggle Sidebar function para sa mobile toggle sa sidebar.php
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>
</body>
</html>