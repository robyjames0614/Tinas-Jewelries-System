<?php
include 'db_conn.php';

// Kunin ang lahat ng orders mula sa database
$sql = "SELECT * FROM orders ORDER BY order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Tina's Jewelries</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0; padding: 40px;
        }
        h1 {
            font-family: 'Playfair Display', serif;
            color: #1a1a1a;
            text-align: center;
        }
        .admin-container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #1a1a1a;
            color: #d4af37;
            padding: 15px;
            text-align: left;
            font-size: 0.9rem;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            font-size: 0.85rem;
        }
        tr:hover { background-color: #fdfbf7; }
        .status {
            color: green;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.7rem;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>Order Management</h1>
    <p style="text-align: center; color: #666;">Dito mo makikita ang mga pumasok na orders mula sa website.</p>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Payment</th>
                <th>Total Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>#{$row['id']}</td>
                            <td><strong>{$row['fullname']}</strong></td>
                            <td>{$row['address']}</td>
                            <td>{$row['phone']}</td>
                            <td>{$row['payment_method']}</td>
                            <td style='color: #d4af37; font-weight: 600;'>₱" . number_format($row['total_amount'], 2) . "</td>
                            <td>" . date('M d, Y h:i A', strtotime($row['order_date'])) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align:center;'>Wala pang orders na nakatala.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>