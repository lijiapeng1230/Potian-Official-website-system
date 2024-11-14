<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
$auth = new Auth($pdo);
$auth->logout();
header('Location: login.php');
exit;
?> 