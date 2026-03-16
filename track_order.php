<?php
include 'admin/db_conn.php';

$order_data = null;
$search_phone = "";

if (isset($_POST['track'])) {
    $search_phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    // Hanapin ang pinakabagong order gamit ang phone number
    $sql = "SELECT * FROM orders WHERE phone = '$search_phone' ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $order_data = mysqli_fetch_assoc($result);
    } else {
        $error = "Paumanhin, walang mahanap na order para sa numerong ito.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Order - Tina's Jewelries</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #fffaf5; margin: 0; padding: 20px; display: flex; justify-content: center; }
        .track-container { max-width: 500px; width: 100%; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; }
        h1 { font-family: 'Playfair Display', serif; color: #1a1a1a; margin-bottom: 10px; }
        p { color: #666; font-size: 14px; }
        .search-box { margin: 25px 0; }
        input[type="text"] { width: 80%; padding: 12px; border: 2px solid #d4af37; border-radius: 25px; outline: none; text-align: center; font-size: 16px; }
        button { background: #1a1a1a; color: #d4af37; border: none; padding: 12px 30px; border-radius: 25px; cursor: pointer; font-weight: 600; margin-top: 15px; transition: 0.3s; }
        button:hover { background: #d4af37; color: #1a1a1a; }
        
        /* Status Tracker Styles */
        .status-card { background: #fdfdfd; border: 1px solid #eee; padding: 20px; border-radius: 10px; margin-top: 30px; text-align: left; }
        .status-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }
        .order-id { font-weight: bold; color: #1a1a1a; }
        
        .progress-track { display: flex; justify-content: space-between; position: relative; margin-top: 20px; }
        .step { text-align: center; width: 25%; font-size: 10px; color: #ccc; position: relative; z-index: 1; }
        .step.active { color: #d4af37; font-weight: bold; }
        .step:before { content: ""; width: 15px; height: 15px; background: #eee; border-radius: 50%; display: block; margin: 0 auto 5px; }
        .step.active:before { background: #d4af37; box-shadow: 0 0 10px rgba(212, 175, 55, 0.5); }
        
        .line { position: absolute; top: 7px; left: 12%; width: 76%; height: 2px; background: #eee; z-index: 0; }
        .line-progress { position: absolute; top: 0; left: 0; height: 100%; background: #d4af37; transition: 0.5s; }
    </style>
</head>
<body>

<div class="track-container">
    <h1>Track Order</h1>
    <p>Ipasok ang iyong phone number para makita ang status ng iyong alahas.</p>

    <form method="POST" class="search-box">
        <input type="text" name="phone" placeholder="Halimbawa: 09123456789" value="<?php echo htmlspecialchars($search_phone); ?>" required>
        <br>
        <button type="submit" name="track">Check Status</button>
    </form>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($order_data): ?>
        <div class="status-card">
            <div class="status-header">
                <span class="order-id">Order #<?php echo $order_data['id']; ?></span>
                <span style="font-size: 12px; color: #888;"><?php echo $order_data['order_date']; ?></span>
            </div>
            
            <p><strong>Items:</strong> <?php echo $order_data['order_items']; ?></p>
            <p><strong>Total:</strong> ₱<?php echo number_format($order_data['total_amount'], 2); ?></p>

            <div class="progress-track">
                <div class="line">
                    <?php 
                        $progress = "0%";
                        if($order_data['status'] == 'Pending') $progress = "0%";
                        if($order_data['status'] == 'Paid') $progress = "33%";
                        if($order_data['status'] == 'Shipped') $progress = "66%";
                        if($order_data['status'] == 'Delivered') $progress = "100%";
                    ?>
                    <div class="line-progress" style="width: <?php echo $progress; ?>;"></div>
                </div>
                <div class="step <?php echo ($order_data['status'] == 'Pending' || $order_data['status'] == 'Paid' || $order_data['status'] == 'Shipped' || $order_data['status'] == 'Delivered') ? 'active' : ''; ?>">Pending</div>
                <div class="step <?php echo ($order_data['status'] == 'Paid' || $order_data['status'] == 'Shipped' || $order_data['status'] == 'Delivered') ? 'active' : ''; ?>">Paid</div>
                <div class="step <?php echo ($order_data['status'] == 'Shipped' || $order_data['status'] == 'Delivered') ? 'active' : ''; ?>">Shipped</div>
                <div class="step <?php echo ($order_data['status'] == 'Delivered') ? 'active' : ''; ?>">Delivered</div>
            </div>
        </div>
    <?php endif; ?>
    
    <br>
    <a href="index.php" style="color: #d4af37; text-decoration: none; font-size: 13px;">← Bumalik sa Home</a>
</div>

</body>
</html>