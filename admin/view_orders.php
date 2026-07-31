<?php
session_start();
include('../db_conn.php');

/** * UPDATED SECURITY LAYER */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// FIXED SQL: GROUP_CONCAT para sa lahat ng product images
$sql = "SELECT orders.*, 
                GROUP_CONCAT(DISTINCT products.image_path SEPARATOR ',') AS product_imgs, 
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
        body { background-color: #f0f2f5; display: flex; min-height: 100vh; overflow-x: hidden; color: #1a1a1a; }

        /* --- SIDEBAR SYNC --- */
        .sidebar { 
            width: 260px; height: 100vh; background: #1a1a1a; color: white; 
            position: fixed; left: 0; top: 0; padding: 20px; transition: 0.3s; z-index: 1100;
        }
        @media (max-width: 992px) {
            .sidebar { left: -260px; }
            .sidebar.active { left: 0; }
        }

        /* --- MOBILE HEADER / OVERLAY --- */
        .mobile-header {
            display: none; width: 100%; background: #1a1a1a; color: #d4af37;
            padding: 15px 20px; position: fixed; top: 0; left: 0; z-index: 1000;
            justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 1050;
        }
        @media (max-width: 992px) {
            .mobile-header { display: flex; }
            .overlay.active { display: block; }
        }
        
        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; width: calc(100% - 260px); transition: 0.3s; }
        
        @media (max-width: 992px) {
            .main-content { margin-left: 0; width: 100%; padding: 100px 20px 40px; }
        }

        .admin-container { 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(10px);
            padding: 25px; 
            border-radius: 24px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
        }

        /* Header Section */
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .header-title h2 { font-size: 24px; font-weight: 600; color: #111; }
        .header-title p { font-size: 13px; color: #666; }

        /* Dashboard Stats Cards */
        .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { 
            padding: 25px; border-radius: 20px; background: #fff; color: #1a1a1a; 
            border: 1px solid rgba(0,0,0,0.05); transition: 0.3s;
        }
        .card:hover { transform: translateY(-5px); }
        .card.highlight { background: #1a1a1a; color: #d4af37; }
        .card.monthly { background: linear-gradient(135deg, #d4af37, #f1c40f); color: #fff; border: none; }
        .card h3 { font-size: 12px; text-transform: uppercase; opacity: 0.8; letter-spacing: 1px; margin-bottom: 10px; }
        .card p { font-size: 26px; font-weight: 700; }

        /* Table Styling */
        .table-responsive { border-radius: 15px; overflow: hidden; background: #fff; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; color: #888; padding: 18px 15px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 15px; border-bottom: 1px solid #f1f1f1; font-size: 14px; }
        
        /* CLICKABLE ITEM IMAGE STYLING */
        .item-img { 
            width: 40px; height: 40px; border-radius: 8px; object-fit: cover; 
            border: 1px solid #eee; transition: 0.2s; cursor: pointer; 
        }
        .item-img:hover { transform: scale(1.15); z-index: 5; border-color: #d4af37; }

        .proof-img { width: 40px; height: 40px; border-radius: 8px; cursor: pointer; transition: 0.3s; object-fit: cover; border: 1px solid #ddd; }
        .proof-img:hover { transform: scale(1.05); }

        /* Mobile Table to Cards */
        @media (max-width: 992px) {
            .table-responsive { background: transparent; }
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr { background: white; margin-bottom: 20px; border-radius: 20px; padding: 15px; border: 1px solid #eee; }
            td { border: none; position: relative; padding-left: 50%; text-align: right; display: flex; justify-content: space-between; align-items: center; padding: 10px 5px; }
            td::before { content: attr(data-label); font-weight: 600; color: #888; font-size: 12px; text-transform: uppercase; }
        }

        /* Modal & Buttons */
        .btn-export { background: #10ac84; color: white; border: none; padding: 12px 20px; border-radius: 12px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .search-container { position: relative; flex: 1; max-width: 400px; }
        .search-container i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #aaa; }
        .search-container input { padding: 12px 12px 12px 45px; border-radius: 15px; border: 1px solid #eee; width: 100%; outline: none; }

        /* MODALS (GENERAL STYLING FOR PROOF & ITEM PREVIEW) */
        .custom-modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px); align-items: center; justify-content: center; }
        .modal-box { background: #fff; padding: 25px; border-radius: 20px; max-width: 440px; width: 90%; text-align: center; position: relative; box-shadow: 0 15px 35px rgba(0,0,0,0.3); }
        .modal-content { max-width: 100%; max-height: 60vh; border-radius: 12px; object-fit: contain; margin-top: 15px; border: 1px solid #eee; }
        .close { position: absolute; top: 12px; right: 20px; color: #888; font-size: 30px; cursor: pointer; font-weight: bold; transition: 0.2s; }
        .close:hover { color: #000; }
    </style>
</head>
<body>

    <div class="mobile-header">
        <div style="font-weight: bold; letter-spacing: 1px;">TINA'S ADMIN</div>
        <i class="fas fa-bars" style="font-size: 24px; cursor: pointer;" onclick="toggleSidebar()"></i>
    </div>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <div id="sidebar-container">
        <?php include('sidebar.php'); ?>
    </div>

    <div class="main-content">
        <div class="admin-container">
            <div class="header-flex">
                <div class="header-title">
                    <h2>Order Monitoring</h2>
                    <p>Track and manage your jewelry sales</p>
                </div>
                <button onclick="exportTableToExcel('orderTable')" class="btn-export">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
            
            <div class="dashboard-cards">
                <div class="card">
                    <h3>Active Orders</h3>
                    <p><?php echo mysqli_num_rows($result); ?></p>
                </div>
                <div class="card monthly">
                    <h3>Month's Revenue</h3>
                    <p>₱<?php echo number_format($monthly_total, 0); ?></p>
                </div>
                <div class="card highlight">
                    <h3>Total Sales</h3>
                    <p>₱<?php echo number_format($grand_total, 0); ?></p>
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <form action="" method="GET" style="display:flex; gap:10px; flex-wrap: wrap;">
                    <div class="search-container">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search customer..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" style="padding: 12px 25px; background: #1a1a1a; color: #d4af37; border-radius: 12px; border: none; cursor: pointer; font-weight: 600;">Search</button>
                </form>
            </div>

            <div class="table-responsive">
                <table id="orderTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Item</th>
                            <th>Customer</th>
                            <th>Details</th>
                            <th>Amount</th>
                            <th>Proof</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            // MULTI-IMAGE PARSING LOGIC
                            $raw_products = explode(',', $row['product_imgs'] ?? '');
                            $product_images_to_show = [];
                            $folders = ['uploads/', '../uploads/', '../products/', 'products/'];

                            foreach ($raw_products as $p_img) {
                                $p_img = trim($p_img);
                                if (!empty($p_img)) {
                                    foreach ($folders as $f) {
                                        if (file_exists($f . $p_img)) {
                                            $product_images_to_show[] = $f . $p_img;
                                            break;
                                        }
                                    }
                                }
                            }

                            if (empty($product_images_to_show)) {
                                $product_images_to_show[] = "../assets/img/no-item.png";
                            }

                            // RECEIPT IMAGE LOGIC
                            $raw_receipt = trim($row['receipt_img'] ?? '');
                            $final_receipt_path = "";
                            if (!empty($raw_receipt) && strtolower($raw_receipt) !== 'null') {
                                foreach ($folders as $rf) { if (file_exists($rf . $raw_receipt)) { $final_receipt_path = $rf . $raw_receipt; break; } }
                            }

                            $status_color = "#999"; 
                            if($row['status'] == 'Paid') $status_color = "#27ae60";
                            if($row['status'] == 'Shipped') $status_color = "#2980b9";
                            if($row['status'] == 'Delivered') $status_color = "#1a1a1a";
                            
                            $formatted_amount = "₱" . number_format($row['total_amount'], 2);
                        ?>
                        <tr>
                            <td data-label="Order ID">#<?php echo $row['id']; ?></td>
                            
                            <!-- CLICKABLE ITEM IMAGES -->
                            <td data-label="Item">
                                <div style="display: flex; gap: 4px; flex-wrap: wrap; max-width: 110px;">
                                    <?php foreach ($product_images_to_show as $img_path): ?>
                                        <img src="<?php echo $img_path; ?>" class="item-img" title="Click to view item" onclick="openItemModal('<?php echo $img_path; ?>', '#<?php echo $row['id']; ?>')">
                                    <?php endforeach; ?>
                                </div>
                            </td>

                            <td data-label="Customer">
                                <strong><?php echo htmlspecialchars($row['fullname']); ?></strong><br>
                                <small><?php echo htmlspecialchars($row['phone']); ?></small>
                            </td>
                            <td data-label="Details"><?php echo htmlspecialchars($row['order_items']); ?></td>
                            <td data-label="Amount" style="font-weight:700; color: #d4af37;"><?php echo $formatted_amount; ?></td>
                            <td data-label="Proof">
                                <?php if ($final_receipt_path != ""): ?>
                                    <img src="<?php echo $final_receipt_path; ?>" class="proof-img" onclick="openProofModal('<?php echo $final_receipt_path; ?>', '#<?php echo $row['id']; ?>', '<?php echo $formatted_amount; ?>')">
                                <?php else: ?>
                                    <span style="color:#e74c3c; font-size:11px; font-weight:600;">No Proof</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Status">
                                <form action="update_status.php" method="POST">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <select name="new_status" onchange="this.form.submit()" class="status-select" style="border-left: 4px solid <?php echo $status_color; ?>; padding: 6px 10px; border-radius: 8px;">
                                        <option value="Pending" <?php echo ($row['status']=='Pending'?'selected':''); ?>>Pending</option>
                                        <option value="Paid" <?php echo ($row['status']=='Paid'?'selected':''); ?>>Paid</option>
                                        <option value="Shipped" <?php echo ($row['status']=='Shipped'?'selected':''); ?>>Shipped</option>
                                        <option value="Delivered" <?php echo ($row['status']=='Delivered'?'selected':''); ?>>Delivered</option>
                                    </select>
                                </form>
                            </td>
                            <td data-label="Action">
                                <a href="delete_order.php?id=<?php echo $row['id']; ?>" style="color:#e74c3c;" onclick="return confirm('Delete?')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 1. RECEIPT PAYMENT PROOF MODAL -->
    <div id="imgModal" class="custom-modal">
        <div class="modal-box">
            <span class="close" onclick="closeModal('imgModal')">&times;</span>
            <h3 style="font-size: 1.1rem; color: #1a1a1a; margin-bottom: 2px;">GCash Payment Proof</h3>
            <p style="font-size: 0.85rem; color: #666; margin-bottom: 10px;">
                Order <strong id="modalOrderId" style="color: #1a1a1a;"></strong> | Expected Amount: <strong id="modalAmount" style="color: #d4af37;"></strong>
            </p>
            <img class="modal-content" id="modalImg" src="" alt="Payment Receipt">
        </div>
    </div>

    <!-- 2. NEW: ITEM PHOTO PREVIEW MODAL -->
    <div id="itemModal" class="custom-modal">
        <div class="modal-box">
            <span class="close" onclick="closeModal('itemModal')">&times;</span>
            <h3 style="font-size: 1.1rem; color: #1a1a1a; margin-bottom: 2px;">Ordered Item Preview</h3>
            <p style="font-size: 0.85rem; color: #666; margin-bottom: 10px;">
                Item for Order <strong id="itemModalOrderId" style="color: #d4af37;"></strong>
            </p>
            <img class="modal-content" id="itemModalImg" src="" alt="Product Image">
        </div>
    </div>

    <script>
        // SIDEBAR TOGGLE
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar'); 
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // Open GCash Proof Modal
        function openProofModal(src, orderId, totalAmount) {
            document.getElementById("imgModal").style.display = "flex";
            document.getElementById("modalImg").src = src;
            document.getElementById("modalOrderId").innerText = orderId;
            document.getElementById("modalAmount").innerText = totalAmount;
        }

        // Open Item Photo Preview Modal
        function openItemModal(src, orderId) {
            document.getElementById("itemModal").style.display = "flex";
            document.getElementById("itemModalImg").src = src;
            document.getElementById("itemModalOrderId").innerText = orderId;
        }

        // Generic Close Modal Function
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        // Isara kapag kinlik ang labas ng modal box
        window.onclick = function(event) {
            var proofModal = document.getElementById('imgModal');
            var itemModal = document.getElementById('itemModal');
            if (event.target == proofModal) { closeModal('imgModal'); }
            if (event.target == itemModal) { closeModal('itemModal'); }
        }

        // Excel Export
        function exportTableToExcel(tableID, filename = 'Orders_Report'){
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