<?php
session_start();
include('admin/db_conn.php');

$query = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Jewelry - Tina's Gold Trading</title>
    <link rel="stylesheet" href="index.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            padding: 50px 20px;
            max-width: 1200px;
            margin: auto;
        }
        .product-card {
            background: white;
            padding: 0;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            text-align: center;
            overflow: hidden;
            transition: 0.3s ease;
        }
        .product-card:hover { transform: translateY(-10px); }
        .product-card img { 
            width: 100%; 
            height: 350px; 
            object-fit: cover; 
        }
        .product-info { padding: 20px; }
        .product-card h3 { font-size: 1.2rem; margin-bottom: 8px; color: #1a1a1a; text-transform: capitalize; font-family: 'Poppins', sans-serif; }
        .product-card p { color: #d4af37; font-weight: bold; font-size: 1.3rem; margin-bottom: 20px; }
        
        .add-to-cart {
            background: #1a1a1a; color: #d4af37; border: none;
            padding: 15px; width: 100%; cursor: pointer; font-weight: 600;
            text-transform: uppercase; letter-spacing: 1px;
            transition: 0.3s;
        }
        .add-to-cart:hover { background: #d4af37; color: #1a1a1a; }
    </style>
</head>
<body>

<header>
    <nav class="navbar">
        <ul class="nav-left">
            <li><a href="index.php">Home</a></li>
            <li><a href="product.php" class="active">Product</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
        </ul>
        
        <div class="logo">Tina's Jewelry</div>
        
        <ul class="nav-right">
            <li><a href="cart.php">🛒 Cart</a></li>
            <?php if(isset($_SESSION['username'])): ?>
                <li><a href="#" style="color: #d4af37;">Hi, <?php echo htmlspecialchars(strtoupper($_SESSION['username'])); ?>!</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.html">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div style="text-align: center; padding-top: 80px; background-color: #fffaf5; padding-bottom: 40px;">
    <h1 style="font-family: 'Playfair Display'; font-size: 3.5rem; margin-bottom: 10px; color: #1a1a1a;">Our Jewelry Collection</h1>
    <p style="font-family: 'Poppins'; color: #666; margin-bottom: 20px;">Quality gold pieces for your investment and style.</p>
    <a href="cart.php" style="color:#d4af37; text-decoration:none; font-weight:bold; border-bottom:2px solid #d4af37; letter-spacing: 2px;">VIEW MY CART 🛒</a>
</div>

<div class="product-grid">
    <?php 
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) { 
            $name  = $row['item_name']; 
            $price = (float)$row['price']; // Siniguradong number
            $image = $row['image_path'];
    ?>
        <div class="product-card">
            <img src="uploads/<?php echo $image; ?>" onerror="this.src='img/placeholder.png'">
            <div class="product-info">
                <h3><?php echo htmlspecialchars($name); ?></h3>
                <p>₱<?php echo number_format($price, 2); ?></p>
                
                <button class="add-to-cart" onclick="addToCart('<?php echo addslashes($name); ?>', <?php echo $price; ?>, '<?php echo $image; ?>')">
                    Add to Cart
                </button>
            </div>
        </div>
    <?php 
        } 
    } else {
        echo "<p style='grid-column: 1/-1; text-align: center; color: #888;'>No products available at the moment.</p>";
    }
    ?>
</div>

<script src="script.js"></script>

</body>
</html>