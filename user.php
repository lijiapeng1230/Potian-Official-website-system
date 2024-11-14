<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
$auth = new Auth($pdo);

// 检查用户是否登录
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// 获取用户信息
$stmt = $pdo->prepare("
    SELECT username, email, is_vip, vip_level, vip_expire_time, created_at 
    FROM users 
    WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$userInfo = $stmt->fetch();

// 获取用户下载统计
function getUserDownloadStats($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT 
            download_type,
            COUNT(*) as download_count,
            MAX(download_time) as last_download
        FROM download_stats 
        WHERE user_id = ? 
        GROUP BY download_type
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// 获取用户统计数据
$userStats = getUserDownloadStats($pdo, $_SESSION['user_id']);

// 软件名称映射
$softwareNames = [
    'server_management' => '服务器管理系统5.0',
    'system_rescue' => '黎明系统急救箱',
    'ping_tool' => 'Ping包测试工具'
];

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = '';
    $success = '';
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_email':
                $newEmail = $_POST['email'];
                // 检查邮箱是否已被使用
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$newEmail, $_SESSION['user_id']]);
                if ($stmt->rowCount() > 0) {
                    $error = '该邮箱已被使用';
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                    $stmt->execute([$newEmail, $_SESSION['user_id']]);
                    $success = '邮箱更新成功';
                    $userInfo['email'] = $newEmail;
                }
                break;

            case 'change_password':
                $oldPassword = $_POST['old_password'];
                $newPassword = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];

                if ($newPassword !== $confirmPassword) {
                    $error = '两次输入的新密码不一致';
                } else {
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();

                    if (password_verify($oldPassword, $user['password'])) {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                        $success = '密码修改成功';
                    } else {
                        $error = '当前密码错误';
                    }
                }
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户中心 - 破天星辰科技网络</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #010d15;
        }

        .container {
            padding: 20px;
            color: #FFFFFF;
        }

        .card {
            background-color: #10232a;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background-color: #020b0c;
            border-bottom: 1px solid #0b666a;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
        }

        .btn {
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #0b666a;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0c7075;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: #8b0000;
            border: none;
        }

        .btn-danger:hover {
            background-color: #a00000;
            transform: translateY(-2px);
        }

        .btn-edit {
            padding: 8px 20px;
            border-radius: 20px;
        }

        .vip-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            margin-left: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
        }

        .vip-badge.gold { 
            background: linear-gradient(45deg, #FFD700, #FFA500); 
            color: #000; 
        }

        .vip-badge.silver { 
            background: linear-gradient(45deg, #C0C0C0, #A9A9A9); 
            color: #000; 
        }

        .vip-badge.bronze { 
            background: linear-gradient(45deg, #CD7F32, #8B4513); 
            color: #fff; 
        }

        .vip-badge.normal { 
            background: linear-gradient(45deg, #6c757d, #495057); 
            color: #fff; 
        }

        .info-item {
            padding: 15px;
            border-radius: 8px;
            background-color: #020b0c;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .info-item:hover {
            background-color: #0b666a;
            transform: translateX(5px);
        }

        .info-label {
            color: #7FFFD4;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .info-value {
            color: #fff;
            font-size: 1.1em;
        }

        .table {
            color: #fff;
        }

        .table td, .table th {
            border-color: #0b666a;
            padding: 12px;
        }

        .table thead th {
            background-color: #020b0c;
            border-bottom: 2px solid #0b666a;
        }

        .table tbody tr {
            transition: all 0.3s;
        }

        .table tbody tr:hover {
            background-color: #0b666a;
        }

        .form-control {
            background-color: #020b0c;
            border: 1px solid #0b666a;
            color: #fff;
            transition: all 0.3s;
        }

        .form-control:focus {
            background-color: #020b0c;
            border-color: #7FFFD4;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(127, 255, 212, 0.25);
        }

        .disabled-input {
            background-color: #1a2930;
            color: #6c757d;
            cursor: not-allowed;
        }

        .alert {
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
            animation: slideIn 0.5s ease-out;
        }

        .alert-success {
            background-color: #0b666a;
            color: #fff;
        }

        .alert-danger {
            background-color: #8b0000;
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

        /* 添加过渡动画 */
        .edit-form, .user-info-display {
            transition: all 0.3s ease-in-out;
        }

        .edit-form {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 美化滚动条 */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #020b0c;
        }

        ::-webkit-scrollbar-thumb {
            background: #0b666a;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #0c7075;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- 用户基本信息卡片 -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-user me-2"></i>个人信息</h4>
                <button class="btn btn-primary btn-edit" onclick="toggleEditInfo()">
                    <i class="fas fa-edit me-2"></i>修改信息
                </button>
            </div>
            <div class="card-body">
                <!-- 信息显示区域 -->
                <div class="user-info-display" id="infoDisplay">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-user me-2"></i>用户名
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($userInfo['username']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-envelope me-2"></i>邮箱
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($userInfo['email']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-crown me-2"></i>会员状态
                                </div>
                                <div class="info-value">
                                    <span class="vip-badge <?php 
                                        $vipLevel = (int)$userInfo['vip_level'];
                                        switch($vipLevel) {
                                            case 3: echo 'gold'; break;
                                            case 2: echo 'silver'; break;
                                            case 1: echo 'bronze'; break;
                                            default: echo 'normal';
                                        }
                                    ?>">
                                    <?php
                                        switch($vipLevel) {
                                            case 3: echo '黄金VIP'; break;
                                            case 2: echo '白银VIP'; break;
                                            case 1: echo '青铜VIP'; break;
                                            default: echo '普通会员';
                                        }
                                    ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-calendar-alt me-2"></i>注册时间
                                </div>
                                <div class="info-value">
                                    <?php echo date('Y-m-d H:i:s', strtotime($userInfo['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-clock me-2"></i>VIP到期时间
                                </div>
                                <div class="info-value">
                                    <?php 
                                    if ($userInfo['is_vip'] && $userInfo['vip_expire_time']) {
                                        echo date('Y-m-d', strtotime($userInfo['vip_expire_time']));
                                        
                                        // 计算剩余天数
                                        $daysLeft = ceil((strtotime($userInfo['vip_expire_time']) - time()) / (24 * 3600));
                                        if ($daysLeft > 0) {
                                            echo " <small class='text-warning'>（还剩 {$daysLeft} 天）</small>";
                                        } else {
                                            echo " <small class='text-danger'>（已过期）</small>";
                                        }
                                    } else {
                                        echo '<span class="text-muted">未开通VIP</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-download me-2"></i>累计下载
                                </div>
                                <div class="info-value">
                                    <?php
                                    $totalDownloads = 0;
                                    foreach ($userStats as $stat) {
                                        $totalDownloads += $stat['download_count'];
                                    }
                                    echo $totalDownloads . ' 次';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($userInfo['is_vip']): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-star me-2"></i>会员特权
                                </div>
                                <div class="info-value">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <i class="fas fa-check-circle text-success me-2"></i>无限次下载
                                        </div>
                                        <div class="col-md-4">
                                            <i class="fas fa-check-circle text-success me-2"></i>优先技术支持
                                        </div>
                                        <div class="col-md-4">
                                            <i class="fas fa-check-circle text-success me-2"></i>专属客服服务
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 信息编辑表单 -->
                <div class="edit-form" id="editForm">
                    <form method="post">
                        <input type="hidden" name="action" value="update_email">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">用户名</label>
                                    <input type="text" class="form-control disabled-input" value="<?php echo htmlspecialchars($userInfo['username']); ?>" disabled>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">邮箱</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($userInfo['email']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">会员状态</label>
                                    <input type="text" class="form-control disabled-input" value="<?php 
                                        switch($vipLevel) {
                                            case 3: echo '黄金VIP'; break;
                                            case 2: echo '白银VIP'; break;
                                            case 1: echo '青铜VIP'; break;
                                            default: echo '普通会员';
                                        }
                                    ?>" disabled>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">保存修改</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleEditInfo()">取消</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 修改密码卡片 -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-key me-2"></i>密码管理</h4>
                <button class="btn btn-primary btn-edit" onclick="togglePasswordForm()">
                    <i class="fas fa-edit me-2"></i>修改密码
                </button>
            </div>
            <div class="card-body">
                <!-- 密码状态显示 -->
                <div class="user-info-display" id="passwordDisplay">
                    <div class="info-item">
                        <div class="info-label">密码状态</div>
                        <div class="info-value">
                            <i class="fas fa-circle-check text-success me-2"></i>已设置
                        </div>
                        <small class="text-muted">如需修改密码，请点击右上角的"修改密码"按钮</small>
                    </div>
                </div>

                <!-- 修改密码表单 -->
                <div class="edit-form" id="passwordForm">
                    <form method="post">
                        <input type="hidden" name="action" value="change_password">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">当前密码</label>
                                    <input type="password" class="form-control" name="old_password" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">新密码</label>
                                    <input type="password" class="form-control" name="new_password" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">确认新密码</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">保存修改</button>
                        <button type="button" class="btn btn-secondary" onclick="togglePasswordForm()">取消</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 下载记录统计 -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-download me-2"></i>下载记录统计</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($userStats)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>软件名称</th>
                                    <th>下载次数</th>
                                    <th>最后下载时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userStats as $stat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($softwareNames[$stat['download_type']] ?? $stat['download_type']); ?></td>
                                        <td><?php echo htmlspecialchars($stat['download_count']); ?></td>
                                        <td><?php echo date('Y-m-d H:i:s', strtotime($stat['last_download'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>暂无下载记录</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-3 mb-4">
            <a href="index.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-home me-2"></i>返回首页
            </a>
            <a href="logout.php" class="btn btn-danger btn-lg">
                <i class="fas fa-sign-out-alt me-2"></i>退出登录
            </a>
        </div>
    </div>

    <script src="https://cdn.bootcdn.net/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleEditInfo() {
            const displayDiv = document.getElementById('infoDisplay');
            const editForm = document.getElementById('editForm');
            if (displayDiv.style.display !== 'none') {
                displayDiv.style.display = 'none';
                editForm.style.display = 'block';
            } else {
                displayDiv.style.display = 'block';
                editForm.style.display = 'none';
            }
        }

        function togglePasswordForm() {
            const displayDiv = document.getElementById('passwordDisplay');
            const editForm = document.getElementById('passwordForm');
            if (displayDiv.style.display !== 'none') {
                displayDiv.style.display = 'none';
                editForm.style.display = 'block';
            } else {
                displayDiv.style.display = 'block';
                editForm.style.display = 'none';
            }
        }

        // 确保页面加载时表单是隐藏的
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('passwordForm').style.display = 'none';
            document.getElementById('editForm').style.display = 'none';
        });
    </script>
</body>
</html> 