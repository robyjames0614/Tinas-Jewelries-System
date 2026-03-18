<?php
session_start();
include('admin/db_conn.php'); 

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap');

        /* --- GLOBAL --- */
        html { scroll-behavior: smooth; }
        body { 
            font-family: 'Poppins', sans-serif; 
            margin: 0; 
            overflow-x: hidden;
            background-image: linear-gradient(rgba(255, 255, 255, 0.75), rgba(255, 255, 255, 0.75)), 
                              url('image/background.jpg'); 
            background-size: cover; background-position: center; background-attachment: fixed;
        }
        
        /* --- NAVBAR --- */
        .navbar { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 0 5%; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; height: 70px;
        }
        .logo { font-family: 'Playfair Display'; font-weight: bold; font-size: 20px; color: #1a1a1a; letter-spacing: 1px; }
        .nav-links { display: flex; list-style: none; gap: 20px; align-items: center; margin: 0; padding: 0; }
        .nav-links a { text-decoration: none; color: #333; font-weight: 500; transition: 0.3s; font-size: 14px; position: relative; }
        .nav-links a::after { content: ''; position: absolute; width: 0; height: 2px; bottom: -5px; left: 0; background-color: #d4af37; transition: 0.3s; }
        .nav-links a:hover::after { width: 100%; }
        .nav-links a:hover, .nav-links a.active { color: #d4af37; }
        .mobile-menu-btn { display: none; font-size: 24px; cursor: pointer; color: #1a1a1a; }

        /* --- MESSENGER FLOATING BUTTON --- */
        .live-chat-btn {
            position: fixed; bottom: 25px; right: 25px; width: 60px; height: 60px;
            background: #0084ff; color: white; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            font-size: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 2000; transition: all 0.3s ease; text-decoration: none;
        }
        .live-chat-btn:hover { transform: scale(1.1) rotate(5deg); background-color: #0073e6; }

        /* --- HERO --- */
        .hero { 
            display: flex; align-items: center; justify-content: center; 
            padding: 60px 10%; min-height: 45vh; background: rgba(255, 255, 255, 0.3); 
            backdrop-filter: blur(8px); gap: 40px; border-bottom: 1px solid rgba(255,255,255,0.5);
        }
        .hero-image img { 
            max-width: 220px; width: 100%; border-radius: 50%; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: 5px solid #fff;
            animation: floatLogo 2s ease-in-out infinite; 
        }
        @keyframes floatLogo { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-15px); } }
        .hero-text h1 { font-family: 'Playfair Display', serif; font-size: 42px; color: #1a1a1a; }
        .hero-text button { 
            background: #1a1a1a; color: #d4af37; border: none; padding: 12px 35px; 
            font-weight: bold; cursor: pointer; border-radius: 5px; transition: 0.3s;
        }
        .hero-text button:hover { background: #d4af37; color: #1a1a1a; transform: scale(1.05); }

        /* --- ORDER CARDS --- */
        .order-history { max-width: 900px; margin: 40px auto; padding: 0 20px; min-height: 400px; }
        .order-card { 
            background: rgba(255, 255, 255, 0.85); border: 1px solid rgba(255, 255, 255, 0.4); 
            border-radius: 15px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 32px rgba(0,0,0,0.08); 
        }
        .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .view-details-btn { 
            background: transparent; border: 1px solid #d4af37; color: #d4af37; 
            padding: 6px 15px; border-radius: 20px; font-size: 11px; font-weight: bold; cursor: pointer;
        }
        .view-details-btn:hover { background: #d4af37; color: white; }

        /* Progress Track */
        .progress-track { display: flex; justify-content: space-between; position: relative; margin-top: 40px; }
        .step { text-align: center; width: 25%; font-size: 11px; color: #999; position: relative; z-index: 1; }
        .step.active { color: #d4af37; font-weight: 600; }
        .step:before { content: "\f058"; font-family: "Font Awesome 5 Free"; font-weight: 900; display: block; margin: 0 auto 8px; font-size: 18px; }
        .line { position: absolute; top: 10px; left: 12.5%; width: 75%; height: 3px; background: #ddd; z-index: 0; }
        .line-progress { position: absolute; top: 0; left: 0; height: 100%; background: #d4af37; transition: 1.5s ease; }

        /* --- FOOTER --- */
        footer { background: #1a1a1a; color: #fff; padding: 50px 10% 20px; margin-top: 50px; }
        .footer-content { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; margin-bottom: 40px; }
        .footer-section h3 { font-family: 'Playfair Display'; color: #d4af37; margin-bottom: 20px; }
        .footer-section p, .footer-section li { font-size: 14px; color: #bbb; line-height: 1.8; list-style: none; }
        .footer-section ul { padding: 0; }
        .footer-bottom { text-align: center; border-top: 1px solid #333; padding-top: 20px; font-size: 12px; color: #777; }

        @media (max-width: 768px) {
            .mobile-menu-btn { display: block; }
            .nav-links { display: none !important; flex-direction: column; position: absolute; top: 70px; left: 0; width: 100%; background: white; padding: 10px 0; }
            .nav-links.active { display: flex !important; }
            .hero { flex-direction: column-reverse; text-align: center; }
        }
    </style>
</head>
<body>

<a href="https://www.facebook.com/share/18ZHkBLsvR/?mibextid=wwXIfr" class="live-chat-btn" target="_blank" title="Chat with us!">
    <i class="fab fa-facebook-messenger"></i>
</a>

<header>
    <nav class="navbar">
        <div class="logo">TINAS JEWELRIES</div>
        <div class="mobile-menu-btn" onclick="toggleNav()">
            <i class="fas fa-bars"></i>
        </div>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="product.php">Product</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="track_order.php" style="color: #d4af37; font-weight: bold;">Track Order</a></li>
            <li style="padding: 10px 20px; background: rgba(0,0,0,0.05); border-radius: 20px; margin: 5px; display: flex; align-items: center;">
                <span style="font-size: 11px; color: #444;">Hi, <?php echo htmlspecialchars(strtoupper($_SESSION['username'])); ?>!</span>
                <a href="admin/logout.php" style="color: #ff4d4d; margin-left: 8px; font-size: 11px; text-decoration: none; font-weight: bold;">Logout</a>
            </li>
        </ul>
    </nav>
</header>

<section class="hero">
    <div class="hero-image">
        <img src="image/logo.png.jpg" alt="Logo">
    </div>
    <div class="hero-text">
        <span style="color: #d4af37; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; font-size: 13px;">Member Dashboard</span>
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Premium gold for your investment and style.</p>
        <button onclick="location.href='product.php'">Start Shopping</button>
    </div>
</section>

<section class="order-history">
    <h2 style="font-family: 'Playfair Display'; margin-bottom: 35px; text-align: center;">My Purchase History</h2>
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while($order = mysqli_fetch_assoc($result)): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <span style="font-size: 11px; color: #888; text-transform: uppercase;">Reference #<?php echo $order['id']; ?></span>
                        <div style="color: #b8860b; font-weight: bold; font-size: 1.4rem; margin-top: 5px;">₱<?php echo number_format($order['total_amount'], 2); ?></div>
                    </div>
                    <button class="view-details-btn" onclick="alert('Order details function coming soon!')">VIEW DETAILS</button>
                </div>
                
                <div class="progress-track">
                    <div class="line">
                        <?php 
                            $prog = "0%";
                            if($order['status'] == 'Pending') $prog = "0%";
                            elseif($order['status'] == 'Paid') $prog = "33%";
                            elseif($order['status'] == 'Shipped') $prog = "66%";
                            elseif($order['status'] == 'Delivered') $prog = "100%";
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
        <div style="text-align: center; color: #888; background: rgba(255,255,255,0.6); padding: 50px; border-radius: 15px; backdrop-filter: blur(5px);">
            <i class="fas fa-shopping-bag" style="font-size: 40px; margin-bottom: 15px; opacity: 0.3;"></i>
            <p>No orders yet. Start your gold investment today!</p>
        </div>
    <?php endif; ?>
</section>

<footer>
    <div class="footer-content">
        <div class="footer-section">
            <h3>Tina's Jewelries</h3>
            <p>Your trusted source for premium gold and timeless investments. We provide quality that lasts generations.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="product.php" style="color: #bbb; text-decoration: none;">New Arrivals</a></li>
                <li><a href="track_order.php" style="color: #bbb; text-decoration: none;">Track Order</a></li>
                <li><a href="contact.html" style="color: #bbb; text-decoration: none;">Support</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Us</h3>
            <p><i class="fas fa-envelope"></i> support@tinasjewelry.com</p>
            <p><i class="fas fa-phone"></i> +63 912 345 6789</p>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?php echo date("Y"); ?> Tina's Jewelry Shop. All Rights Reserved.
    </div>
</footer>

<script>
    function toggleNav() {
        const navLinks = document.getElementById('navLinks');
        navLinks.classList.toggle('active');
    }
</script>
</body>
</html>