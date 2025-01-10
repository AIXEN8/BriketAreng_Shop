<?php
session_start();
include 'config/database.php';

// Cek autentikasi admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validasi input dasar
        $nama_produk = trim($_POST['nama_produk']);
        $deskripsi = trim($_POST['deskripsi']);
        $harga = floatval($_POST['harga']);
        $stok = intval($_POST['stok']);

        // Validasi nilai
        if (empty($nama_produk)) {
            throw new Exception("Nama produk tidak boleh kosong!");
        }
        if ($harga <= 0) {
            throw new Exception("Harga harus lebih dari 0!");
        }
        if ($stok < 0) {
            throw new Exception("Stok tidak boleh negatif!");
        }

        // Validasi dan proses upload file
        if (!isset($_FILES["gambar"]) || $_FILES["gambar"]["error"] != 0) {
            throw new Exception("File gambar wajib diupload!");
        }

        $target_dir = "assets/images/products/";
        
        // Buat direktori jika belum ada
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                throw new Exception("Gagal membuat direktori upload!");
            }
        }

        // Validasi ukuran file (5MB max)
        if ($_FILES["gambar"]["size"] > 5000000) {
            throw new Exception("File terlalu besar! Maksimal 5MB");
        }

        // Generate nama file unik
        $file_extension = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Validasi tipe file
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = mime_content_type($_FILES["gambar"]["tmp_name"]);
        
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception("Hanya file JPG, JPEG & PNG yang diizinkan!");
        }

        // Upload file
        if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            throw new Exception("Gagal mengupload gambar!");
        }

        // Insert ke database
        $query = "INSERT INTO products (nama_produk, deskripsi, harga, stok, gambar) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdis", $nama_produk, $deskripsi, $harga, $stok, $target_file);

        if (!$stmt->execute()) {
            // Hapus file jika gagal insert ke database
            unlink($target_file);
            throw new Exception("Gagal menambahkan produk ke database!");
        }

        $_SESSION['success'] = "Produk berhasil ditambahkan!";
        header("Location: index.php");
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Produk - Toko Briket Arang</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/images/charcoal-bg.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            padding: 20px 0;
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .preview-image {
            max-width: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h4 class="mb-0">Tambah Produk</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger animate__animated animate__shakeX">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data" novalidate>
                            <div class="form-group">
                                <label>Nama Produk</label>
                                <input type="text" 
                                       name="nama_produk" 
                                       class="form-control" 
                                       required
                                       value="<?php echo isset($_POST['nama_produk']) ? htmlspecialchars($_POST['nama_produk']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea name="deskripsi" 
                                          class="form-control" 
                                          rows="4" 
                                          required><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Harga (Rp)</label>
                                <input type="number" 
                                       name="harga" 
                                       class="form-control" 
                                       required 
                                       min="0"
                                       value="<?php echo isset($_POST['harga']) ? htmlspecialchars($_POST['harga']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label>Stok</label>
                                <input type="number" 
                                       name="stok" 
                                       class="form-control" 
                                       required 
                                       min="0"
                                       value="<?php echo isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label>Gambar Produk</label>
                                <input type="file" 
                                       name="gambar" 
                                       class="form-control-file" 
                                       required 
                                       accept="image/jpeg,image/png,image/jpg"
                                       onchange="previewImage(this);">
                                <small class="form-text text-muted">
                                    Format: JPG, JPEG, PNG. Maksimal 5MB
                                </small>
                                <img id="preview" class="preview-image">
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Tambah Produk</button>
                                <a href="index.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
    function previewImage(input) {
        const preview = document.getElementById('preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }
    </script>
</body>
</html> 