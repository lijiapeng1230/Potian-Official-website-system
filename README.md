# 破天网站（极简）开源系统 简介



这是一个基于PHP和MySQL开发的简陋网站系统。本项目采用响应式设计，支持多终端访问，集成了用户管理、下载统计、日志记录等功能模块。网站使用PDO进行数据库操作，确保了数据安全性；采用面向对象的设计思想，代码结构清晰，易于维护和扩展。系统支持VIP会员机制，并包含完整的管理后台。
我作为一名在学的代码初学者，没有多少编写代码的经验，写的不好还请见谅
也希望大家能够帮助我一起成长
积极的帮助我发现并改进漏洞，谢谢大家了！



### 最新的当前版本BUG以及解决方法
- 1.0.1版本：首次安装的填写管理员信息的地方需要删除admin自己写一个用户名，改变邮箱，密码自定义。删除提供的邮箱，自己填写安装即可。（此问题已修复）
- 1.0.1版本：用户无法使用邮箱登录，只能使用用户名登录。
- 2.0版本：VIP套餐的编辑按钮无法打开

## 核心文件说明
### 1. index.php
- 网站主页文件
- 功能：
  - 检查系统安装状态
  - 数据库连接
  - 用户认证
  - 下载统计
  - 展示网站内容
  - 响应式界面设计

### 2. setup.php
- 系统安装向导
- 功能：
  - 环境检查（PHP >= 7.4.0, MySQL >= 5.6.0）
  - 数据库配置
  - 数据库初始化
  - 创建配置文件
  - 安装锁定机制

### 3. check_install.php
- 安装状态检测类
- 功能：
  - 检查安装锁定文件
  - 验证配置文件
  - 测试数据库连接
  - 验证数据库完整性

### 4. database/install.sql
- 数据库初始化脚本
- 包含表结构：
  - users（用户表）
  - admin_logs（管理员日志）
  - download_stats（下载统计）
  - login_logs（登录日志）
  - settings（系统设置）
- 默认数据：
  - 管理员账号
  - 网站基本设置

## 安装说明

1. 环境要求：
   - PHP >= 7.4.0
   - MySQL >= 5.6.0
   - PDO 扩展
   - mysqli 扩展
1.1 系统要求清单：（可选）
   - PHP 环境：
   - PHP 版本 >= 7.4.0
   - 内存限制 >= 128M
   - 上传文件大小 >= 20M
   - 必需的 PHP 扩展：
   - PDO：数据库操作基础支持
   - PDO_MySQL：MySQL 数据库驱动
   - MySQLi：MySQL 改进扩展
   - MBString：多字节字符串处理
   - JSON：JSON 数据处理
   - cURL：网络请求支持
   - GD：图像处理
   - OpenSSL：加密支持
   - ZIP：压缩文件处理
   - Fileinfo：文件信息检测
   - 目录权限要求：
   - config 目录：可写（755）
   - logs 目录：可写（755）
   - uploads 目录：可写（755）
   - temp 目录：可写（755）
   - 推荐的服务器环境：
   - Apache/Nginx
   - MySQL 5.6 或更高版本
   - 支持 URL 重写（mod_rewrite）
   - 其他建议：
   - 启用 OPcache 以提高性能
   - 建议使用 SSL 证书
   - 定期备份数据库
   - 设置适当的文件权限
  1.2 这些要求确保系统能够正常运行和处理各种功能，包括：（可选）
   - 数据库操作
   - 文件上传和处理
   - 图片处理
   - 安全加密
   - 网络通信
   - 文件压缩
   - 字符编码处理
2. 安装步骤：
   - 上传所有文件到网站根目录
   - 访问 setup.php 进行安装
   - 填写数据库信息
   - 等待系统自动完成安装

3. 默认管理员账号：
   - 用户名：admin
   - 邮箱：admin@xctcn.cn
   - 密码：123456
4. 了解前端开发的可以熟悉index.php：
   - 修改相应HTML语句部分
   - 或者直接修改文字
   
## 主要功能

1. 用户系统：
   - 用户注册/登录
   - 管理员权限控制
   - VIP 会员系统

2. 下载系统：
   - 下载统计
   - 访问控制
   - 下载记录

3. 日志系统：
   - 管理员操作日志
   - 用户登录日志
   - 下载统计日志

4. 系统设置：
   - 网站基本信息配置
   - 功能开关控制
   - 维护模式

## 安全说明

1. 安装完成后建议：
   - 修改默认管理员密码
   - 删除或重命名 setup.php
   - 设置 config 目录权限

2. 数据库安全：
   - 使用 PDO 预处理语句
   - 密码加密存储
   - 防止 SQL 注入

## 维护建议

1. 定期备份：
   - 数据库备份
   - 配置文件备份

2. 安全更新：
   - 定期更新 PHP 版本
   - 检查系统漏洞
   - 更新密码策略

3. 性能优化：
   - 定期清理日志
   - 优化数据库索引
   - 监控系统负载

## 历代BUG及其修复教程
1.0.1BUG
- 管理员密码修改，在phpadmin执行以下 SQL
- UPDATE users SET password = '$2y$10$3PGqpkNpVJ2yXrXMwbvfkOHOPOGLEGY0AQhXdVUieGg8G6Qm8gEuy' WHERE username = 'admin';

## 技术支持

- 官方QQ群：734555740
- 官方邮箱：potiankeji2022@163.com
- 哔哩哔哩：https://space.bilibili.com/541002925
- 抖音：https://v.douyin.com/iAMXt2RM/

## 版权

©破天星辰科技网络工作室 开源项目
