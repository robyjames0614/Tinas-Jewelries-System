<?php
include('../db_conn.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $delete_query = "DELETE FROM inquiries WHERE id = $id";
    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('Message deleted!'); window.location.href='view_inquiries.php';</script>";
    }
}

$pending_count_query = mysqli_query($conn, "SELECT id FROM orders WHERE status='Pending'");
$pending_count = ($pending_count_query) ? mysqli_num_rows($pending_count_query) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries | Tina's Jewelries Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7f6; display: flex; min-height: 100vh; }

        /* --- MOBILE HEADER --- */
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        .container { 
            background: #fff; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); 
        }

        h2 { 
            color: #111; 
            border-bottom: 2px solid #d4af37; 
            padding-bottom: 15px; 
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Desktop Table Style */
        .inquiry-table { width: 100%; border-collapse: collapse; }
        .inquiry-table th, .inquiry-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .inquiry-table th { background: #fdfdfd; color: #888; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; }
        
        .btn-delete { color: #e74c3c; cursor: pointer; text-decoration: none; font-size: 1.1rem; transition: 0.2s; }
        .btn-delete:hover { color: #c0392b; transform: scale(1.1); }
        
        /* --- MOBILE RESPONSIVE LOGIC --- */
        @media (max-width: 992px) {
            .mobile-header { display: flex; }
            .main-content { 
                margin-left: 0; 
                width: 100%; 
                padding: 90px 15px 20px; 
            }
            
            /* Ginagawang Cards ang Table Rows */
            .inquiry-table thead { display: none; } /* Itatago ang headers */
            
            .inquiry-table, .inquiry-table tbody, .inquiry-table tr, .inquiry-table td { 
                display: block; 
                width: 100%; 
            }

            .inquiry-table tr {
                margin-bottom: 20px;
                border: 1px solid #eee;
                border-radius: 12px;
                padding: 10px;
                background: #fff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            }

            .inquiry-table td {
                text-align: right;
                padding: 10px 15px;
                position: relative;
                border-bottom: 1px solid #f9f9f9;
            }

            .inquiry-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: 45%;
                text-align: left;
                font-weight: 600;
                font-size: 0.8rem;
                color: #d4af37;
                text-transform: uppercase;
            }

            .inquiry-table td:last-child { border-bottom: none; }
            
            /* Adjustments para sa long messages */
            .inquiry-table td[data-label="Message"] {
                text-align: left;
                padding-top: 35px;
            }
            .inquiry-table td[data-label="Message"]::before {
                top: 10px;
            }
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
        <div class="container">
            <h2><i class="fa fa-envelope"></i> Customer Inquiries</h2>
            
            <div class="table-responsive">
                <table class="inquiry-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Date Received</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM inquiries ORDER BY submitted_at DESC";
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td data-label="Name"><strong><?php echo htmlspecialchars($row['fullname']); ?></strong></td>
                                <td data-label="Email"><a href="mailto:<?php echo $row['email']; ?>" style="color: #3498db; text-decoration: none;"><?php echo htmlspecialchars($row['email']); ?></a></td>
                                <td data-label="Message" style="color: #555; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                                <td data-label="Date"><?php echo date('M d, Y | h:i A', strtotime($row['submitted_at'])); ?></td>
                                <td data-label="Action">
                                    <a href='view_inquiries.php?delete_id=<?php echo $row['id']; ?>' 
                                       class='btn-delete' 
                                       onclick="return confirm('Sigurado ka bang buburahin ang mensaheng ito?')">
                                         <i class='fa fa-trash'></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='no-data'>Walang bagong inquiries sa ngayon.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            if(sidebar) {
                sidebar.classList.toggle('active');
            }
        }
    </script>

</body>
</html>