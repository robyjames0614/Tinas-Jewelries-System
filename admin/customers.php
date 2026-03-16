<?php
session_start();
include('db_conn.php');

// Security check
if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

// Kunin ang lahat ng unique na customers base sa pangalan at phone
$sql = "SELECT fullname, phone, address, COUNT(id) as total_orders, SUM(total_amount) as total_spent 
        FROM orders 
        GROUP BY fullname, phone 
        ORDER BY fullname ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Directory - Tina's Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; display: flex; min-height: 100vh; }
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        .admin-container { background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th { background: #1a1a1a; color: #d4af37; padding: 15px; text-align: left; font-size: 12px; text-transform: uppercase; border-radius: 5px 5px 0 0; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .customer-icon { background: #f1f1f1; padding: 10px; border-radius: 50%; color: #d4af37; margin-right: 10px; }
        .stats-badge { background: #d4af37; color: #1a1a1a; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .total-spent { color: #27ae60; font-weight: 600; }
    </style>
</head>
<body>

    <?php include('sidebar.php'); ?>

    <div class="main-content">
        <div class="admin-container">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                <i class="fas fa-users" style="font-size: 2rem; color: #1a1a1a;"></i>
                <h2>Customer Directory</h2>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Phone Number</th>
                        <th>Address</th>
                        <th>Orders Made</th>
                        <th>Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <i class="fas fa-user customer-icon"></i>
                                <strong><?php echo htmlspecialchars($row['fullname']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><small><?php echo htmlspecialchars($row['address']); ?></small></td>
                            <td><span class="stats-badge"><?php echo $row['total_orders']; ?> Order(s)</span></td>
                            <td><span class="total-spent">₱<?php echo number_format($row['total_spent'], 2); ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 30px; color: #888;">No customers found yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>