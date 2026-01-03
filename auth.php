<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: /sksu_voting/login.php");
    exit();
}
?>
