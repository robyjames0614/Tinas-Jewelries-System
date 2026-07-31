<?php
session_start();
include('../db_conn.php');

// Security Check
if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

// 1. Handling Dates
$start_date = $_POST['start_date'] ?? date('Y-m-01'); 
$end_date = $_POST['end_date'] ?? date('Y-m-d');    

// 2. Query for Sales
$sql = "SELECT order_date, fullname, order_items, total_amount, status 
        FROM orders 
        WHERE (status LIKE '%Delivered%' OR status LIKE '%Paid%' OR status LIKE '%Shipped%') 
        AND DATE(order_date) BETWEEN ? AND ? 
        ORDER BY order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$temp_total = 0;
$rows = [];
while($r = $result->fetch_assoc()){
    $temp_total += $r['total_amount'];
    $rows[] = $r;
}

// Query for Sidebar Notifs
$pending_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM orders WHERE status='Pending'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - Tina's Gold Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7f6; display: flex; min-height: 100vh; }

        /* --- MOBILE HEADER (NEW) --- */
        .mobile-header {
            display: none;
            background: #1a1a1a;
            color: white;
            padding: 15px 20px;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1001;
        }
        .menu-btn {
            background: #d4af37;
            border: none;
            color: #1a1a1a;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
        }

        /* --- SIDEBAR --- */
        .sidebar { 
            width: 260px; height: 100vh; background: #1a1a1a; color: white; 
            position: fixed; left: 0; top: 0; padding: 20px; transition: 0.3s; z-index: 1000;
        }
        .sidebar h2 { color: #d4af37; text-align: center; margin-bottom: 30px; font-size: 22px; letter-spacing: 2px; border-bottom: 1px solid #333; padding-bottom: 15px; }
        .sidebar a { display: flex; align-items: center; color: #bbb; padding: 12px 15px; text-decoration: none; border-radius: 8px; margin-bottom: 8px; transition: 0.3s; font-size: 14px; }
        .sidebar a i { margin-right: 12px; width: 20px; text-align: center; }
        .sidebar a:hover, .sidebar a.active { background: rgba(212, 175, 55, 0.1); color: #d4af37; font-weight: 600; border-left: 4px solid #d4af37; }
        .notif-badge { background: #ff4d4d; color: white; font-size: 10px; padding: 2px 7px; border-radius: 50%; margin-left: auto; }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; transition: 0.3s; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }

        .revenue-banner {
            background: white; padding: 25px; border-radius: 15px; border-top: 4px solid #2e7d32;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 30px; display: inline-block; min-width: 300px;
        }
        .revenue-banner h3 { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
        .revenue-banner p { font-size: 28px; font-weight: 700; color: #2e7d32; }

        .filter-card { background: white; padding: 25px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .filter-grid { display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; }
        
        .input-group { display: flex; flex-direction: column; gap: 5px; }
        .input-group label { font-size: 12px; font-weight: 600; color: #555; }
        .input-group input { padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none; }

        .btn-gold { background: #d4af37; color: #1a1a1a; padding: 11px 25px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-dark { background: #1a1a1a; color: #d4af37; padding: 11px 25px; border: 1px solid #d4af37; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }

        .report-table-container { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th { background: #fdfdfd; color: #888; padding: 15px; text-align: left; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #eee; }
        td { padding: 18px 15px; border-bottom: 1px solid #f9f9f9; font-size: 14px; }
        
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: #e8f5e9; color: #2e7d32; }

        @media print { 
            .sidebar, .filter-card, .btn-dark, .mobile-header { display: none !important; } 
            .main-content { margin: 0; width: 100%; padding: 0; } 
        }

        @media (max-width: 992px) {
            .mobile-header { display: flex; }
            .sidebar { left: -260px; }
            .sidebar.active { left: 0; }
            .main-content { margin-left: 0; width: 100%; padding: 100px 20px 20px; }
            .header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .btn-dark { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<div class="mobile-header">
    <span style="font-weight: 700; color: #d4af37; letter-spacing: 1px;">TINA'S ADMIN</span>
    <button class="menu-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
</div>

<?php include('sidebar.php'); ?>

<div class="main-content">
    <div class="header">
        <div>
            <h1 style="font-size: 26px;">Sales Analytics</h1>
            <p style="color: #888; font-size: 14px;">Detailed report of your gold business transactions.</p>
        </div>
        <button onclick="window.print()" class="btn-dark"><i class="fas fa-print"></i> Print Report</button>
    </div>

    <div class="filter-card">
        <form method="POST" class="filter-grid">
            <div class="input-group">
                <label>Start Date</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="input-group">
                <label>End Date</label>
                <input type="date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <button type="submit" class="btn-gold">Apply Filter</button>
        </form>
    </div>

    <div class="revenue-banner">
        <h3>Total Revenue for Period</h3>
        <p>₱<?php echo number_format($temp_total, 2); ?></p>
    </div>

    <div class="report-table-container">
        <div class="table-responsive">
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
                    <?php if(count($rows) > 0): ?>
                        <?php foreach($rows as $row): ?>
                        <tr>
                            <td style="color: #666;"><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                            <td><strong style="color: #333;"><?php echo htmlspecialchars($row['fullname']); ?></strong></td>
                            <td style="color: #777; font-size: 13px;"><?php echo htmlspecialchars($row['order_items']); ?></td>
                            <td><span class="status-badge"><?php echo $row['status']; ?></span></td>
                            <td style="font-weight: 600; color: #d4af37;">₱<?php echo number_format($row['total_amount'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; padding: 50px; color: #999;">No transactions found for this period.</td></tr>
                    <?php endif; ?>
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