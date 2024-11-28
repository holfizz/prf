<?php
$connect = mysqli_connect('localhost', 'root', 'root', 'prf');

if (!$connect) {
    error_log("Failed to connect to MySQL: " . mysqli_connect_error());
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($connect, "utf8mb4");
error_log("Database connection established successfully");

