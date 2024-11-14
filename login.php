<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth($pdo);
$error = '';

// 如果已经登录，跳转到用户中心
if ($auth->isLoggedIn()) {
    header('Location: user.php');
    exit;
}

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($username, $password)) {
        header('Location: user.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户登录 - 破天星辰科技网络</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #010d15;
            background-image: url('https://app.potiankeji.top/cdn/img/background4.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .card {
            background-color: rgba(16, 35, 42, 0.9);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.5s ease-out;
        }

        .card-header {
            background-color: rgba(2, 11, 12, 0.8);
            border-bottom: 1px solid #0b666a;
            border-radius: 15px 15px 0 0;
            padding: 20px;
            text-align: center;
        }

        .card-header h3 {
            color: #fff;
            margin: 0;
            font-size: 1.8em;
        }

        .card-body {
            padding: 30px;
        }

        .form-control {
            background-color: rgba(2, 11, 12, 0.8);
            border: 1px solid #0b666a;
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .form-control:focus {
            background-color: rgba(2, 11, 12, 0.9);
            border-color: #7FFFD4;
            box-shadow: 0 0 0 0.25rem rgba(127, 255, 212, 0.25);
            color: #fff;
        }

        .btn-primary {
            background-color: #0b666a;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-size: 1.1em;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: #0c7075;
            transform: translateY(-2px);
        }

        .alert {
            background-color: rgba(139, 0, 0, 0.9);
            border: none;
            color: #fff;
            border-radius: 8px;
            animation: shake 0.5s ease-in-out;
        }

        .form-label {
            color: #7FFFD4;
            font-size: 0.9em;
            margin-bottom: 8px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #fff;
        }

        .register-link a {
            color: #7FFFD4;
            text-decoration: none;
            transition: all 0.3s;
        }

        .register-link a:hover {
            color: #fff;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .back-link {
            position: fixed;
            top: 20px;
            left: 20px;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(11, 102, 106, 0.8);
            border-radius: 8px;
            transition: all 0.3s;
        }

        .back-link:hover {
            background: rgba(12, 112, 117, 0.9);
            color: #fff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left me-2"></i>返回首页
    </a>

    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <h3>用户登录</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-4">
                        <label class="form-label">用户名</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-0 text-light">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">密码</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-0 text-light">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>登录
                    </button>
                </form>

                <div class="register-link">
                    还没有账号？<a href="register.php">立即注册</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.bootcdn.net/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>

