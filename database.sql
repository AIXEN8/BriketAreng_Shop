-- Tabel products (jika belum ada)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10,2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    gambar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk mencatat riwayat stok
CREATE TABLE stock_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    stok_sebelum INT NOT NULL,
    stok_sesudah INT NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Trigger untuk mencatat perubahan stok
DELIMITER //
CREATE TRIGGER after_stock_update
AFTER UPDATE ON products
FOR EACH ROW
BEGIN
    IF OLD.stok != NEW.stok THEN
        INSERT INTO stock_history (product_id, stok_sebelum, stok_sesudah, keterangan)
        VALUES (NEW.id, OLD.stok, NEW.stok, 'Update stok manual');
    END IF;
END;//
DELIMITER ; 