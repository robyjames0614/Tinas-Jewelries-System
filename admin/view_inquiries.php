<?php
include('db_conn.php'); // Siguraduhin na tama ang path papunta sa db_conn mo
session_start();

// Logic para sa Pag-delete ng Inquiry
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $delete_query = "DELETE FROM inquiries WHERE id = $id";
    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('Message deleted!'); window.location.href='view_inquiries.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries | Tina's Jewelries Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 1100px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { font-family: 'Playfair Display', serif; color: #111; border-bottom: 2px solid #d4af37; padding-bottom: 10px; }
        
        .inquiry-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .inquiry-table th, .inquiry-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .inquiry-table th { background: #111; color: #d4af37; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
        
        .btn-delete { color: #e74c3c; cursor: pointer; text-decoration: none; font-size: 1.2rem; }
        .btn-delete:hover { color: #c0392b; }
        
        .no-data { text-align: center; padding: 20px; color: #888; }
        .badge { background: #d4af37; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fa fa-envelope"></i> Customer Inquiries</h2>
    
    <table class="inquiry-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM inquiries ORDER BY submitted_at DESC";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td><strong>" . $row['fullname'] . "</strong></td>";
                    echo "<td>" . $row['email'] . "</td>";
                    echo "<td>" . $row['message'] . "</td>";
                    echo "<td>" . date('M d, Y', strtotime($row['submitted_at'])) . "</td>";
                    echo "<td>
                            <a href='view_inquiries.php?delete_id=" . $row['id'] . "' 
                               class='btn-delete' 
                               onclick=\"return confirm('Sigurado ka bang buburahin ito?')\">
                               <i class='fa fa-trash'></i>
                            </a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='no-data'>Walang bagong inquiries sa ngayon.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>