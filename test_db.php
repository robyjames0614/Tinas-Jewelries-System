<?php
include('admin/db_conn.php');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "<h1>Connected successfully to database!</h1>";

$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);

if ($result) {
    $row_count = mysqli_num_rows($result);
    echo "Mayroong <b>" . $row_count . "</b> products sa table mo.<br><br>";
    
    if ($row_count > 0) {
        echo "<b>Listahan ng Columns sa table mo:</b><br>";
        $fields = mysqli_fetch_fields($result);
        foreach ($fields as $field) {
            echo "- " . $field->name . "<br>";
        }
    } else {
        echo "<b>Babala:</b> Walang laman ang 'products' table mo. Mag-add ka muna sa admin panel.";
    }
} else {
    echo "Error sa query: " . mysqli_error($conn);
}
?>