<?php
session_start();
include('db_conn.php');

if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// 1. FIXED SQL - Ginamit ang GROUP BY para hindi mag-duplicate ang rows
$sql = "SELECT orders.*, 
               MAX(products.image_path) AS product_img, 
               MAX(products.stock) AS current_stock
        FROM orders 
        LEFT JOIN products ON orders.order_items LIKE CONCAT('%', products.item_name, '%')"; 

if ($search != '') {
    $sql .= " WHERE orders.fullname LIKE '%$search%' OR orders.phone LIKE '%$search%'";
}

$sql .= " GROUP BY orders.id"; 
$sql .= " ORDER BY orders.id DESC";
$result = mysqli_query($conn, $sql);

if (!$result) { die("Query Failed: " . mysqli_error($conn)); }

// Stats Logic
$sum_res = mysqli_query($conn, "SELECT SUM(total_amount) as grand_total FROM orders");
$sum_row = mysqli_fetch_assoc($sum_res);
$grand_total = $sum_row['grand_total'] ?? 0;

$current_month = date('m'); $current_year = date('Y');
$monthly_res = mysqli_query($conn, "SELECT SUM(total_amount) as monthly_total FROM orders WHERE MONTH(order_date) = '$current_month' AND YEAR(order_date) = '$current_year'");
$monthly_row = mysqli_fetch_assoc($monthly_res);
$monthly_total = $monthly_row['monthly_total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders - Tina's Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; display: flex; min-height: 100vh; }
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        .admin-container { background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .dashboard-cards { display: flex; gap: 20px; margin-bottom: 35px; }
        .card { background: #1a1a1a; color: white; padding: 25px; border-radius: 15px; flex: 1; border-bottom: 4px solid #d4af37; }
        .card.monthly { background: #d4af37; color: #1a1a1a; border-bottom: 4px solid #1a1a1a; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #1a1a1a; color: #d4af37; padding: 15px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 13px; vertical-align: middle; }
        .item-img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; border: 1px solid #eee; }
        .status-select { padding: 8px; border-radius: 6px; font-weight: 600; font-size: 12px; border: 1px solid #ddd; cursor: pointer; transition: 0.3s; }
        .btn-export { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; }
        .proof-img { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 2px solid #f0f0f0; transition: 0.3s; cursor: pointer; }
        .proof-img:hover { transform: scale(1.1); border-color: #d4af37; }
        .badge-none { background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; }
        
        #imgModal { display: none; position: fixed; z-index: 1000; padding-top: 50px; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.95); }
        .modal-content { margin: auto; display: block; width: auto; max-width: 90%; max-height: 80vh; border-radius: 10px; border: 3px solid #d4af37; }
        .close { position: absolute; top: 20px; right: 35px; color: #fff; font-size: 40px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

    <?php include('sidebar.php'); ?>

    <div class="main-content">
        <div class="admin-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #1a1a1a;">Order Monitoring</h2>
                <button onclick="exportTableToExcel('orderTable')" class="btn-export"><i class="fas fa-file-excel"></i> Export Excel</button>
            </div>
            
            <div class="dashboard-cards">
                <div class="card"><h3>Total Orders</h3><p><?php echo mysqli_num_rows($result); ?></p></div>
                <div class="card monthly"><h3>Sales (Month)</h3><p>₱<?php echo number_format($monthly_total, 2); ?></p></div>
                <div class="card"><h3>Revenue</h3><p>₱<?php echo number_format($grand_total, 2); ?></p></div>
            </div>

            <div style="margin-bottom: 25px;">
                <form action="" method="GET" style="display:flex; gap:10px;">
                    <input type="text" name="search" placeholder="Search customer name or phone..." value="<?php echo htmlspecialchars($search); ?>" style="padding:12px; border-radius:8px; border:1px solid #ddd; width: 400px; font-size: 14px;">
                    <button type="submit" style="padding:10px 25px; background:#1a1a1a; color:#d4af37; border-radius:8px; border:none; cursor:pointer; font-weight: 600;">Search</button>
                </form>
            </div>

            <table id="orderTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Customer Information</th>
                        <th>Order Details</th>
                        <th>Amount</th>
                        <th>Payment Proof</th>
                        <th>Status Control</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        $raw_product = trim($row['product_img'] ?? '');
                        $raw_receipt = trim($row['receipt_img'] ?? '');
                        
                        $final_product_path = "../assets/img/no-item.png"; 
                        $final_receipt_path = "";
                        $folders = ['uploads/', '../uploads/', '../products/', 'products/'];

                        if (!empty($raw_product)) {
                            foreach ($folders as $f) {
                                if (file_exists($f . $raw_product)) { $final_product_path = $f . $raw_product; break; }
                            }
                        }

                        if (!empty($raw_receipt) && strtolower($raw_receipt) !== 'null') {
                            foreach ($folders as $rf) {
                                if (file_exists($rf . $raw_receipt)) { $final_receipt_path = $rf . $raw_receipt; break; }
                            }
                        }

                        // Status Color Logic
                        $status_color = "#999"; 
                        if($row['status'] == 'Paid') $status_color = "#27ae60";
                        if($row['status'] == 'Shipped') $status_color = "#2980b9";
                        if($row['status'] == 'Delivered') $status_color = "#1a1a1a";
                    ?>
                    <tr>
                        <td style="font-weight: 600; color: #888;">#<?php echo $row['id']; ?></td>
                        <td><img src="<?php echo $final_product_path; ?>" class="item-img"></td>
                        <td>
                            <span style="font-weight:600; color: #1a1a1a;"><?php echo htmlspecialchars($row['fullname']); ?></span><br>
                            <small style="color:#666;"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['phone']); ?></small>
                        </td>
                        <td>
                            <span style="font-weight:600; font-size: 12px;"><?php echo htmlspecialchars($row['order_items']); ?></span><br>
                            <?php 
                                $stock = $row['current_stock'] ?? 0;
                                $stock_style = ($stock <= 5) ? "color:#e74c3c; font-weight:bold;" : "color:#888;";
                            ?>
                            <span style="font-size:11px; <?php echo $stock_style; ?>">Stock Level: <?php echo $stock; ?></span>
                        </td>
                        <td style="font-weight:600; color: #d4af37;">₱<?php echo number_format($row['total_amount'], 2); ?></td>
                        <td style="text-align: center;">
                            <?php if ($final_receipt_path != ""): ?>
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                                    <img src="<?php echo $final_receipt_path; ?>" class="proof-img" onclick="openModal(this.src)" title="Click to preview">
                                    <a href="<?php echo $final_receipt_path; ?>" target="_blank" style="font-size: 10px; color: #d4af37; text-decoration: none; font-weight: bold;">
                                        <i class="fas fa-external-link-alt"></i> FULL VIEW
                                    </a>
                                </div>
                            <?php else: ?>
                                <span class="badge-none">NO RECEIPT</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form action="update_status.php" method="POST">
                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                <select name="new_status" onchange="this.form.submit()" class="status-select" style="border-left: 5px solid <?php echo $status_color; ?>; background: #fff;">
                                    <option value="Pending" <?php echo ($row['status']=='Pending'?'selected':''); ?>>Pending</option>
                                    <option value="Paid" <?php echo ($row['status']=='Paid'?'selected':''); ?>>Paid</option>
                                    <option value="Shipped" <?php echo ($row['status']=='Shipped'?'selected':''); ?>>Shipped</option>
                                    <option value="Delivered" <?php echo ($row['status']=='Delivered'?'selected':''); ?>>Delivered</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <a href="delete_order.php?id=<?php echo $row['id']; ?>" style="color:#e74c3c; padding: 8px;" onclick="return confirm('Are you sure you want to delete this order?')">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="imgModal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImg">
        <div id="caption" style="text-align:center; color:#ccc; padding:10px; font-size:14px;">Tina's Jewelry - Payment Verification Preview</div>
    </div>

    <script>
    function openModal(src) {
        document.getElementById("imgModal").style.display = "block";
        document.getElementById("modalImg").src = src;
    }
    function closeModal() {
        document.getElementById("imgModal").style.display = "none";
    }
    // Close modal when clicking outside the image
    window.onclick = function(event) {
        let modal = document.getElementById("imgModal");
        if (event.target == modal) { closeModal(); }
    }
    function exportTableToExcel(tableID, filename = 'Tina_Jewelries_Orders'){
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
        var downloadLink = document.createElement("a");
        document.body.appendChild(downloadLink);
        downloadLink.href = 'data:application/vnd.ms-excel,' + tableHTML;
        downloadLink.download = filename + '_' + new Date().toLocaleDateString() + '.xls';
        downloadLink.click();
    }
    </script>
</body>
</html>