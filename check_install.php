<?php
class InstallationChecker {
    public static function needsInstallation() {
        // 检查安装锁定文件
        if (!file_exists('config/installed.lock')) {
            return true;
        }

        // 检查配置文件是否存在
        if (!file_exists('config/database.php')) {
            return true;
        }

        try {
            // 加载数据库配置
            $config = require 'config/database.php';
            
            // 尝试连接数据库
            $conn = new mysqli(
                $config['db_host'],
                $config['db_user'],
                $config['db_pass'],
                $config['db_name']
            );

            // 检查连接是否成功
            if ($conn->connect_error) {
                return true;
            }

            // 测试查询
            $result = $conn->query("SELECT COUNT(*) as count FROM users");
            if (!$result) {
                return true;
            }

            $conn->close();
            return false;

        } catch (Exception $e) {
            return true;
        }
    }
} 