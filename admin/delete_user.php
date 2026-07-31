<?php
// 1. DATABASE CONFIGURATION (InfinityFree)
$servername = "sql207.infinityfree.com"; 
$username = "if0_41887001";              
$password = "rjames0614";   
$dbname = "if0_41887001_tinasdb";        

// 2. Connection Setup
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. PROSESO NG PAG-DELETE
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $user_id = intval($_GET['id']); 

    $query = "DELETE FROM users WHERE id = $user_id";
    
    if ($conn->query($query) === TRUE) {
        // MAHALAGA: Gumagamit tayo ng JavaScript History Back para bumalik ka lang sa huling pinanggalingan mong page,
        // anuman ang pangalan ng file ng iyong listahan ng mga user!
        echo "<script>
                alert('User successfully deleted!'); 
                if(document.referrer) {
                    window.location.href = document.referrer;
                } else {
                    window.location.href = 'index.php';
                }
              </script>";
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}
?>