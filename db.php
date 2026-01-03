<?php
$conn = new mysqli("localhost", "root", "", "sksu_auth");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
