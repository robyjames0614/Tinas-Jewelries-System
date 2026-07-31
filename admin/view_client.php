<?php
session_start();
include('../db_conn.php');

// Pinagsama natin ang users at orders table
// Gagamit tayo ng subquery para makuha ang pinakabagong order details ng bawat user
$query = "SELECT u.*, 
          (SELECT phone FROM orders WHERE orders.email = u.email ORDER BY order_date DESC LIMIT 1) as latest_phone,
          (SELECT address FROM orders WHERE orders.email = u.email ORDER BY order_date DESC LIMIT 1) as latest_address,
          (SELECT COUNT(*) FROM orders WHERE orders.email = u.email) as total_orders
          FROM users u 
          ORDER BY u.id DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Orders Tracker</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #d4af37; color: white; }
        .has-order { color: green; font-weight: bold; }
        .no-order { color: gray; }
        .btn-view { padding: 5px 10px; background: #d4af37; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>

    <h2>Customer Orders Tracker</h2>
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Latest Phone</th>
            <th>Latest Address</th>
            <th>Orders Count</th>
            <th>Actions</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <!-- Dito lalabas yung galing sa orders table -->
            <td><?php echo $row['latest_phone'] ?? 'Walang Order'; ?></td>
            <td><?php echo $row['latest_address'] ?? 'Walang Order'; ?></td>
            <td>
                <?php if($row['total_orders'] > 0): ?>
                    <span class="has-order"><?php echo $row['total_orders']; ?> order(s)</span>
                <?php else: ?>
                    <span class="no-order">0</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if($row['total_orders'] > 0): ?>
                    <a href="client_orders.php?email=<?php echo urlencode($row['email']); ?>" class="btn-view">View Orders</a>
                <?php else: ?>
                    <span>-</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="dashboard.php">← Back to Dashboard</a>
</body>
</html>