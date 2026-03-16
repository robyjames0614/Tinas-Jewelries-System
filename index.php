<?php
session_start();
include('admin/db_conn.php'); // Siguraduhing tama ang path papunta sa db_conn.php mo

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Kunin ang mga order ng user base sa kanilang logged-in username
$current_user = $_SESSION['username'];
$sql = "SELECT * FROM orders WHERE fullname = '$current_user' ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tina's Jewelry Shop - Dashboard</title>
  <link rel="stylesheet" href="index.css">
  <style>
    .user-greeting { color: #d4af37; font-weight: bold; margin-right: 10px; }
    .logout-btn { color: #ff4d4d !important; font-weight: bold; }
    .logout-btn:hover { text-decoration: underline; }

    /* Order History Styles */
    .order-history { max-width: 1100px; margin: 50px auto; padding: 20px; }
    .order-history h2 { font-family: 'Playfair Display', serif; color: #1a1a1a; margin-bottom: 20px; border-bottom: 2px solid #d4af37; display: inline-block; }
    
    .order-card { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #d4af37; }
    .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    
    /* Progress Bar (Galing sa track_order.php mo) */
    .progress-track { display: flex; justify-content: space-between; position: relative; margin-top: 25px; }
    .step { text-align: center; width: 25%; font-size: 11px; color: #ccc; position: relative; z-index: 1; }
    .step.active { color: #d4af37; font-weight: bold; }
    .step:before { content: ""; width: 12px; height: 12px; background: #eee; border-radius: 50%; display: block; margin: 0 auto 5px; }
    .step.active:before { background: #d4af37; }
    .line { position: absolute; top: 5px; left: 12%; width: 76%; height: 2px; background: #eee; z-index: 0; }
    .line-progress { position: absolute; top: 0; left: 0; height: 100%; background: #d4af37; transition: 0.5s; }
  </style>
</head>
<body>

<header>
  <nav class="navbar">
    <ul class="nav-left">
      <li><a href="index.php" class="active">Home</a></li>
      <li><a href="product.php">Product</a></li>
      <li><a href="about.html">About</a></li>
      <li><a href="contact.html">Contact</a></li>
      <li><a href="track_order.php" style="color: #d4af37; font-weight: bold;">🚚 Track My Order</a></li>
    </ul>
    
    <ul class="nav-right">
      <li><a href="cart.php">🛒</a></li>
      <li class="user-greeting">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>!</li>
      <li><a href="admin/logout.php" class="logout-btn" onclick="return confirm('Sigurado ka?')">Logout</a></li>
    </ul>
  </nav>
</header>

<section class="hero">
  <div class="hero-image">
    <img src="image/logo.png.jpg" alt="Tina's Jewelry Logo">
  </div>
  <div class="hero-text">
    <h1>Tina’s<br>Jewelry<br>Shop</h1>
    <p>Discover Stunning Jewelry At Irresistible Prices, Shine Brighter For Less Today!</p>
    <button onclick="location.href='product.php'">SHOP NOW</button>
  </div>
</section>

<section class="order-history">
    <h2>Your Orders</h2>
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while($order = mysqli_fetch_assoc($result)): ?>
            <div class="order-card">
                <div class="order-header">
                    <span><strong>Order #<?php echo $order['id']; ?></strong> | <?php echo $order['order_date']; ?></span>
                    <span style="font-weight: bold;">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <p style="font-size: 14px; color: #666;"><?php echo $order['order_items']; ?></p>

                <div class="progress-track">
                    <div class="line">
                        <?php 
                            $prog = "0%";
                            if($order['status'] == 'Pending') $prog = "0%";
                            if($order['status'] == 'Paid') $prog = "33%";
                            if($order['status'] == 'Shipped') $prog = "66%";
                            if($order['status'] == 'Delivered') $prog = "100%";
                        ?>
                        <div class="line-progress" style="width: <?php echo $prog; ?>;"></div>
                    </div>
                    <div class="step <?php echo (in_array($order['status'], ['Pending','Paid','Shipped','Delivered'])) ? 'active' : ''; ?>">Pending</div>
                    <div class="step <?php echo (in_array($order['status'], ['Paid','Shipped','Delivered'])) ? 'active' : ''; ?>">Paid</div>
                    <div class="step <?php echo (in_array($order['status'], ['Shipped','Delivered'])) ? 'active' : ''; ?>">Shipped</div>
                    <div class="step <?php echo ($order['status'] == 'Delivered') ? 'active' : ''; ?>">Delivered</div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="color: #888; font-style: italic;">You haven't placed any orders yet.</p>
    <?php endif; ?>
</section>

<script src="script.js"></script>
</body>
</html>