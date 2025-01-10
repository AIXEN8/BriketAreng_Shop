<?php
session_start();
require_once 'config.php';

// Validasi akses
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Penanganan error
try {
    // Kode orders
} catch (Exception $e) {
    error_log($e->getMessage());
    // Tampilkan pesan error yang aman untuk user
}