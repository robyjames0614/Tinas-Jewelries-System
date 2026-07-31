<?php
session_start();
include('db_conn.php');

if (!isset($_SESSION['admin_id'])) {
    echo "Please login first.";
    exit();
}

$my_id = $_SESSION['admin_id']; 
$receiver_id = 1; // Default: Kausap ay Admin (ID 1)

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['message'])) {
    $msg = mysqli_real_escape_string($conn, $_POST['message']);
    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES ('$my_id', '$receiver_id', '$msg')";
    mysqli_query($conn, $sql);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat Support</title>
    <style>
        .chat-container { width: 350px; margin: 20px auto; border: 1px solid #d4af37; padding: 15px; border-radius: 8px; background: #1a1a1a; color: #fff; }
        #chat-box { height: 250px; overflow-y: scroll; border: 1px solid #444; margin-bottom: 10px; padding: 10px; }
        .msg { margin-bottom: 8px; padding: 5px; border-bottom: 1px solid #333; }
        input { width: 70%; padding: 8px; }
        button { padding: 8px; background: #d4af37; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="chat-container">
        <h3>Live Support</h3>
        <div id="chat-box">
            <?php
            $query = "SELECT * FROM messages ORDER BY created_at ASC";
            $result = mysqli_query($conn, $query);
            while($row = mysqli_fetch_assoc($result)) {
                echo "<div class='msg'>User " . $row['sender_id'] . ": " . htmlspecialchars($row['message']) . "</div>";
            }
            ?>
        </div>
        <form method="POST">
            <input type="text" name="message" required>
            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>