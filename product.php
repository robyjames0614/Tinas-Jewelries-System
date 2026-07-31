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

// FILTER LOGIC
if (isset($_GET['type'])) {
    $type = mysqli_real_escape_string($conn, $_GET['type']);
    if ($type == 'ring') {
        $query = "SELECT id, item_name, price, image_path, description, available_sizes 
                FROM products 
                WHERE item_name LIKE '%ring%' 
                AND item_name NOT LIKE '%earring%' 
                ORDER BY id DESC";
    } else {
        $query = "SELECT id, item_name, price, image_path, description, available_sizes 
                FROM products 
                WHERE item_name LIKE '%$type%' 
                ORDER BY id DESC";
    }
} else {
    $query = "SELECT id, item_name, price, image_path, description, available_sizes FROM products ORDER BY id DESC";
}
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Jewelry - Tina's Gold Trading</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="product.css">

    <style>
        /* TOAST NOTIFICATION POPUP */
        .toast-notif {
            visibility: hidden; min-width: 280px; background-color: #1a1a1a; color: #fff;
            text-align: center; border-radius: 8px; padding: 12px 20px; position: fixed;
            z-index: 9999; right: 30px; bottom: 30px; font-size: 13px; border-left: 4px solid #d4af37;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2); opacity: 0; transition: opacity 0.3s, bottom 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        .toast-notif.show { visibility: visible; opacity: 1; bottom: 40px; }
    </style>
</head>
<body>

<!-- TOAST NOTIFICATION FOR DESKTOP & MOBILE -->
<div id="toastNotif" class="toast-notif">
    <i class="fas fa-check-circle" style="color: #d4af37; margin-right: 8px;"></i>
    <span id="toastMsg">Item added to cart!</span>
</div>

<div id="productModal" class="modal">
    <div class="modal-container">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <div class="modal-left"><img id="modalImg" src=""></div>
        <div class="modal-right">
            <h2 id="modalTitle"></h2>
            <div id="modalPriceContainer"></div> 
            <div id="modalDesc"></div>
            <div id="modalBtnContainer"></div>
        </div>
    </div>
</div>

<header>
    <nav class="navbar">
        <a href="index.php" class="logo" style="text-decoration:none; color:inherit;">TINAS JEWELRIES</a>

        <!-- MOBILE CONTROLS (CART ICON & HAMBURGER) -->
        <div class="nav-right-controls" style="position: relative;">
            <a href="cart.php" class="cart-icon-btn" id="cart-btn-mobile" onclick="toggleMiniCart(event)">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge cart-count-val"><?php echo $cart_count; ?></span>
            </a>

            <!-- MOBILE MINI CART POPUP -->
            <div id="mini-cart-popup-mobile" class="mini-cart">
                <div class="mini-cart-header">
                    <h4>Shopping Cart</h4>
                    <span class="close-mini-cart" onclick="closeMiniCart()">&times;</span>
                </div>
                <div id="mini-cart-items-mobile" class="mini-cart-body">
                    <?php if (!empty($cart_items_db)): ?>
                        <?php foreach ($cart_items_db as $item): 
                            $p_price = floatval($item['price']);
                            $qty = intval($item['quantity']);
                            $size_label = !empty($item['selected_size']) ? ' (Size ' . htmlspecialchars($item['selected_size']) . ')' : '';
                        ?>
                            <div class="mini-cart-item">
                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" onerror="this.onerror=null; this.src='image/<?php echo htmlspecialchars($item['image']); ?>';" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="item-details">
                                    <h5><?php echo htmlspecialchars($item['name']) . $size_label; ?></h5>
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
                        <strong class="mini-cart-total-val">₱<?php echo number_format($grand_total, 2); ?></strong>
                    </div>
                    <a href="cart.php" class="btn-view-cart">View Cart</a>
                    <a href="checkout.php" class="btn-checkout">Checkout</a>
                </div>
            </div>

            <div class="mobile-menu-btn" onclick="toggleNav()">
                <i class="fas fa-bars"></i>
            </div>
        </div>

        <!-- DESKTOP NAVIGATION -->
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Home</a></li>
            <li><a href="product.php" class="active">Product</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="track_order.php">Track Order</a></li>
            
            <!-- DESKTOP CART WITH MINI POPUP -->
            <li class="cart-nav-item" style="position: relative;">
                <a href="cart.php" id="cart-btn-desktop" onclick="toggleMiniCart(event)">
                    <i class="fas fa-shopping-cart"></i> Cart 
                    <span class="cart-badge cart-count-val"><?php echo $cart_count; ?></span>
                </a>

                <!-- DESKTOP MINI CART POPUP -->
                <div id="mini-cart-popup-desktop" class="mini-cart">
                    <div class="mini-cart-header">
                        <h4>Shopping Cart</h4>
                        <span class="close-mini-cart" onclick="closeMiniCart()">&times;</span>
                    </div>
                    <div id="mini-cart-items-desktop" class="mini-cart-body">
                        <?php if (!empty($cart_items_db)): ?>
                            <?php foreach ($cart_items_db as $item): 
                                $p_price = floatval($item['price']);
                                $qty = intval($item['quantity']);
                                $size_label = !empty($item['selected_size']) ? ' (Size ' . htmlspecialchars($item['selected_size']) . ')' : '';
                            ?>
                                <div class="mini-cart-item">
                                    <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" onerror="this.onerror=null; this.src='image/<?php echo htmlspecialchars($item['image']); ?>';" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <div class="item-details">
                                        <h5><?php echo htmlspecialchars($item['name']) . $size_label; ?></h5>
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
                            <strong class="mini-cart-total-val">₱<?php echo number_format($grand_total, 2); ?></strong>
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

<!-- CATEGORY FILTER BUTTONS -->
<div class="category-filter">
    <a href="product.php" class="filter-btn <?php echo !isset($_GET['type']) ? 'active' : ''; ?>">All Products</a>
    <a href="product.php?type=ring" class="filter-btn <?php echo (isset($_GET['type']) && $_GET['type']=='ring') ? 'active' : ''; ?>">Rings</a>
    <a href="product.php?type=earring" class="filter-btn <?php echo (isset($_GET['type']) && $_GET['type']=='earring') ? 'active' : ''; ?>">Earrings</a>
    <a href="product.php?type=necklace" class="filter-btn <?php echo (isset($_GET['type']) && $_GET['type']=='necklace') ? 'active' : ''; ?>">Necklaces</a>
    <a href="product.php?type=bracelet" class="filter-btn <?php echo (isset($_GET['type']) && $_GET['type']=='bracelet') ? 'active' : ''; ?>">Bracelets</a>
    <a href="product.php?type=anklet" class="filter-btn <?php echo (isset($_GET['type']) && $_GET['type']=='anklet') ? 'active' : ''; ?>">Anklets</a>
</div>

<div class="product-grid">
    <?php 
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) { 
            $imgSrc = "uploads/" . $row['image_path'];
            $lowerName = strtolower($row['item_name']);
            
            // Rings lang ang itinuturing na may Size
            $isRing = (strpos($lowerName, 'ring') !== false && strpos($lowerName, 'earring') === false);
    ?>
        <div class="product-card">
            <img src="<?php echo $imgSrc; ?>" onclick="openQuickView('<?php echo addslashes($row['item_name']); ?>', '<?php echo $imgSrc; ?>', '<?php echo $row['image_path']; ?>', `<?php echo addslashes($row['description']); ?>`, '<?php echo $row['available_sizes']; ?>', <?php echo $row['price']; ?>)" style="cursor:pointer;">
            <div class="product-info">
                <h3><?php echo htmlspecialchars($row['item_name']); ?></h3>
                <p>Starting at ₱<?php echo number_format($row['price'], 2); ?></p>
                
                <?php if ($isRing): ?>
                    <button type="button" class="add-to-cart" onclick="openQuickView('<?php echo addslashes($row['item_name']); ?>', '<?php echo $imgSrc; ?>', '<?php echo $row['image_path']; ?>', `<?php echo addslashes($row['description']); ?>`, '<?php echo $row['available_sizes']; ?>', <?php echo $row['price']; ?>)">SELECT SIZE</button>
                <?php else: ?>
                    <form onsubmit="addToCartAjax(event, this)">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['item_name']); ?>">
                        <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                        <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($row['image_path']); ?>">
                        <input type="hidden" name="selected_size" value="">
                        <button type="submit" name="add_to_cart" class="add-to-cart">ADD TO CART</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php 
        } 
    } else {
        echo '<p style="grid-column: 1/-1; text-align: center; padding: 40px; color: #666; font-size: 1.1rem;">Walang nahanap na alahas sa kategoryang ito.</p>';
    }
    ?>
</div>

<script>
    function toggleNav() { 
        document.getElementById('navLinks').classList.toggle('active'); 
    }

    function toggleMiniCart(e) {
        e.preventDefault();
        const isMobile = window.innerWidth <= 768;
        const miniCart = isMobile 
            ? document.getElementById('mini-cart-popup-mobile') 
            : document.getElementById('mini-cart-popup-desktop');
        
        if (miniCart) {
            miniCart.classList.toggle('active');
        }
    }

    function closeMiniCart() {
        const popups = document.querySelectorAll('.mini-cart');
        popups.forEach(p => p.classList.remove('active'));
    }

    window.addEventListener('click', function(e) {
        const popups = document.querySelectorAll('.mini-cart');
        const cartBtnDesktop = document.getElementById('cart-btn-desktop');
        const cartBtnMobile = document.getElementById('cart-btn-mobile');

        popups.forEach(miniCart => {
            if (miniCart && miniCart.classList.contains('active')) {
                if (!miniCart.contains(e.target) && 
                    (!cartBtnDesktop || !cartBtnDesktop.contains(e.target)) && 
                    (!cartBtnMobile || !cartBtnMobile.contains(e.target))) {
                    miniCart.classList.remove('active');
                }
            }
        });
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
                closeModal();
                
                showToast("Item successfully added to cart!");

                // Update badge numbers (Mobile at Desktop)
                document.querySelectorAll('.cart-count-val').forEach(b => b.innerText = data.cart_count);

                // Update Total prices
                document.querySelectorAll('.mini-cart-total-val').forEach(t => t.innerText = '₱' + data.grand_total);

                // Update items sa parehong containers
                const containers = [
                    document.getElementById('mini-cart-items-desktop'),
                    document.getElementById('mini-cart-items-mobile')
                ];

                containers.forEach(container => {
                    if (container) {
                        container.innerHTML = '';
                        data.cart_items.forEach(item => {
                            let newItem = document.createElement('div');
                            newItem.className = 'mini-cart-item';
                            let sizeText = item.selected_size ? ` (Size ${item.selected_size})` : '';
                            newItem.innerHTML = `
                                <img src="uploads/${item.image}" onerror="this.onerror=null; this.src='image/${item.image}';" alt="${item.name}">
                                <div class="item-details">
                                    <h5>${item.name}${sizeText}</h5>
                                    <p>₱${parseFloat(item.price).toLocaleString('en-US', {minimumFractionDigits: 2})} x ${item.quantity}</p>
                                </div>
                            `;
                            container.appendChild(newItem);
                        });
                    }
                });
            }
        })
        .catch(err => console.error('Error adding to cart:', err));
    }

    function openQuickView(name, imgSrc, imgFile, desc, sizes, basePrice) {
        document.getElementById("modalImg").src = imgSrc;
        document.getElementById("modalTitle").innerText = name;
        document.getElementById("modalDesc").innerText = desc;
        
        let lowerName = name.toLowerCase();
        let isRing = (lowerName.includes('ring') && !lowerName.includes('earring'));

        let options = "";
        let displayPrice = basePrice;
        let sizeSelectorHtml = "";

        if (isRing) {
            if (sizes && sizes.includes(':')) {
                let sizePricePairs = sizes.split(',');
                options = sizePricePairs.map(pair => {
                    let parts = pair.split(':');
                    let s = parts[0] ? parts[0].trim() : "";
                    let p = parts[1] ? parts[1].trim() : basePrice;
                    return `<option value="${p}" data-size="${s}">Size ${s} - ₱${parseFloat(p).toLocaleString(undefined, {minimumFractionDigits: 2})}</option>`;
                }).join('');
                displayPrice = sizePricePairs[0].split(':')[1];
            } else if (sizes && sizes.trim() !== "") {
                let sizeArray = sizes.split(',');
                options = sizeArray.map(s => {
                    return `<option value="${basePrice}" data-size="${s.trim()}">Size ${s.trim()}</option>`;
                }).join('');
            } else {
                options = `<option value="${basePrice}" data-size="Standard">Standard Size</option>`;
            }

            sizeSelectorHtml = `
                <label style="font-size: 14px; font-weight: 600;">Select Ring Size:</label>
                <select id="selectedSize" style="width:100%; padding:10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px;" onchange="updatePrice()">${options}</select>
            `;
        }

        document.getElementById("modalPriceContainer").innerHTML = `<p id="dynamicPrice" style="font-size:1.4rem; color:#d4af37; font-weight:600;">₱${parseFloat(displayPrice).toFixed(2)}</p>`;
        
        document.getElementById("modalBtnContainer").innerHTML = `
            <form onsubmit="submitModalCart(event, '${name}', ${isRing})">
                ${sizeSelectorHtml}
                
                <input type="hidden" name="product_name" id="modalProductName" value="${name}">
                <input type="hidden" name="product_price" id="modalProductPrice" value="${displayPrice}">
                <input type="hidden" name="product_image" value="${imgFile}">
                <input type="hidden" name="selected_size" id="modalSelectedSize" value="">
                
                <button type="submit" name="add_to_cart" class="add-to-cart" style="margin-top: 10px;">Add to Cart</button>
            </form>
        `;
        
        if (isRing) {
            updatePrice();
        }

        document.getElementById("productModal").style.display = "flex";
    }

    function updatePrice() {
        let select = document.getElementById('selectedSize');
        if (select && select.options.length > 0) {
            let val = select.value;
            let size = select.options[select.selectedIndex].getAttribute('data-size');
            
            document.getElementById('dynamicPrice').innerText = "₱" + parseFloat(val).toFixed(2);
            document.getElementById('modalProductPrice').value = val;
            document.getElementById('modalSelectedSize').value = size;
        }
    }

    function submitModalCart(event, baseName, isRing) {
        if (isRing) {
            let select = document.getElementById('selectedSize');
            if (select && select.options.length > 0) {
                let size = select.options[select.selectedIndex].getAttribute('data-size');
                document.getElementById('modalSelectedSize').value = size;
            }
        } else {
            document.getElementById('modalSelectedSize').value = "";
        }
        addToCartAjax(event, event.target);
    }

    function closeModal() { document.getElementById("productModal").style.display = "none"; }
</script>
</body>
</html>