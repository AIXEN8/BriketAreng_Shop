<?php
session_start();
require_once 'config.php';

// Validasi akses admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Penanganan error
try {
    $config = require 'config.php';
    $db = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['database']}",
        $config['db']['username'],
        $config['db']['password']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle form submission untuk update stok
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stok'])) {
        $stmt = $db->prepare("UPDATE products SET stok = :stok WHERE id = :id");
        $stmt->execute([
            'stok' => $_POST['stok'],
            'id' => $_POST['product_id']
        ]);
        $message = "Stok berhasil diperbarui!";
    }

    // Ambil data produk
    $stmt = $db->query("SELECT * FROM products ORDER BY nama_produk ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "Terjadi kesalahan sistem";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Stok</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container">
        <h1>Manajemen Stok</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Stok Saat Ini</th>
                        <th>Update Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['nama_produk']); ?></td>
                            <td><?php echo htmlspecialchars($product['stok']); ?></td>
                            <td>
                                <form method="POST" class="update-stok-form">
                                    <input type="hidden" name="product_id" 
                                           value="<?php echo $product['id']; ?>">
                                    <input type="number" name="stok" 
                                           value="<?php echo $product['stok']; ?>" 
                                           min="0" required>
                                    <button type="submit" name="update_stok" 
                                            class="btn btn-primary">Update</button>
                                </form>
                            </td>
                            <td>
                                <a href="riwayat_stok.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-info">Riwayat</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Form Tambah Produk Baru -->
        <div class="card mt-4">
            <div class="card-header">
                <h3>Tambah Produk Baru</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="tambah_produk.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nama_produk">Nama Produk</label>
                        <input type="text" name="nama_produk" id="nama_produk" 
                               class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" 
                                  class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga</label>
                        <input type="number" name="harga" id="harga" 
                               class="form-control" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="stok">Stok Awal</label>
                        <input type="number" name="stok" id="stok" 
                               class="form-control" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="gambar">Gambar Produk</label>
                        <input type="file" name="gambar" id="gambar" 
                               class="form-control" accept="image/*">
                    </div>
                    <button type="submit" name="tambah_produk" 
                            class="btn btn-success mt-3">Tambah Produk</button>
                </form>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script>
        // Validasi input stok
        document.querySelectorAll('.update-stok-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                const stokInput = form.querySelector('input[name="stok"]');
                if (parseInt(stokInput.value) < 0) {
                    e.preventDefault();
                    alert('Stok tidak boleh kurang dari 0!');
                }
            });
        });
    </script>
</body>
</html> 