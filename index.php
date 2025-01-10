<?php
session_start();
require_once 'config.php';

// Penanganan error
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ? '1' : '0');

// Fungsi autoload jika menggunakan composer
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
}

// Koneksi database
try {
    $config = require 'config.php';
    $db = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['database']}",
        $config['db']['username'],
        $config['db']['password']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
