<?php
include 'db_conn.php';

$orders = []; 
$search_phone = "";
$error = "";

if (isset($_POST['track'])) {
    $search_phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    $sql = "SELECT * FROM orders WHERE phone = '$search_phone' ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row; 
        }
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
    <title>Track Your Order - Tina's Jewelries Gold Trading</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #fffaf5; margin: 0; padding: 20px; display: flex; justify-content: center; }
        .track-container { max-width: 600px; width: 100%; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; }
        h1 { font-family: 'Playfair Display', serif; color: #1a1a1a; margin-bottom: 10px; }
        p { color: #666; font-size: 14px; }
        .search-box { margin: 25px 0; }
        input[type="text"] { width: 80%; padding: 12px; border: 2px solid #d4af37; border-radius: 25px; outline: none; text-align: center; font-size: 16px; }
        button { background: #1a1a1a; color: #d4af37; border: none; padding: 12px 30px; border-radius: 25px; cursor: pointer; font-weight: 600; margin-top: 15px; transition: 0.3s; }
        button:hover { background: #d4af37; color: #1a1a1a; }
        
        .status-card { background: #fdfdfd; border: 1px solid #eee; padding: 20px; border-radius: 10px; margin-top: 30px; text-align: left; }
        .status-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }
        .order-id { font-weight: bold; color: #1a1a1a; }
        
        .order-item-box { display: flex; align-items: center; gap: 15px; background: #fafafa; padding: 10px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #f0f0f0; }
        .order-item-box img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; background: #fff; }
        
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
    <p>Ipasok ang iyong phone number para makita ang status at litrato ng iyong alahas.</p>

    <form method="POST" class="search-box">
        <input type="text" name="phone" placeholder="Halimbawa: 09123456789" value="<?php echo htmlspecialchars($search_phone); ?>" required>
        <br>
        <button type="submit" name="track">Check Status</button>
    </form>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order_data): ?>
            <div class="status-card" style="margin-bottom: 20px;">
                <div class="status-header">
                    <span class="order-id">Order #<?php echo $order_data['id']; ?></span>
                    <span style="font-size: 12px; color: #888;"><?php echo $order_data['order_date']; ?></span>
                </div>
                
                <p style="margin-bottom: 8px;"><strong>Inorder na Alahas:</strong></p>
                
                <?php 
                    $items_raw = $order_data['order_items'];
                    $single_items = explode(',', $items_raw);

                    foreach ($single_items as $item_str) {
                        $item_str = trim($item_str);
                        
                        $clean_name = preg_replace('/\s*\(.*?\)/', '', $item_str); 
                        $clean_name = trim($clean_name);

                        $img_filename = "";
                        $escaped_name = mysqli_real_escape_string($conn, $clean_name);
                        
                        $prod_query = mysqli_query($conn, "SELECT image_path FROM products WHERE item_name LIKE '%$escaped_name%' LIMIT 1");
                        
                        if ($prod_query && mysqli_num_rows($prod_query) > 0) {
                            $prod_row = mysqli_fetch_assoc($prod_query);
                            $img_filename = $prod_row['image_path'];
                        }
                ?>
                    <div class="order-item-box">
                        <?php if (!empty($img_filename)): ?>
                            <img src="uploads/<?php echo htmlspecialchars($img_filename); ?>" 
                                 onerror="this.onerror=null; this.src='image/<?php echo htmlspecialchars($img_filename); ?>';" 
                                 alt="Jewelry">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/60/fffaf5/d4af37?text=Gold" alt="Jewelry">
                        <?php endif; ?>

                        <div style="font-size: 13px; color: #333; font-weight: 500;">
                            <?php echo htmlspecialchars($item_str); ?>
                        </div>
                    </div>
                <?php } ?>

                <p style="margin-top: 15px;"><strong>Total Amount:</strong> ₱<?php echo number_format($order_data['total_amount'], 2); ?></p>

                <!-- LBC Tracking Section (Pinalaki at Pinaganda) -->
                <?php if ($order_data['status'] == 'Shipped' && !empty($order_data['tracking_number'])): ?>
                    <div style="background: #fffdf9; padding: 20px; border-radius: 12px; margin: 20px 0; border: 2px solid #d4af37; text-align: center; box-shadow: 0 4px 15px rgba(212,175,55,0.15);">
                        <p style="margin: 0 0 8px 0; font-size: 15px; color: #1a1a1a;">
                            <strong>LBC Tracking #:</strong> <span style="color: #b8860b; font-size: 18px; font-weight: 700; letter-spacing: 0.5px;"><?php echo htmlspecialchars($order_data['tracking_number']); ?></span>
                        </p>
                        <button onclick="navigator.clipboard.writeText('<?php echo $order_data['tracking_number']; ?>'); alert('Na-copy na ang tracking number!');" 
                                style="background: #1a1a1a; color: #d4af37; border: none; padding: 10px 22px; border-radius: 20px; cursor: pointer; font-size: 13px; font-weight: 600; margin-top: 8px; transition: 0.3s;">
                            <i class="fas fa-copy"></i> Copy Tracking Number
                        </button>
                        <div style="margin-top: 15px; font-size: 14px; color: #444; border-top: 1px dashed #e6d5b8; padding-top: 12px;">
                            Pumunta at i-paste ito sa <a href="https://www.lbcexpress.com" target="_blank" style="color: #b8860b; font-weight: 700; text-decoration: underline; font-size: 15px;">LBC Express Website</a> para i-track ang delivery.
                        </div>
                    </div>
                <?php endif; ?>

                <div class="progress-track">
                    <div class="line">
                        <?php 
                            $progress = "0%";
                            if($order_data['status'] == 'Pending') $progress = "0%";
                            elseif($order_data['status'] == 'Paid') $progress = "33%";
                            elseif($order_data['status'] == 'Shipped') $progress = "66%";
                            elseif($order_data['status'] == 'Delivered') $progress = "100%";
                        ?>
                        <div class="line-progress" style="width: <?php echo $progress; ?>;"></div>
                    </div>
                    <div class="step <?php echo in_array($order_data['status'], ['Pending','Paid','Shipped','Delivered']) ? 'active' : ''; ?>">Pending</div>
                    <div class="step <?php echo in_array($order_data['status'], ['Paid','Shipped','Delivered']) ? 'active' : ''; ?>">Paid</div>
                    <div class="step <?php echo in_array($order_data['status'], ['Shipped','Delivered']) ? 'active' : ''; ?>">Shipped</div>
                    <div class="step <?php echo ($order_data['status'] == 'Delivered') ? 'active' : ''; ?>">Delivered</div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <br>
    <a href="index.php" style="color: #d4af37; text-decoration: none; font-size: 13px;">← Bumalik sa Home</a>
</div>

</body>
</html>