<?php
// Configuration Initialization File
require_once 'db_connect.php'; 

define('ROOT_PATH', dirname(__DIR__));

// Get the protocol (http or https)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

// Get the host (localhost OR 192.168.x.x)
$host = $_SERVER['HTTP_HOST'];

// Combine them dynamically
define('BASE_URL', $protocol . "://" . $host . "/hydrotracker");

date_default_timezone_set('Asia/Kuala_Lumpur');

// Start it here so you never forget to add session_start() on a new page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>