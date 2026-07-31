<?php
session_start();
session_unset();
session_destroy();

// BAGUHIN ITO: Mula login.html gawing login.php
header("Location: ../login.php"); 
exit();
?>