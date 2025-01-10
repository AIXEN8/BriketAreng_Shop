<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/mongodb.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validasi input
        $username = sanitize_input($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validasi password match
        if ($password !== $confirm_password) {
            throw new Exception("Password tidak cocok!");
        }

        // Validasi panjang password
        if (strlen($password) < 8) {
            throw new Exception("Password minimal 8 karakter!");
        }

        // Validasi kekuatan password
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            throw new Exception("Password harus mengandung huruf besar, huruf kecil, angka, dan karakter khusus!");
        }

        // Cek username sudah ada atau belum
        $existingUser = $users->findOne(['username' => $username]);
        if ($existingUser) {
            throw new Exception("Username sudah digunakan!");
        }

        // Hash password dengan algoritma modern
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 2048,
            'time_cost' => 4,
            'threads' => 3
        ]);

        // Siapkan data user
        $userData = [
            'username' => $username,
            'password' => $hashedPassword
        ];

        // Simpan ke MongoDB
        $userId = saveUserToMongo($userData);
        
        if ($userId) {
            // Buat token aktivasi
            $activationToken = generate_token();
            
            // Simpan token ke database
            $users->updateOne(
                ['_id' => $userId],
                ['$set' => ['activation_token' => $activationToken]]
            );

            $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
            header("Location: login.php");
            exit();
        } else {
            throw new Exception("Gagal melakukan registrasi!");
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Toko Briket Arang</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('assets/images/charcoal-bg.jpg') center/cover;
            padding: 2rem 0;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.5s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 40px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.1);
            outline: none;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
        }

        .register-link {
            text-align: center;
            margin-top: 1rem;
        }

        .register-link a {
            color: var(--secondary-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            color: var(--accent-color);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-message {
            background: #ff6b6b;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Tambahan style untuk indikator kekuatan password */
        .password-strength {
            margin-top: 5px;
            height: 5px;
            background: #ddd;
            border-radius: 3px;
        }

        .password-strength-meter {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .weak { background: #ff4444; width: 33.33%; }
        .medium { background: #ffbb33; width: 66.66%; }
        .strong { background: #00C851; width: 100%; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="login-header">
                <h1>Register</h1>
                <p>Buat akun baru</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" 
                           name="username" 
                           class="form-control" 
                           placeholder="Username"
                           required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Password"
                           required>
                </div>

                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" 
                           name="confirm_password" 
                           class="form-control" 
                           placeholder="Konfirmasi Password"
                           required>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-user-plus"></i> Register
                </button>

                <div class="register-link">
                     <a href="login.php">Login</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Animasi form
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('i').style.color = '#e67e22';
            });

            input.addEventListener('blur', function() {
                this.parentElement.querySelector('i').style.color = '#2c3e50';
            });
        });

        // Validasi password match
        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="confirm_password"]');
        
        function validatePassword() {
            if (password.value != confirmPassword.value) {
                confirmPassword.setCustomValidity("Password tidak cocok!");
            } else {
                confirmPassword.setCustomValidity('');
            }
        }

        password.onchange = validatePassword;
        confirmPassword.onkeyup = validatePassword;

        // Fungsi untuk mengecek kekuatan password
        function checkPasswordStrength(password) {
            let strength = 0;
            
            // Minimal 8 karakter
            if (password.length >= 8) strength++;
            
            // Mengandung huruf kecil dan besar
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength++;
            
            // Mengandung angka dan karakter
            if (password.match(/([0-9])/)) strength++;
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength++;
            
            return strength;
        }

        // Update indikator kekuatan password
        document.querySelector('input[name="password"]').addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            const meter = document.querySelector('.password-strength-meter');
            
            meter.className = 'password-strength-meter';
            if (strength >= 4) {
                meter.classList.add('strong');
            } else if (strength >= 2) {
                meter.classList.add('medium');
            } else {
                meter.classList.add('weak');
            }
        });
    </script>
</body>
</html> 