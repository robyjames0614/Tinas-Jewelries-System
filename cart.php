<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - Tina's Jewelries</title>
    <link rel="stylesheet" href="index.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fffaf5; color: #1a1a1a; margin: 0; }
        .cart-container { max-width: 1000px; margin: 50px auto; padding: 20px; }
        .cart-header { font-family: 'Playfair Display', serif; font-size: 2.5rem; border-bottom: 1px solid #d4af37; padding-bottom: 20px; margin-bottom: 30px; text-align: center; }

        .cart-table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 5px 20px rgba(0,0,0,0.03); }
        .cart-table th { text-align: left; padding: 20px; background-color: #1a1a1a; color: #d4af37; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
        .cart-table td { padding: 20px; border-bottom: 1px solid #eee; }

        .product-info { display: flex; align-items: center; gap: 20px; }
        .product-info img { width: 80px; height: 80px; object-fit: cover; border: 1px solid #eee; }
        .product-name { font-weight: 600; font-size: 1rem; }

        .qty-input { width: 50px; padding: 5px; text-align: center; border: 1px solid #ddd; }
        .remove-btn { color: #ff4d4d; cursor: pointer; text-decoration: none; font-size: 0.8rem; font-weight: 600; }

        .cart-summary { margin-top: 40px; background: #fff; padding: 30px; width: 100%; max-width: 400px; margin-left: auto; border: 1px solid #d4af37; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .total-price { font-size: 1.5rem; font-weight: 600; color: #d4af37; }

        .checkout-btn { display: block; width: 100%; padding: 15px; background-color: #1a1a1a; color: #fff; text-align: center; text-decoration: none; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; transition: 0.3s; border: 1px solid #1a1a1a; cursor: pointer; }
        .checkout-btn:hover { background-color: transparent; color: #1a1a1a; }
        
        /* Modal Enhancements */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); backdrop-filter: blur(5px); overflow-y: auto; }
        .modal-content { background-color: #fff; margin: 2% auto; padding: 30px; width: 90%; max-width: 450px; border-radius: 8px; border: 1px solid #d4af37; position: relative; max-height: 90vh; overflow-y: auto; }
        .modal-content input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }

        .payment-status-tag { background: #fdfcf0; border: 1px solid #d4af37; padding: 15px; border-radius: 8px; margin-top: 15px; text-align: center; }
        .qr-img { width: 220px; height: auto; margin: 15px 0; border: 4px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        
        /* Safe Preview Styling */
        .receipt-upload-box { margin-top: 20px; padding: 15px; border: 2px dashed #d4af37; border-radius: 8px; background: #fafafa; text-align: center; }
        #previewContainer { margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px; }
        #imagePreview { max-width: 150px; border-radius: 5px; border: 1px solid #d4af37; display: none; margin: 10px auto; }

        .copy-badge { background: #d4af37; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.65rem; cursor: pointer; border: none; margin-left: 5px; vertical-align: middle; }
        .copy-badge:active { background: #1a1a1a; }

        .confirm-btn { background-color: #d4af37; color: #fff; width: 100%; padding: 15px; border: none; font-weight: 600; cursor: pointer; margin-top: 20px; text-transform: uppercase; transition: 0.3s; }
        .confirm-btn:hover { background-color: #1a1a1a; }
        .confirm-btn:disabled { background-color: #ccc; cursor: not-allowed; }
        
        .close-btn { position: absolute; right: 20px; top: 10px; font-size: 28px; cursor: pointer; color: #888; }
        .success-header { color: #28a745; margin-bottom: 10px; }
    </style>
</head>
<body>

<header>
    <nav class="navbar">
        <ul class="nav-left">
            <li><a href="index.php">Home</a></li>
            <li><a href="product.php">Product</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
        </ul>
        <ul class="nav-right">
            <li><a href="cart.php" class="active">🛒 Cart</a></li>
        </ul>
    </nav>
</header>

<div class="cart-container">
    <h1 class="cart-header">Your Selection</h1>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="cart-summary">
        <div class="summary-row"><span>Subtotal</span><span id="subtotal-val">₱0</span></div>
        <div class="summary-row"><span>Shipping</span><span>FREE</span></div>
        <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 15px;">
        <div class="summary-row"><span style="font-weight: 600;">ESTIMATED TOTAL</span><span class="total-price">₱0</span></div>
        <button class="checkout-btn">Proceed to Checkout</button>
    </div>
    <a href="product.php" style="text-decoration:none; color:#666; margin-top:20px; display:inline-block;">← Continue Shopping</a>
</div>

<div id="checkoutModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2 style="font-family:'Playfair Display'; margin-bottom: 5px;">Checkout Details</h2>
        <p style="font-size: 0.8rem; color: #888; margin-bottom: 20px;">Sundin ang steps sa ibaba para ma-confirm ang order.</p>
        
        <form id="orderForm" onsubmit="placeOrder(event)">
            <input type="text" id="custName" name="fullname" placeholder="Full Name" required>
            <input type="text" id="custAddress" name="address" placeholder="Delivery Address" required>
            <input type="tel" id="custPhone" name="phone" placeholder="Phone Number (09XXXXXXXXX)" required>
            
            <input type="hidden" name="payment_method" value="GCash">

            <div class="payment-status-tag">
                <p style="font-size: 0.8rem; font-weight: 600; color: #d4af37; margin-bottom: 10px;">STEP 1: SEND PAYMENT TO GCASH</p>
                <img src="img/gcash-qr.png" alt="GCash QR Code" class="qr-img">
                <p style="font-size: 0.85rem; color: #1a1a1a; margin: 5px 0;">
                    <b>Account:</b> ROBY JAMES B.<br>
                    <b>Number:</b> <span id="gcashNum">09552679761</span> 
                    <button type="button" class="copy-badge" onclick="copyGcashNumber()">COPY</button>
                </p>
                <p style="font-size: 0.85rem; background: #fff; padding: 5px; border-radius: 4px; display: inline-block; margin-top: 5px;">
                    Total to Pay: <b class="total-price" style="color: #d4af37;">₱0</b>
                </p>
            </div>

            <div class="receipt-upload-box">
                <p style="font-size: 0.8rem; font-weight: 600; color: #d4af37; margin-bottom: 10px;">STEP 2: UPLOAD RECEIPT</p>
                <label style="font-size: 0.7rem; color: #666; display: block; margin-bottom: 10px;">Pumili ng screenshot ng matagumpay na transaction.</label>
                <input type="file" id="receiptFile" name="receipt_img" accept="image/*" required>
                
                <div id="previewContainer">
                    <img id="imagePreview" src="#" alt="Receipt Preview">
                </div>
            </div>

            <button type="submit" class="confirm-btn">Confirm Order & Pay</button>
        </form>
    </div>
</div>

<div id="thankYouModal" class="modal">
    <div class="modal-content" style="text-align: center; border-color: #28a745;">
        <h2 class="success-header">✔ Order Received!</h2>
        <p>Salamat! Ang iyong bayad ay kasalukuyang vine-verify.</p>
        <div style="background: #fdf8e4; padding: 15px; border-radius: 8px; text-align: left; margin: 20px 0; font-size: 0.9rem; border-left: 5px solid #d4af37;">
            <p><b>🛡️ Verification Process:</b></p>
            <ul>
                <li>I-verify namin ang receipt image na sinend mo.</li>
                <li>Huwag i-delete ang screenshot ng iyong transaction.</li>
                <li>Wait for our confirmation call (09552679761).</li>
            </ul>
        </div>
        <button onclick="closeThankYou()" class="confirm-btn" style="background-color: #1a1a1a;">Done & Return Home</button>
    </div>
</div>

<script src="cart.js"></script>

<script>
    // Copy Function
    function copyGcashNumber() {
        const num = document.getElementById('gcashNum').innerText;
        navigator.clipboard.writeText(num).then(() => {
            alert("GCash Number copied: " + num);
        });
    }

    // Receipt Image Preview
    document.getElementById('receiptFile').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            const previewImage = document.getElementById('imagePreview');

            reader.addEventListener('load', function() {
                previewImage.setAttribute('src', this.result);
                previewImage.style.display = 'block';
            });
            reader.readAsDataURL(file);
        }
    });
</script>

</body>
</html>