<?php
session_start();

// 1. Siguraduhin na tama ang path. 
// Kung ang contact_process.php ay nasa htdocs, at ang db_conn.php ay nasa loob ng admin folder:
$db_path = 'db_conn.php';

if (file_exists($db_path)) {
    include($db_path);
} else {
    die("Error: Hindi mahanap ang database configuration file.");
}

// 2. Check kung may laman ang POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check kung exist ang database connection
    if (!isset($conn)) {
        die("Error: Walang koneksyon sa database.");
    }

    // Kinukuha ang data mula sa form
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Insert query
    $sql = "INSERT INTO inquiries (fullname, email, message) VALUES ('$fullname', '$email', '$message')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Salamat! Na-receive namin ang iyong mensahe.');
                window.location.href='contact.html';
              </script>";
    } else {
        // I-display ang error para malaman natin kung bakit hindi nag-i-insert
        echo "Database Error: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}
?>