<?php
// Recommended: set DB credentials in environment variables or a secure secrets store
$server   = getenv('DB_HOST') ?: 'localhost';
$user     = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'Business';

$connect = mysqli_connect($server, $user, $password, $database);
if (!$connect) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    die('Database connection error.');
}

// Ensure UTF-8 support
$connect->set_charset('utf8mb4');

?> 