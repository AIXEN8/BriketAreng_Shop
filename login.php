<?php
session_start();
require_once 'includes/mongodb_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $mongo = MongoDBConnection::getInstance();
        $username = trim(htmlspecialchars($_POST['username']));
        $password = $_POST['password'];

        $user = $mongo->getUserByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = (string)$user['_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Update last login
            $mongo->getCollection('users')->updateOne(
                ['_id' => $user['_id']],
                ['$set' => ['last_login' => new MongoDB\BSON\UTCDateTime()]]
            );

            header("Location: index.php");
            exit();
        } else {
            throw new Exception("Username atau password salah!");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Toko Briket Arang</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('assets/images/charcoal-bg.jpg') center/cover;
        }
        
        .login-card {
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

        .register-cta {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }

        .register-cta p {
            color: #666;
            margin-bottom: 1rem;
        }

        .btn-register {
            display: inline-block;
            background: transparent;
            color: var(--secondary-color);
            border: 2px solid var(--secondary-color);
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }

        .btn-register:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(230, 126, 34, 0.3);
            animation: none;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(230, 126, 34, 0.4);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(230, 126, 34, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(230, 126, 34, 0);
            }
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Login</h1>
                <p>Selamat datang kembali!</p>
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

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="register-cta">
                <p></p>
                <a href="register.php" class="btn-register">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </a>
            </div>
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
    </script>
</body>
</html> 