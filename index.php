<?php
session_start();
include('db_conn.php'); 

// Check status para sa display ng member/user greeting
$isLoggedIn = isset($_SESSION['username']);
$current_user = $isLoggedIn ? $_SESSION['username'] : '';
$user_id = $isLoggedIn ? $_SESSION['username'] : session_id();

// DATABASE QUERY: Kwentahin ang kabuuang bilang ng items sa database cart
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

// Alamin ang kasalukuyang page para sa active navigation state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tina's Jewelries - Gold Trading & Jewelry Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="index.css?v=<?php echo time(); ?>">

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fffaf5; color: #1a1a1a; margin: 0; padding: 0; overflow-x: hidden; }
        
        /* NAVBAR & HEADER STYLES */
        .navbar { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 0 5%; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky; top: 0; z-index: 1000; height: 70px;
        }
        .logo { font-family: 'Playfair Display', serif; font-weight: bold; font-size: 20px; color: #1a1a1a; letter-spacing: 1px; text-decoration: none; }
        .nav-links { display: flex; list-style: none; gap: 20px; align-items: center; margin: 0; padding: 0; }
        .nav-links a { text-decoration: none; color: #333; font-weight: 500; transition: 0.3s; font-size: 14px; }
        .nav-links a:hover, .nav-links a.active { color: #d4af37; font-weight: 600; }
        
        /* MOBILE CONTROLS */
        .nav-right-controls { display: none; }
        
        .cart-icon-btn { 
            color: #d4af37; font-size: 18px; text-decoration: none; 
            display: flex; align-items: center; gap: 5px; position: relative; cursor: pointer;
        }
        
        /* CART BADGE */
        .cart-badge {
            background-color: #d4af37; color: #fff; font-size: 10px;
            font-weight: bold; padding: 2px 6px; border-radius: 50%;
            margin-left: 3px; display: inline-block; vertical-align: middle;
        }

        .user-nav-badge {
            padding: 6px 14px; background: rgba(212, 175, 55, 0.1); border-radius: 20px; 
            display: flex; align-items: center; gap: 8px; border: 1px solid rgba(212, 175, 55, 0.3);
        }
        .user-nav-badge span { font-size: 11px; color: #1a1a1a; font-weight: 600; }
        .logout-link { color: #ff4d4d !important; font-size: 11px; font-weight: bold; text-decoration: none; }
        .mobile-menu-btn { display: none; font-size: 22px; cursor: pointer; color: #1a1a1a; }

        /* MINI CART WRAPPER & POPUP */
        .cart-nav-item { position: relative; }
        .mini-cart {
            display: none; position: absolute; top: 40px; right: 0; width: 320px;
            background: #fff; border: 1px solid #d4af37; box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border-radius: 8px; z-index: 1001; padding: 15px; box-sizing: border-box; text-align: left;
        }
        .mini-cart.active { display: block; }
        .mini-cart-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .mini-cart-header h4 { margin: 0; font-family: 'Playfair Display', serif; font-size: 16px; color: #1a1a1a; }
        .close-mini-cart { cursor: pointer; font-size: 20px; color: #888; font-weight: bold; }
        .mini-cart-body { max-height: 250px; overflow-y: auto; margin: 10px 0; }
        .mini-cart-item { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; border-bottom: 1px dashed #eee; padding-bottom: 8px; }
        .mini-cart-item img { width: 45px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .item-details h5 { margin: 0 0 3px 0; font-size: 13px; color: #333; }
        .item-details p { margin: 0; font-size: 12px; color: #d4af37; font-weight: 600; }
        .mini-cart-footer { border-top: 1px solid #eee; padding-top: 10px; }
        .mini-cart-footer .total-price { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .btn-view-cart, .btn-checkout { display: block; text-align: center; padding: 8px; text-decoration: none; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px; }
        .btn-view-cart { background: #fff; color: #1a1a1a; border: 1px solid #1a1a1a; }
        .btn-checkout { background: #1a1a1a; color: #fff; }

        /* TOAST NOTIFICATION POPUP */
        .toast-notif {
            visibility: hidden; min-width: 280px; background-color: #1a1a1a; color: #fff;
            text-align: center; border-radius: 8px; padding: 12px 20px; position: fixed;
            z-index: 9999; right: 30px; bottom: 30px; font-size: 13px; border-left: 4px solid #d4af37;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2); opacity: 0; transition: opacity 0.3s, bottom 0.3s;
        }
        .toast-notif.show { visibility: visible; opacity: 1; bottom: 40px; }

        /* MOBILE VIEW STYLES */
        @media (max-width: 768px) {
            .navbar { padding: 0 20px; }
            .nav-right-controls { display: flex; align-items: center; gap: 15px; }
            .mobile-menu-btn { display: block; }
            .nav-links { 
                display: none; flex-direction: column; position: absolute; 
                top: 70px; left: 0; width: 100%; background: #fff; 
                padding: 20px 0; box-shadow: 0 10px 20px rgba(0,0,0,0.1); gap: 15px; text-align: center;
            }
            .nav-links.active { display: flex !important; }
            .nav-links .cart-nav-item { display: none; }
            .user-nav-badge { margin: 10px auto; width: fit-content; }
        }
    </style>
</head>
<body>

<!-- FB MESSENGER LIVE CHAT -->
<a href="https://www.facebook.com/share/18ZHkBLsvR/?mibextid=wwXIfr" class="live-chat-btn" target="_blank" title="Chat with us!">
    <i class="fab fa-facebook-messenger"></i>
</a>

<!-- TOAST NOTIFICATION FOR DESKTOP & MOBILE -->
<div id="toastNotif" class="toast-notif">
    <i class="fas fa-check-circle" style="color: #d4af37; margin-right: 8px;"></i>
    <span id="toastMsg">Item added to cart!</span>
</div>

<header>
    <nav class="navbar">
        <a href="index.php" class="logo">TINAS JEWELRIES</a>

        <!-- MOBILE RIGHT ACTION BUTTONS -->
        <div class="nav-right-controls">
            <a href="cart.php" class="cart-icon-btn">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge cart-count-val"><?php echo $cart_count; ?></span>
            </a>

            <div class="mobile-menu-btn" onclick="toggleNav()">
                <i class="fas fa-bars"></i>
            </div>
        </div>

        <!-- MAIN NAVIGATION (DESKTOP & MOBILE DROPDOWN) -->
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a></li>
            <li><a href="product.php" class="<?php echo $current_page == 'product.php' ? 'active' : ''; ?>">Product</a></li>
            <li><a href="about.html" class="<?php echo $current_page == 'about.html' ? 'active' : ''; ?>">About</a></li>
            <li><a href="contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
            <li><a href="track_order.php" class="<?php echo $current_page == 'track_order.php' ? 'active' : ''; ?>">Track Order</a></li>
            
            <!-- DESKTOP CART WITH MINI POPUP -->
            <li class="cart-nav-item">
                <a href="cart.php" id="cart-btn-desktop" class="<?php echo $current_page == 'cart.php' ? 'active' : ''; ?>" onclick="toggleMiniCart(event)">
                    <i class="fas fa-shopping-cart"></i> Cart 
                    <span class="cart-badge cart-count-val"><?php echo $cart_count; ?></span>
                </a>

                <!-- MINI CART POPUP -->
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
                            <p class="empty-msg" style="text-align: center; font-size: 12px; color: #888;">Your cart is empty</p>
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
                <li class="user-nav-badge">
                    <span>Hi, <?php echo htmlspecialchars(strtoupper($current_user)); ?>!</span>
                    <a href="admin/logout.php" class="logout-link">Logout</a>
                </li>
            <?php else: ?>
                <li><a href="login.php" class="btn-login-nav" style="font-weight: 600;">Login/Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<section class="hero">
    <div class="hero-image">
        <img src="image/logo.png.jpg" alt="Tina's Jewelries Gold Trading Logo">
    </div>
    <div class="hero-text">
        <?php if ($isLoggedIn): ?>
            <span style="color: #d4af37; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; font-size: 13px;">Member Dashboard</span>
            <h1>Welcome back, <?php echo htmlspecialchars($current_user); ?>!</h1>
        <?php else: ?>
            <span style="color: #d4af37; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; font-size: 13px;">Welcome to</span>
            <h1>Tina's Jewelries Gold Trading</h1>
        <?php endif; ?>
        <p>Premium gold for your investment and style.</p>
        <button onclick="location.href='product.php'">Start Shopping</button>
    </div>
</section>

<!-- PRODUCTS SECTION -->
<section class="products-section">
    <h2 class="section-title">Featured Products</h2>
    <div class="product-grid">
        <?php
        $select_products = mysqli_query($conn, "SELECT * FROM `products` LIMIT 6");
        
        if($select_products && mysqli_num_rows($select_products) > 0){
            while($fetch_product = mysqli_fetch_assoc($select_products)){
                $p_name  = $fetch_product['item_name'];
                $p_price = $fetch_product['price'];
                $p_image = trim($fetch_product['image_path']);
        ?>
        <div class="product-card">
            <img src="uploads/<?php echo htmlspecialchars($p_image); ?>" 
                 alt="<?php echo htmlspecialchars($p_name); ?>"
                 onerror="this.onerror=null; this.src='image/<?php echo htmlspecialchars($p_image); ?>';">
                 
            <h3><?php echo htmlspecialchars($p_name); ?></h3>
            <div class="price">₱<?php echo number_format((float)$p_price, 2); ?></div>
            
            <form onsubmit="addToCartAjax(event, this)">
                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($p_name); ?>">
                <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($p_price); ?>">
                <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($p_image); ?>">
                <button type="submit" name="add_to_cart" class="btn-add-cart">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
            </form>
        </div>
        <?php
            }
        } else {
            echo '<p style="grid-column: 1/-1; color: #666; text-align: center;">No products available at the moment.</p>';
        }
        ?>
    </div>
</section>

<footer>
    <div class="footer-content">
        <div class="footer-section">
            <h3>Tina's Jewelries Gold Trading</h3>
            <p>Your trusted source for premium gold and timeless investments.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="product.php" style="color: #bbb; text-decoration: none;">New Arrivals</a></li>
                <li><a href="track_order.php" style="color: #bbb; text-decoration: none;">Track Order</a></li>
                <li><a href="contact.php" style="color: #bbb; text-decoration: none;">Support</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Us</h3>
            <p><i class="fas fa-envelope"></i> lyr216@gmail.com</p>
            <p><i class="fas fa-phone"></i> 09477377683</p>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?php echo date("Y"); ?> Tina's Jewelries Gold Trading. All Rights Reserved.
    </div>
</footer>

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
        const cartBtn = document.getElementById('cart-btn-desktop');
        if (miniCart && miniCart.classList.contains('active')) {
            if (!miniCart.contains(e.target) && !cartBtn.contains(e.target)) {
                miniCart.classList.remove('active');
            }
        }
    });

    function showToast(msg) {
        const toast = document.getElementById('toastNotif');
        document.getElementById('toastMsg').innerText = msg;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    function addToCartAjax(event, formElement) {
        event.preventDefault();

        const formData = new FormData(formElement);
        formData.append('add_to_cart', '1');

        fetch('cart.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Show notification Toast Alert
                showToast("Item successfully added to cart!");

                // Update Badges Count sa Desktop at Mobile
                const badges = document.querySelectorAll('.cart-count-val');
                badges.forEach(b => b.innerText = data.cart_count);

                // Update Mini Cart Total
                document.getElementById('mini-cart-total').innerText = '₱' + data.grand_total;

                // Re-render HTML items sa mini cart
                let cartItemsContainer = document.getElementById('mini-cart-items');
                cartItemsContainer.innerHTML = '';

                data.cart_items.forEach(item => {
                    let newItem = document.createElement('div');
                    newItem.className = 'mini-cart-item';
                    newItem.innerHTML = `
                        <img src="uploads/${item.image}" onerror="this.onerror=null; this.src='image/${item.image}';" alt="${item.name}">
                        <div class="item-details">
                            <h5>${item.name}</h5>
                            <p>₱${parseFloat(item.price).toLocaleString('en-US', {minimumFractionDigits: 2})} x ${item.quantity}</p>
                        </div>
                    `;
                    cartItemsContainer.appendChild(newItem);
                });
            }
        })
        .catch(err => console.error('Error adding to cart:', err));
    }
</script>
</body>
</html>