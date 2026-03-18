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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #fafafa; margin: 0; overflow-x: hidden; }
        
        /* --- NAVIGATION (HAMBURGER STYLE) --- */
        .navbar { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 0 5%; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky; top: 0; z-index: 1000; height: 70px;
        }
        
        .logo { font-family: 'Playfair Display'; font-weight: bold; font-size: 24px; color: #1a1a1a; }

        .nav-links { 
            display: flex; list-style: none; gap: 25px; align-items: center; margin: 0; padding: 0; 
        }
        .nav-links a { text-decoration: none; color: #333; font-weight: 500; transition: 0.3s; }
        .nav-links a.active { color: #d4af37; font-weight: bold; }

        .mobile-menu-btn { 
            display: none; font-size: 24px; cursor: pointer; color: #1a1a1a; 
        }

        /* --- PRODUCT GRID --- */
        .product-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px; padding: 40px 5%; max-width: 1200px; margin: auto;
        }

        .product-card {
            background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center; overflow: hidden; transition: 0.3s ease; display: flex; flex-direction: column;
        }

        .product-card:hover { transform: translateY(-8px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .product-card img { width: 100%; height: 280px; object-fit: cover; }

        .product-info { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .product-card h3 { font-size: 1.1rem; margin-bottom: 10px; color: #1a1a1a; }
        .product-card p { color: #d4af37; font-weight: 600; font-size: 1.3rem; margin-bottom: 20px; }
        
        .add-to-cart {
            background: #1a1a1a; color: #d4af37; border: none; padding: 12px;
            width: 100%; cursor: pointer; font-weight: 600; text-transform: uppercase;
            border-radius: 8px; transition: 0.3s;
        }
        .add-to-cart:hover { background: #d4af37; color: #1a1a1a; }

        /* --- MOBILE RESPONSIVE --- */
        @media (max-width: 768px) {
            .mobile-menu-btn { display: block; }

            .nav-links { 
                display: none; flex-direction: column; position: absolute; 
                top: 70px; left: 0; width: 100%; background: #fff; 
                padding: 20px 0; box-shadow: 0 10px 20px rgba(0,0,0,0.1); gap: 0;
            }

            .nav-links.active { display: flex !important; }

            .nav-links li { width: 100%; text-align: center; padding: 15px 0; border-bottom: 1px solid #f9f9f9; }

            .product-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; padding: 20px 10px; }
            .product-card img { height: 180px; }
            .product-card h3 { font-size: 0.9rem; }
            .product-card p { font-size: 1rem; }
            h1 { font-size: 2.2rem !important; }
        }
    </style>
</head>
<body>

<header>
    <nav class="navbar">
        <div class="logo">TINAS JEWELRIES GOLD TRADING</div>
        
        <div class="mobile-menu-btn" onclick="toggleNav()">
            <i class="fas fa-bars"></i>
        </div>

        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Home</a></li>
            <li><a href="product.php" class="active">Product</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="track_order.php">Track Order</a></li>
            <li><a href="cart.php" style="color: #d4af37;"><i class="fas fa-shopping-cart"></i> Cart</a></li>
            
            <?php if(isset($_SESSION['username'])): ?>
                <li style="background: #fafafa; padding: 10px;">
                    <span style="font-size: 13px; color: #666;">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="admin/logout.php" style="color: #ff4d4d; margin-left: 10px; font-size: 13px;">Logout</a>
                </li>
            <?php else: ?>
                <li><a href="login.html">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div style="text-align: center; padding: 60px 20px; background-color: #fffaf5;">
    <h1 style="font-family: 'Playfair Display'; font-size: 3.5rem; margin: 0; color: #1a1a1a;">Our Collection</h1>
    <p style="color: #666; margin: 10px 0 20px;">Premium gold for your investment and style.</p>
</div>

<div class="product-grid">
    <?php 
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) { 
            $name  = $row['item_name']; 
            $price = (float)$row['price'];
            $image = $row['image_path'];
    ?>
        <div class="product-card">
            <img src="uploads/<?php echo $image; ?>" onerror="this.src='img/placeholder.png'">
            <div class="product-info">
                <div>
                    <h3><?php echo htmlspecialchars($name); ?></h3>
                    <p>₱<?php echo number_format($price, 2); ?></p>
                </div>
                <button class="add-to-cart" onclick="addToCart('<?php echo addslashes($name); ?>', <?php echo $price; ?>, '<?php echo $image; ?>')">
                    Add to Cart
                </button>
            </div>
        </div>
    <?php 
        } 
    } else {
        echo "<p style='grid-column: 1/-1; text-align: center; color: #888;'>No products available yet.</p>";
    }
    ?>
</div>

<script>
    // Toggle Menu Function
    function toggleNav() {
        const navLinks = document.getElementById('navLinks');
        navLinks.classList.toggle('active');
    }
</script>
<script src="script.js"></script>

</body>
</html>