<?php
session_start();
require_once 'classes/Captcha.php';
Captcha::generate();
?> 