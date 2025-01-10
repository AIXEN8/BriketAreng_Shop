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
    // Kode customer
} catch (Exception $e) {
    error_log($e->getMessage());
    // Tampilkan pesan error yang aman untuk user
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Customer - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container">
        <h1>Manajemen Customer</h1>
        
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>Nama Customer</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Tanggal Registrasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customerBarang as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['nama']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['status']); ?></td>
                        <td><?php 
                            $timestamp = $customer['created_at']->toDateTime();
                            echo $timestamp->format('d M Y H:i'); 
                        ?></td>
                        <td>
                            <form method="POST" class="update-customer-form">
                                <input type="hidden" name="customer_id" value="<?php echo $customer['_id']; ?>">
                                <select name="status">
                                    <option value="active" <?php echo $customer['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $customer['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                                <button type="submit" name="update_customer" class="btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html> 