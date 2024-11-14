<?php
require_once 'check_install.php';

// 修改安装检测逻辑
if (!InstallationChecker::needsInstallation()) {
    echo '<script>alert("系统已经安装！正在跳转到首页..."); window.location.href = "index.php";</script>';
    exit;
}

// 检查PHP版本
$required_php_version = '7.4.0';
$php_check = version_compare(PHP_VERSION, $required_php_version, '>=');

// 检查MySQL版本
function checkMySQLVersion() {
    try {
        if (extension_loaded('mysqli')) {
            $mysqli = new mysqli();
            $version = mysqli_get_client_info();
            return version_compare($version, '5.6.0', '>=');
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? '';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    
    // 测试数据库连接
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass);
        
        if ($conn->connect_error) {
            throw new Exception("数据库连接失败: " . $conn->connect_error);
        }
        
        // 创建数据库（如果不存在）
        $sql = "CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
        if (!$conn->query($sql)) {
            throw new Exception("创建数据库失败: " . $conn->error);
        }
        
        // 选择数据库
        $conn->select_db($db_name);
        
        // 设置字符集
        $conn->set_charset("utf8");
        
        // 获取所有表名
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        
        // 如果存在表，先删除它们
        if (!empty($tables)) {
            // 先禁用外键检查
            $conn->query('SET FOREIGN_KEY_CHECKS = 0');
            
            foreach ($tables as $table) {
                $conn->query("DROP TABLE IF EXISTS `$table`");
            }
            
            // 重新启用外键检查
            $conn->query('SET FOREIGN_KEY_CHECKS = 1');
        }
        
        // 导入数据库结构
        $sql = file_get_contents('database/install.sql');
        
        // 执行多条SQL语句
        if ($conn->multi_query($sql)) {
            do {
                // 处理每个查询的结果
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
        }
        
        if ($conn->error) {
            throw new Exception("导入数据失败: " . $conn->error);
        }
        
        // 创建配置文件
        $config_content = "<?php\nreturn [\n    'db_host' => '{$db_host}',\n    'db_name' => '{$db_name}',\n    'db_user' => '{$db_user}',\n    'db_pass' => '{$db_pass}'\n];";
        
        if (!is_dir('config')) {
            mkdir('config', 0755, true);
        }
        
        file_put_contents('config/database.php', $config_content);
        
        // 创建安装锁定文件
        file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
        
        echo '<script>alert("安装成功！正在跳转到首页..."); window.location.href = "index.php";</script>';
        exit;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>系统安装向导</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .check-item { margin: 10px 0; }
        .success { color: green; }
        .error { color: red; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h1>系统安装向导</h1>
        
        <h2>环境检查</h2>
        <div class="check-item">
            PHP版本 (要求 >= <?php echo $required_php_version; ?>):
            <span class="<?php echo $php_check ? 'success' : 'error'; ?>">
                <?php echo PHP_VERSION; ?> <?php echo $php_check ? '✓' : '✗'; ?>
            </span>
        </div>
        
        <div class="check-item">
            MySQL版本 (要求 >= 5.6.0):
            <span class="<?php echo checkMySQLVersion() ? 'success' : 'error'; ?>">
                <?php echo checkMySQLVersion() ? '✓' : '✗'; ?>
            </span>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <h2>数据库配置</h2>
        <form method="POST">
            <div class="form-group">
                <label>数据库主机:</label>
                <input type="text" name="db_host" value="localhost" required>
            </div>
            
            <div class="form-group">
                <label>数据库名称:</label>
                <input type="text" name="db_name" required>
            </div>
            
            <div class="form-group">
                <label>数据库用户名:</label>
                <input type="text" name="db_user" required>
            </div>
            
            <div class="form-group">
                <label>数据库密码:</label>
                <input type="password" name="db_pass" required>
            </div>
            
            <button type="submit">开始安装</button>
        </form>
    </div>
</body>
</html> 