<?php
session_start();
include('db_conn.php');

if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

// 1. Mas ligtas na pag-handle ng Dates
$start_date = $_POST['start_date'] ?? date('Y-m-01'); 
$end_date = $_POST['end_date'] ?? date('Y-m-d');    

// 2. Optimized Query: Isama ang 'Shipped' kung gusto mong makita ang pending income
// Pero para sa Final Sales, 'Delivered' at 'Paid' ang standard
$sql = "SELECT order_date, fullname, order_items, total_amount, status 
        FROM orders 
        WHERE (status LIKE '%Delivered%' OR status LIKE '%Paid%' OR status LIKE '%Shipped%') 
        AND DATE(order_date) BETWEEN ? AND ? 
        ORDER BY order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$total_period_sales = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report - Tina's Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Pinatibay na CSS para sa Printing */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; height: 100vh; background: #1a1a1a; color: white; position: fixed; padding: 20px; }
        .sidebar h2 { color: #d4af37; margin-bottom: 30px; text-align: center; }
        .sidebar a { display: block; color: #bbb; padding: 12px; text-decoration: none; border-radius: 5px; margin-bottom: 5px; transition: 0.3s; }
        .sidebar a:hover { background: #d4af37; color: #1a1a1a; }
        
        .main-content { margin-left: 250px; width: calc(100% - 250px); padding: 40px; }
        .filter-section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; display: flex; gap: 20px; align-items: flex-end; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        th { background: #1a1a1a; color: #d4af37; padding: 15px; text-align: left; text-transform: uppercase; font-size: 13px; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-paid { background: #e8f5e9; color: #2e7d32; }

        .print-btn { background: #2e7d32; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        
        /* Media Query para sa malinis na Printout */
        @media print { 
            .sidebar, .filter-section, .print-btn, .logout-btn { display: none !important; } 
            .main-content { margin: 0; width: 100%; padding: 0; } 
            table { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Tina's Gold</h2>
    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="view_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
    <a href="sales_report.php" style="background:#d4af37; color:#1a1a1a;"><i class="fas fa-file-invoice-dollar"></i> Sales Report</a>
    <a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a>
    <a href="logout.php" class="logout-btn" style="margin-top: 50px; color: #ff4d4d;"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <h1>Sales Report</h1>
    <p style="margin-bottom: 20px; color: #666;">Monitoring your gold business growth.</p>

    <form method="POST" class="filter-section">
        <div>
            <label style="font-size: 12px; font-weight: bold;">Start Date</label><br>
            <input type="date" name="start_date" value="<?php echo $start_date; ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
        </div>
        <div>
            <label style="font-size: 12px; font-weight: bold;">End Date</label><br>
            <input type="date" name="end_date" value="<?php echo $end_date; ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
        </div>
        <button type="submit" style="background: #1a1a1a; color: #d4af37; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Apply Filter</button>
        <button type="button" onclick="window.print()" class="print-btn"><i class="fas fa-print"></i> Print Report</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Items Sold</th>
                <th>Status</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): 
                    $total_period_sales += $row['total_amount'];
                ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                    <td><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($row['fullname']); ?></td>
                    <td style="color: #555; font-style: italic;"><?php echo htmlspecialchars($row['order_items']); ?></td>
                    <td><span class="status-badge badge-paid"><?php echo $row['status']; ?></span></td>
                    <td style="font-weight: bold;">₱<?php echo number_format($row['total_amount'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
                <tr style="background: #f9f9f9; font-weight: bold; font-size: 20px;">
                    <td colspan="4" style="text-align: right; padding: 20px;">TOTAL REVENUE:</td>
                    <td style="color: #2e7d32; border-top: 2px solid #1a1a1a;">₱<?php echo number_format($total_period_sales, 2); ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #999;">No sales recorded for this period.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>