<?php
session_start();
include('../db_conn.php');

// Security check
if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

// Kunin ang lahat ng unique na customers base sa pangalan at phone
$sql = "SELECT fullname, phone, address, COUNT(id) as total_orders, SUM(total_amount) as total_spent 
        FROM orders 
        GROUP BY fullname, phone 
        ORDER BY fullname ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Directory - Tina's Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; display: flex; min-height: 100vh; }

        /* --- MOBILE HEADER (NEW) --- */
        .mobile-header {
            display: none;
            background: #1a1a1a;
            color: white;
            padding: 15px 20px;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1001;
        }
        .menu-btn {
            background: #d4af37;
            border: none;
            color: #1a1a1a;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
        }

        /* --- MAIN CONTENT --- */
        .main-content { 
            flex: 1; 
            margin-left: 260px; 
            padding: 40px; 
            transition: 0.3s;
        }

        .admin-container { 
            background: white; 
            padding: 35px; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
        }
        
        /* Table Responsiveness */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            margin-top: 25px;
        }

        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th { background: #1a1a1a; color: #d4af37; padding: 15px; text-align: left; font-size: 12px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .customer-icon { background: #f1f1f1; padding: 10px; border-radius: 50%; color: #d4af37; margin-right: 10px; }
        .stats-badge { background: #d4af37; color: #1a1a1a; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; }
        .total-spent { color: #27ae60; font-weight: 600; white-space: nowrap; }

        /* Mobile View Adjustments */
        @media (max-width: 992px) {
            .mobile-header { display: flex; }
            .main-content { 
                margin-left: 0; 
                width: 100%; 
                padding: 100px 20px 20px; 
            }
            .admin-container { padding: 20px; }
            .admin-container h2 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>

    <div class="mobile-header">
        <span style="font-weight: 700; color: #d4af37; letter-spacing: 1px;">TINA'S ADMIN</span>
        <button class="menu-btn" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <?php include('sidebar.php'); ?>

    <div class="main-content">
        <div class="admin-container">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                <i class="fas fa-users" style="font-size: 2rem; color: #1a1a1a;"></i>
                <h2>Customer Directory</h2>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Orders Made</th>
                            <th>Total Spent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-user customer-icon"></i>
                                    <strong><?php echo htmlspecialchars($row['fullname']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><small style="color: #666;"><?php echo htmlspecialchars($row['address']); ?></small></td>
                                <td><span class="stats-badge"><?php echo $row['total_orders']; ?> Order(s)</span></td>
                                <td><span class="total-spent">₱<?php echo number_format($row['total_spent'], 2); ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding: 30px; color: #888;">No customers found yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            // Ito ay tatawag sa function na dapat ay nasa loob ng iyong sidebar.php 
            // o direktang i-toggle ang sidebar class
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>

</body>
</html>