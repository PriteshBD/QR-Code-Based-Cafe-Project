<?php
session_start();
unset($_SESSION['cart']);
header("Location: menu.php");
exit();
?>