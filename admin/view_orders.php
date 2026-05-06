<?php
session_start();
include('db_conn.php');

/** * UPDATED SECURITY LAYER 
 * Sinisiguro nito na Admin lang ang makakakita ng Order Monitoring.
 */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// FIXED SQL - Mas pinabilis na query at iwas duplicate
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

// Stats Logic para sa Cards
$sum_res = mysqli_query($conn, "SELECT SUM(total_amount) as grand_total FROM orders WHERE status != 'Cancelled'");
$sum_row = mysqli_fetch_assoc($sum_res);
$grand_total = $sum_row['grand_total'] ?? 0;

$current_month = date('m'); $current_year = date('Y');
$monthly_res = mysqli_query($conn, "SELECT SUM(total_amount) as monthly_total FROM orders WHERE MONTH(order_date) = '$current_month' AND YEAR(order_date) = '$current_year' AND status != 'Cancelled'");
$monthly_row = mysqli_fetch_assoc($monthly_res);
$monthly_total = $monthly_row['monthly_total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Monitoring - Tina's Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f4f7f6; display: flex; min-height: 100vh; overflow-x: hidden; }

        /* --- SIDEBAR SYNC --- */
        .sidebar { width: 260px; height: 100vh; background: #1a1a1a; position: fixed; left: 0; top: 0; padding: 20px; z-index: 1000; }
        
        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        
        .admin-container { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }

        /* Dashboard Stats Cards */
        .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { padding: 20px; border-radius: 15px; background: #1a1a1a; color: white; border-bottom: 4px solid #d4af37; }
        .card.monthly { background: #d4af37; color: #1a1a1a; border-bottom: 4px solid #1a1a1a; }
        .card h3 { font-size: 11px; text-transform: uppercase; opacity: 0.8; letter-spacing: 1px; }
        .card p { font-size: 22px; font-weight: 700; margin-top: 5px; }

        /* Table Styling */
        .table-responsive { overflow-x: auto; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { background: #f8f9fa; color: #888; padding: 15px; text-align: left; font-size: 11px; text-transform: uppercase; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #f0f0f0; font-size: 13px; vertical-align: middle; }
        
        .item-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; border: 1px solid #eee; }
        .proof-img { width: 45px; height: 45px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 2px solid #eee; transition: 0.3s; }
        .proof-img:hover { transform: scale(1.1); border-color: #d4af37; }

        /* Status & Badges */
        .status-select { padding: 8px 12px; border-radius: 8px; font-weight: 600; font-size: 12px; border: 1px solid #ddd; cursor: pointer; outline: none; }
        .badge-none { background: #fee2e2; color: #dc2626; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; }
        
        .btn-export { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        
        /* Modal Style */
        #imgModal { display: none; position: fixed; z-index: 2000; padding-top: 60px; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); }
        .modal-content { margin: auto; display: block; max-width: 80%; max-height: 80vh; border: 3px solid #d4af37; border-radius: 10px; }
        .close { position: absolute; top: 20px; right: 35px; color: white; font-size: 40px; cursor: pointer; }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 20px; }
            .sidebar { display: none; }
        }
    </style>
</head>
<body>

    <?php include('sidebar.php'); ?>

    <div class="main-content">
        <div class="admin-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h2 style="font-weight: 600; color: #1a1a1a;">Order Monitoring</h2>
                    <p style="font-size: 13px; color: #888;">Manage and verify jewelry orders</p>
                </div>
                <button onclick="exportTableToExcel('orderTable')" class="btn-export">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
            
            <div class="dashboard-cards">
                <div class="card"><h3>Total Active Orders</h3><p><?php echo mysqli_num_rows($result); ?></p></div>
                <div class="card monthly"><h3>This Month's Sales</h3><p>₱<?php echo number_format($monthly_total, 2); ?></p></div>
                <div class="card"><h3>Total Revenue</h3><p>₱<?php echo number_format($grand_total, 2); ?></p></div>
            </div>

            <div style="margin-bottom: 25px;">
                <form action="" method="GET" style="display:flex; gap:10px;">
                    <div style="position: relative; flex: 1; max-width: 400px;">
                        <i class="fas fa-search" style="position: absolute; left: 15px; top: 15px; color: #ccc;"></i>
                        <input type="text" name="search" placeholder="Search customer or phone..." value="<?php echo htmlspecialchars($search); ?>" 
                               style="padding: 12px 12px 12px 45px; border-radius: 10px; border: 1px solid #ddd; width: 100%; outline: none;">
                    </div>
                    <button type="submit" style="padding: 10px 25px; background: #1a1a1a; color: #d4af37; border-radius: 10px; border: none; cursor: pointer; font-weight: 600;">Search</button>
                </form>
            </div>

            <div class="table-responsive">
                <table id="orderTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Item</th>
                            <th>Customer</th>
                            <th>Items & Stock</th>
                            <th>Amount</th>
                            <th>Proof</th>
                            <th>Status Control</th>
                            <th>Delete</th>
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
                                foreach ($folders as $f) { if (file_exists($f . $raw_product)) { $final_product_path = $f . $raw_product; break; } }
                            }
                            if (!empty($raw_receipt) && strtolower($raw_receipt) !== 'null') {
                                foreach ($folders as $rf) { if (file_exists($rf . $raw_receipt)) { $final_receipt_path = $rf . $raw_receipt; break; } }
                            }

                            $status_color = "#999"; 
                            if($row['status'] == 'Paid') $status_color = "#27ae60";
                            if($row['status'] == 'Shipped') $status_color = "#2980b9";
                            if($row['status'] == 'Delivered') $status_color = "#1a1a1a";
                        ?>
                        <tr>
                            <td style="font-weight: 600; color: #888;">#<?php echo $row['id']; ?></td>
                            <td><img src="<?php echo $final_product_path; ?>" class="item-img"></td>
                            <td>
                                <span style="font-weight:600;"><?php echo htmlspecialchars($row['fullname']); ?></span><br>
                                <small style="color:#888;"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['phone']); ?></small>
                            </td>
                            <td>
                                <span style="font-weight:600; font-size: 12px;"><?php echo htmlspecialchars($row['order_items']); ?></span><br>
                                <?php $stock = $row['current_stock'] ?? 0; ?>
                                <small style="color: <?php echo ($stock <= 5) ? '#e74c3c' : '#888'; ?>;">Stock: <?php echo $stock; ?></small>
                            </td>
                            <td style="font-weight:700; color: #d4af37;">₱<?php echo number_format($row['total_amount'], 2); ?></td>
                            <td>
                                <?php if ($final_receipt_path != ""): ?>
                                    <img src="<?php echo $final_receipt_path; ?>" class="proof-img" onclick="openModal(this.src)" title="Check GCash Receipt">
                                <?php else: ?>
                                    <span class="badge-none">No Proof</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="update_status.php" method="POST">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <select name="new_status" onchange="this.form.submit()" class="status-select" style="border-left: 4px solid <?php echo $status_color; ?>;">
                                        <option value="Pending" <?php echo ($row['status']=='Pending'?'selected':''); ?>>Pending</option>
                                        <option value="Paid" <?php echo ($row['status']=='Paid'?'selected':''); ?>>Paid</option>
                                        <option value="Shipped" <?php echo ($row['status']=='Shipped'?'selected':''); ?>>Shipped</option>
                                        <option value="Delivered" <?php echo ($row['status']=='Delivered'?'selected':''); ?>>Delivered</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <a href="delete_order.php?id=<?php echo $row['id']; ?>" style="color:#e74c3c;" onclick="return confirm('Delete this order record?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="imgModal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImg">
    </div>

    <script>
        function openModal(src) {
            document.getElementById("imgModal").style.display = "block";
            document.getElementById("modalImg").src = src;
        }
        function closeModal() {
            document.getElementById("imgModal").style.display = "none";
        }
        window.onclick = function(e) { if (e.target == document.getElementById("imgModal")) closeModal(); }

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