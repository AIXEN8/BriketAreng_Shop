<?php
session_start();
require_once 'config.php';

// Validasi akses admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Validasi parameter id
if (!isset($_GET['id'])) {
    header('Location: stok.php');
    exit();
}

try {
    $config = require 'config.php';
    $db = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['database']}",
        $config['db']['username'],
        $config['db']['password']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil data produk
    $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("Produk tidak ditemukan");
    }

    // Ambil riwayat stok
    $stmt = $db->prepare("
        SELECT * FROM stock_history 
        WHERE product_id = :id 
        ORDER BY created_at DESC
    ");
    $stmt->execute(['id' => $_GET['id']]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "Terjadi kesalahan sistem";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Stok - <?php echo htmlspecialchars($product['nama_produk']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container">
        <h1>Riwayat Stok: <?php echo htmlspecialchars($product['nama_produk']); ?></h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Stok Sebelum</th>
                        <th>Stok Sesudah</th>
                        <th>Perubahan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $record): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($record['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($record['stok_sebelum']); ?></td>
                            <td><?php echo htmlspecialchars($record['stok_sesudah']); ?></td>
                            <td><?php 
                                $perubahan = $record['stok_sesudah'] - $record['stok_sebelum'];
                                echo ($perubahan >= 0 ? '+' : '') . $perubahan;
                            ?></td>
                            <td><?php echo htmlspecialchars($record['keterangan']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a href="stok.php" class="btn btn-primary">Kembali ke Manajemen Stok</a>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html> 