<?php
session_start();
include('../db_conn.php');

// Kunin ang email mula sa URL
$email = $_GET['email'] ?? '';

// Query para makuha ang orders ng specific na customer
$sql = "SELECT * FROM orders WHERE email = '$email' ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Order History</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; padding: 20px; }
        h2 { color: #333; }
        
        /* Table Design */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th { background-color: #d4af37; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #ddd; }
        
        tr:hover { background-color: #f1f1f1; }
        
        .btn-back { display: inline-block; margin-top: 20px; padding: 10px 15px; background: #333; color: white; text-decoration: none; border-radius: 5px; }
        .btn-back:hover { background: #555; }
    </style>
</head>
<body>

    <h2>Order History for: <?php echo htmlspecialchars($email); ?></h2>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Items</th>
                <th>Total Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['order_items']); ?></td>
                    <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Walang record ng order para sa customer na ito.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <a href="view_client.php" class="btn-back">Back to Customer List</a>

</body>
</html>