<?php
// Include ang database connection
include('../db_conn.php'); 

// Query para makuha ang lahat ng users
$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
</head>
<body>
    <h1>User List</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['username']; ?></td>
            <td>
                <!-- Dito papunta ang link para sa delete/edit -->
                <a href="edit_user.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Sigurado ka ba?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>