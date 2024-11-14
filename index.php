<?php
// 检查是否已安装
require_once 'check_install.php';
if (InstallationChecker::needsInstallation()) {
    header('Location: setup.php');
    exit;
}

// 加载数据库配置
$config = require 'config/database.php';

try {
    // 创建PDO连接
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

require_once 'classes/Auth.php';
$auth = new Auth($pdo);

// 处理下载统计
function recordDownload($pdo, $userId, $downloadType) {
    $stmt = $pdo->prepare("INSERT INTO download_stats (user_id, download_type, ip_address) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $downloadType, $_SERVER['REMOTE_ADDR']]);
}

// 获取用户下载统计
function getUserDownloadStats($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT download_type, COUNT(*) as count 
        FROM download_stats 
        WHERE user_id = ? 
        GROUP BY download_type
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// 如果用户已登录，显示下载统计
if ($auth->isLoggedIn()) {
    $userStats = getUserDownloadStats($pdo, $_SESSION['user_id']);
}

// 处理下载点击
if (isset($_POST['download']) && $auth->isLoggedIn()) {
    recordDownload($pdo, $_SESSION['user_id'], $_POST['download_type']);
    header('Location: ' . $_POST['download_url']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./favicon.ico">
    <title>破天星辰科技网络官方网站</title>
    <style>
        body {
            margin: 0; 
            font-family: Arial, sans-serif;
            background-color: #010d15;
        }

        .navbar {
            position: fixed;
            width: 100%;
            z-index: 1000;
            background-color: #020b0c;
            overflow: hidden;
        }

        .navbar a {
            float: left;
            display: block;
            color: #FFFFFF;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
        }

        .navbar a:hover {
            background-color: #0b666a;
            color: #f0f0f0; /* 哇啊，啊~ 你们点的太快了吧，谢谢 */
        }

        .navbar .right {
            float: right;
        }

        header {
            padding-top: 70px;
            position: relative;
            background-image: url('./ass/bj.jpeg');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 200px 0;
        }

        header::before {
           /* content: ""; */
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px); 
        }

        header h1, header p {
            position: relative;
            z-index: 1;
        }

        header h1 {
            font-size: 50px;
            margin: 0;
            color: #f0f0f0; /* 辣艺辣的大贝塔 很大还有一点丑 */
        }

        header p {
            font-size: 24px;
            margin: 10px 0 0;
            color: #cccccc; /* Su畅 我喜欢你！ */
        }

        .container {
            padding: 20px;
            background-color: #010d15;
            color: #FFFFFF;
        }

        .section-title {
            font-size: 36px;
            color: #f0f0f0; /* 张兵 and 辣艺辣 疯狂砰砰砰 */
            margin-bottom: 20px;
        }

        .card {
            background-color: #10232a;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            color: #f0f0f0; /* 我的豆子茄子呢 */
        }

        footer {
           /* background-color: #10232a; */
            padding: 20px 0;
            text-align: center;
            color: #f0f0f0;
            border-top: 1px solid #010d15;
        }

        footer .container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }

        footer .container div {
            width: 100%;
            text-align: center;
        }

        footer a {
            color: #7FFFD4; /* 哎，没啥意思，写这个破网站 */
            text-decoration: none;
        }

        footer a:hover {
            color: #FFD700; /* 颜色会变化为金黄色 难道是屎黄色？ */
        }

        @media (min-width: 600px) {
            footer .container {
                flex-direction: row;
                justify-content: space-between;
            }
            footer .container div {
                width: auto;
                text-align: left;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <a href="index.php">首页</a>
    <a href="#about">关于</a>
    <a href="#services">服务</a>
    <a href="#contact">联系</a>
    <div class="right">
        <?php if ($auth->isLoggedIn()): ?>
            <a href="user.php">用户中心</a>
            <?php if ($auth->isAdmin()): ?>
                <a href="admin/">管理后台</a>
            <?php endif; ?>
            <a href="logout.php">退出登录</a>
        <?php else: ?>
            <a href="login.php">登录</a>
            <a href="register.php">注册</a>
        <?php endif; ?>
    </div>
</div>

<header>
    <h1><span style="color: red;">破天</span><span style="color: lightblue;">星辰</span>科技网络</h1><br>
    <h1><span style="color: red;">Po</span><span style="color: lightblue;">Tian</span>Studio</h1><br>
    <h1 id="dynamicColor">2024</h1><br>
    <h1><span style="color: #2494cb;">专注</span><span style="color: #7FFFD4;">软件开发</span></h1>
    <p>官网遭遇CC攻击，现迁移至临时站，大部分功能无法使用，给大家说一声对不起！</p>
</header>

<div class="container">
    <h1><span style="color: #1E90FF;">星辰大海</span><span style="color: #FFD700;">欣欣向荣</span></h1>
    <a href="https://ipw.cn/ssl/?site=www.xctcn.cn" title="本站支持SSL安全访问" target='_blank'><img style='display:inline-block;vertical-align:middle' alt="本站支持SSL安全访问" src="https://static.ipw.cn/icon/ssl-s4.svg"></a>
    <div class="card">
        <p>🎉破天工作室服务器管理系统5.0</p>
        <p>🎊命令行服务器提供简单的操作</p>
        <p>✨下载要求：能正常运行Windows系统的电脑</p>
        <p>🎈开发语言：易语言</p>
        <p>❗登录系统我们还没有完善，登录请勿修改用户名，使用默认密码admin登录</p>
        <li>一款集成各种开发工具的WindowsSEVER服务器系统命令行安装使用桌面管理环境的系统UI 目前此项目在持续在开发中。<li>
        <?php if ($auth->isLoggedIn()): ?>
            <div class="download-links">
                <a href="https://www.bilibili.com/video/BV1Nm4y1a7sf/?spm_id_from=333.999.0.0" style="display:inline-block; padding:10px 20px; text-decoration:none; color:white; background-color:#1e90ff; border-radius:5px;">跳转到详情</a>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="download_type" value="server_management">
                    <input type="hidden" name="download_url" value="https://pengos.lanzouv.com/b0zjaq2eh">
                    <button type="submit" name="download" style="display:inline-block; padding:10px 20px; text-decoration:none; color:white; background-color:#1e90ff; border-radius:5px; border:none; cursor:pointer;">
                        下载（提取码1234）
                    </button>
                </form>
            </div>
        <?php else: ?>
            <p style="color: #ff6b6b;">请<a href="login.php" style="color: #4dabf7;">登录</a>后查看下载链接</p>
        <?php endif; ?>
    </div>
    <div class="card">
        <p>🎉黎明系统急救箱</p>
        <p>🎊一次安装 即可在关键时刻 连续按5次Shift按键 快速对您的电脑进行操作</p>
        <p>🎈开发语言及运行环境：易语言win-vista Win7 win8</p>
        <p>❗首次使用需要在主管理员用户安装</p>
        <li>此项目在安装后即在需要使用时按下键盘的5次Shift按键 启动工具箱，可在登录界面使用 登陆界面启动提权操作 以便用户忘记密码时快速修改 <li>
        <li>此项目是纯本地端 全程不会联网 因此用户不需要担心隐私问题，同时在安装和使用软件某些功能时需要 同意许可条款 若您不同意我们的条款 请卸载该软件<li></li>
        <?php if ($auth->isLoggedIn()): ?>
            <div class="download-links">
                <a href="https://www.bilibili.com/video/BV1Nm4y1a7sf/?spm_id_from=333.999.0.0" style="display:inline-block; padding:10px 20px; text-decoration:none; color:white; background-color:#1e90ff; border-radius:5px;">跳转到详情</a>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="download_type" value="system_rescue">
                    <input type="hidden" name="download_url" value="https://pengos.lanzouv.com/b0zjaq2fi">
                    <button type="submit" name="download" style="display:inline-block; padding:10px 20px; text-decoration:none; color:white; background-color:#1e90ff; border-radius:5px; border:none; cursor:pointer;">
                        下载（提取码1234）
                    </button>
                </form>
            </div>
        <?php else: ?>
            <p style="color: #ff6b6b;">请<a href="login.php" style="color: #4dabf7;">登录</a>后查看下载链接</p>
        <?php endif; ?>
    </div>
    <div class="card">
        <p>🎉自定义Ping包测试工具</p>
        <p>🎊轻量小巧</p>
        <p>✨集群免费下载</p>
        <p>🎈开发语言：易语言</p>
        <li>一款自定义ping包测试软件，可输入IP地址和数据包大小进行多线程ping，测试服务器带宽反应。运行软件后，后台流量反馈导致服务器带宽不足，鼠标反应延迟，最终卡死。<li>
        <?php if ($auth->isLoggedIn()): ?>
            <div class="download-links">
                <a href="https://www.bilibili.com/video/BV1Nm4y1a7sf/?spm_id_from=333.999.0.0" style="display:inline-block; padding:10px 20px; text-decoration:none; color:white; background-color:#1e90ff; border-radius:5px;">跳转到详情</a>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="download_type" value="ping_tool">
                    <input type="hidden" name="download_url" value="https://pengos.lanzouv.com/b0zjaq2ha">
                    <button type="submit" name="download" style="display:inline-block; padding:10px 20px; text-decoration:none; color:white; background-color:#1e90ff; border-radius:5px; border:none; cursor:pointer;">
                        下载（提取码1234）
                    </button>
                </form>
            </div>
        <?php else: ?>
            <p style="color: #ff6b6b;">请<a href="login.php" style="color: #4dabf7;">登录</a>后查看下载链接</p>
        <?php endif; ?>
    </div>
    <div class="card">
        <H1>公告区：</H1>
        <p>近期官网遭遇大量的DDOS 和非法CC攻击 包括我们的官网资料和数据全部丢失</p>
        <p>官网现在处于不稳定中</p>
        <p>维护时间:每周2、4、6 停服维护</p>
        <li>在此给造成的不便我们团队深感抱歉<li>
            <a href="./lxwm/" style="display:inline-block; padding:10px 20px; text-decoration:none; color:white; background-color:#1e90ff; border-radius:5px;">联系我们</a>
    </div>
</div> 

<footer>
    <div class="container">
        <div>
            <h3>联系我们</h3>
            <p>Skype电话: live:.cid.b2b82e6411f4060e</p>
            <p>客服咨询QQ: 3190746820</p>
            <p>邮箱: potiankeji2022@163.com</p>
            <p>地址: 萌国 萌初市 文化信息产业园 1208</p>
        </div>
        <div>
            <h3>产品</h3>
            <p><a href="#">服务器管理系统</a></p>
            <p><a href="#">黎明开放论坛</a></p>
            <p><a href="#">自定义服务</a></p>
        </div>
        <div>
            <h3>支持</h3>
            <p><a href="#">技术支持</a></p>
            <p><a href="#">解决方案</a></p>
            <p><a href="#">帮助中心</a></p>
        </div>
        <div>
            <h3>更多信息</h3>
            <p><a href="#">关于我们</a></p>
            <p><a href="#">合作伙伴</a></p>
            <p><a href="#">加入我们</a></p>
        </div>
        <div>
            <h3>了解我们</h3>
            <p><a href="#">破天官方交流Q群1: 734555740</a></p>
            <p><a href="https://space.bilibili.com/541002925">破天官方哔哩哔哩</a></p>
            <p><a href="https://v.douyin.com/iAMXt2RM/">破天官方抖音</a></p>
            
        </div>
    </div>
    <p><span id="runtime_span"></span></p>
    <div class="mt-4 body-tertiary py-3 text-center"><div class="w-100"><p align="center"> <a href="https://ipw.cn/ssl/?site=www.xctcn.cn" title="本站支持SSL安全访问" target='_blank'><img style='display:inline-block;vertical-align:middle' alt="本站支持SSL安全访问" src="https://static.ipw.cn/icon/ssl-s4.svg"></a><a href="https://icp.gov.moe/?keyword=20238821" title="萌ICP备20238821号" target="_blank">萌ICP备20238821号</a>丨<a href="https://www.gyfzlm.com/fanzha/NO-20240119.html" title="公益反诈联盟成员单位" target="_blank">公益反诈联盟成员单位</a>
</footer>


<script>
    const randomColor = '#' + Math.floor(Math.random()*16777215).toString(16);
    document.getElementById('dynamicColor').style.color = randomColor;
</script>
    <!-- 网站运行时间var计算代码 -->
    <script type="text/javascript">
        function show_runtime() {
            window.setTimeout(show_runtime, 1000);
            var X = new Date("11/04/2023 0:15:00");
            var Y = new Date();
            var T = (Y.getTime() - X.getTime());
            var M = 24 * 60 * 60 * 1000;
            var a = T / M;
            var A = Math.floor(a);
            var b = (a - A) * 24;
            var B = Math.floor(b);
            var c = (b - B) * 60;
            var C = Math.floor((b - B) * 60);
            var D = Math.floor((c - C) * 60);
            document.getElementById("runtime_span").innerHTML = "本站已经运行: " + A + "天" + B + "小时" + C + "分" + D + "秒";
        }
        show_runtime();
    </script>


</body>
</html>
