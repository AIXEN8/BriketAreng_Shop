<?php
session_start();
require_once 'includes/mongodb_connection.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu!";
    header("Location: login.php");
    exit();
}

$mongo = MongoDBConnection::getInstance();
$product_id = $_GET['id'] ?? '';

try {
    $product = $mongo->getProductById($product_id);
    
    if (!$product) {
        throw new Exception("Produk tidak ditemukan!");
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $jumlah = (int)$_POST['jumlah'];
        
        if ($jumlah <= 0 || $jumlah > $product->stok) {
            throw new Exception("Jumlah pembelian tidak valid!");
        }

        $total = $jumlah * $product->harga;
        
        // Insert transaksi
        $transaction = [
            'user_id' => $_SESSION['user_id'],
            'product_id' => $product_id,
            'jumlah' => $jumlah,
            'total' => $total,
            'status' => 'pending',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];

        $result = $mongo->insertTransaction($transaction);
        
        if ($result->getInsertedCount()) {
            // Update stok
            $mongo->updateProduct($product_id, [
                'stok' => $product->stok - $jumlah
            ]);

            $_SESSION['success'] = "Pembelian berhasil!";
            header("Location: transaksi.php");
            exit();
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Beli Produk - Toko Arang</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Beli Produk</h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <div class="product-info mb-4">
                            <h5><?php echo htmlspecialchars($product['nama_produk']); ?></h5>
                            <p class="mb-2">Harga: Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                            <p class="mb-3">Stok tersedia: <?php echo $product['stok']; ?></p>
                        </div>

                        <form method="POST" onsubmit="return validateForm()">
                            <div class="form-group">
                                <label>Jumlah</label>
                                <input type="number" 
                                       name="jumlah" 
                                       class="form-control" 
                                       min="1" 
                                       max="<?php echo $product['stok']; ?>" 
                                       required 
                                       id="jumlah">
                                <small class="form-text text-muted">Maksimal pembelian: <?php echo $product['stok']; ?> unit</small>
                            </div>
                            <div class="form-group">
                                <label>Total Harga</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="text" class="form-control" id="totalHarga" readonly>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">Beli</button>
                                <a href="index.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function validateForm() {
        const jumlah = document.getElementById('jumlah').value;
        const stok = <?php echo $product['stok']; ?>;
        
        if (jumlah <= 0) {
            alert('Jumlah pembelian harus lebih dari 0!');
            return false;
        }
        
        if (jumlah > stok) {
            alert('Stok tidak mencukupi!');
            return false;
        }
        
        return true;
    }

    // Update total harga saat jumlah berubah
    document.getElementById('jumlah').addEventListener('input', function() {
        const jumlah = this.value;
        const harga = <?php echo $product['harga']; ?>;
        const total = jumlah * harga;
        document.getElementById('totalHarga').value = new Intl.NumberFormat('id-ID').format(total);
    });
    </script>
</body>
</html>