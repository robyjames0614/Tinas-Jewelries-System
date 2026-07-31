<?php
session_start();
include('db_conn.php'); 

$isLoggedIn = isset($_SESSION['username']);
$current_user = $isLoggedIn ? $_SESSION['username'] : '';
$user_id = $isLoggedIn ? $_SESSION['username'] : session_id();

// DATABASE QUERY: Kunin ang totoong cart data
$cart_count = 0;
$grand_total = 0;
$cart_items_db = array();

$cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'");
if ($cart_query) {
    while ($row = mysqli_fetch_assoc($cart_query)) {
        $cart_items_db[] = $row;
        $cart_count += (int)$row['quantity'];
        $grand_total += ((float)$row['price'] * (int)$row['quantity']);
    }
}

// FORM SUBMISSION LOGIC (PHP)
$status_msg = "";
$status_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Opsyonal: I-save sa DB kung may table ka
    /*
    $insert_sql = "INSERT INTO contact_messages (name, email, message) VALUES ('$name', '$email', '$message')";
    mysqli_query($conn, $insert_sql);
    */

    $status_msg = "Salamat, $name! Matagumpay na naipadala ang iyong mensahe. Tutugunan ka namin agad sa iyong email.";
    $status_type = "success";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Tina's Jewelries Gold Trading</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="product.css">

    <style>
        /* ITAGO ANG MOBILE CONTROLS SA DESKTOP VIEW */
        .nav-right-controls { 
            display: none; 
            align-items: center; 
            gap: 15px; 
        }

        .contact-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 5%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .contact-info {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }

        .contact-info h2 {
            font-family: 'Playfair Display', serif;
            color: #1a1a1a;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item i {
            font-size: 20px;
            color: #d4af37;
            width: 30px;
            text-align: center;
        }

        .contact-form {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }

        .contact-form h2 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            box-sizing: border-box;
            outline: none;
            transition: 0.3s;
        }

        .form-group input:focus, .form-group textarea:focus {
            border-color: #d4af37;
            box-shadow: 0 0 5px rgba(212, 175, 55, 0.3);
        }

        .btn-send {
            background: #1a1a1a;
            color: #d4af37;
            border: none;
            padding: 12px;
            width: 100%;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-send:hover {
            background: #d4af37;
            color: #1a1a1a;
        }

        .alert-box {
            padding: 12px 15px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .alert-box.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* RESPONSIVE DESIGN RULES */
        @media (max-width: 768px) {
            .nav-right-controls { display: flex !important; }
            .nav-links .cart-wrapper { display: none !important; }
        }
    </style>
</head>
<body>

<header>
    <nav class="navbar">
        <a href="index.php" class="logo" style="text-decoration:none;">TINAS JEWELRIES</a>

        <!-- RIGHT ACTION BUTTONS (Lalabas agad sa Mobile View) -->
        <div class="nav-right-controls">
            <a href="cart.php" class="cart-icon-btn" title="View Cart">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge"><?php echo $cart_count; ?></span>
            </a>
            <div class="mobile-menu-btn" onclick="toggleNav()">
                <i class="fas fa-bars"></i>
            </div>
        </div>

        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Home</a></li>
            <li><a href="product.php">Product</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.php" class="active">Contact</a></li>
            <li><a href="track_order.php">Track Order</a></li>
            
            <!-- CART WITH MINI CART POPUP (DESKTOP VIEW) -->
            <li class="cart-wrapper" style="position: relative;">
                <a href="cart.php" id="cart-btn" onclick="toggleMiniCart(event)">
                    <i class="fas fa-shopping-cart"></i> Cart 
                    <span id="cart-count" class="cart-badge"><?php echo $cart_count; ?></span>
                </a>

                <div id="mini-cart-popup" class="mini-cart">
                    <div class="mini-cart-header">
                        <h4>Shopping Cart</h4>
                        <span class="close-mini-cart" onclick="closeMiniCart()">&times;</span>
                    </div>
                    <div id="mini-cart-items" class="mini-cart-body">
                        <?php if (!empty($cart_items_db)): ?>
                            <?php foreach ($cart_items_db as $item): 
                                $p_price = floatval($item['price']);
                                $qty = intval($item['quantity']);
                            ?>
                                <div class="mini-cart-item">
                                    <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" onerror="this.onerror=null; this.src='image/<?php echo htmlspecialchars($item['image']); ?>';" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <div class="item-details">
                                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p>₱<?php echo number_format($p_price, 2); ?> x <?php echo $qty; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="empty-msg" style="text-align: center; color: #777; font-size: 13px; padding: 15px 0;">Your cart is empty</p>
                        <?php endif; ?>
                    </div>
                    <div class="mini-cart-footer">
                        <div class="total-price">
                            <span>Total:</span> 
                            <strong id="mini-cart-total">₱<?php echo number_format($grand_total, 2); ?></strong>
                        </div>
                        <a href="cart.php" class="btn-view-cart">View Cart</a>
                        <a href="checkout.php" class="btn-checkout">Checkout</a>
                    </div>
                </div>
            </li>

            <?php if ($isLoggedIn): ?>
                <li style="padding: 5px 15px; background: rgba(0,0,0,0.05); border-radius: 20px;">
                    <span style="font-size: 11px;">Hi, <?php echo htmlspecialchars(strtoupper($current_user)); ?>!</span>
                    <a href="admin/logout.php" style="color: #ff4d4d; margin-left: 8px; font-size: 11px; text-decoration: none; font-weight: bold;">Logout</a>
                </li>
            <?php else: ?>
                <li><a href="login.php" style="color: #d4af37; font-size: 11px; text-decoration: none; font-weight: bold;">Login/Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div class="contact-container">
    <div class="contact-info">
        <h2>Get in Touch</h2>
        <p style="color: #666; font-size: 14px; margin-bottom: 25px;">May mga katanungan tungkol sa aming mga alahas? I-contact kami sa pamamagitan ng mga detalye sa ibaba.</p>
        
        <div class="info-item">
            <i class="fas fa-envelope"></i>
            <div>
                <strong>Email Address</strong>
                <p style="margin: 0; color: #555; font-size: 14px;">lyr216@gmail.com</p>
            </div>
        </div>

        <div class="info-item">
            <i class="fas fa-phone"></i>
            <div>
                <strong>Contact Number</strong>
                <p style="margin: 0; color: #555; font-size: 14px;">09477377683</p>
            </div>
        </div>

        <div class="info-item">
            <i class="fab fa-facebook-messenger"></i>
            <div>
                <strong>Facebook Page</strong>
                <p style="margin: 0; font-size: 14px;"><a href="https://www.facebook.com/share/18ZHkBLsvR/?mibextid=wwXIfr" target="_blank" style="color: #d4af37; text-decoration: none; font-weight: 600;">Tina's Jewelries Gold Trading</a></p>
            </div>
        </div>
    </div>

    <div class="contact-form">
        <h2>Send a Message</h2>

        <?php if (!empty($status_msg)): ?>
            <div class="alert-box <?php echo $status_type; ?>">
                <?php echo $status_msg; ?>
            </div>
        <?php endif; ?>

        <form action="contact.php" method="POST">
            <div class="form-group">
                <label>Your Name</label>
                <input type="text" name="name" required placeholder="Juan Dela Cruz">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="juan@gmail.com">
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" rows="4" required placeholder="Isulat ang iyong mensahe rito..."></textarea>
            </div>
            <button type="submit" name="send_message" class="btn-send">SEND MESSAGE</button>
        </form>
    </div>
</div>

<script>
    function toggleNav() { 
        document.getElementById('navLinks').classList.toggle('active'); 
    }

    function toggleMiniCart(e) {
        if (window.innerWidth > 768) {
            e.preventDefault();
            const miniCart = document.getElementById('mini-cart-popup');
            miniCart.classList.toggle('active');
        }
    }

    function closeMiniCart() {
        document.getElementById('mini-cart-popup').classList.remove('active');
    }

    window.addEventListener('click', function(e) {
        const miniCart = document.getElementById('mini-cart-popup');
        const cartBtn = document.getElementById('cart-btn');
        if (miniCart && miniCart.classList.contains('active')) {
            if (!miniCart.contains(e.target) && !cartBtn.contains(e.target)) {
                miniCart.classList.remove('active');
            }
        }
    });
</script>
</body>
</html>