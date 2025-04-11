<?php
session_start();
require_once '../includes/config.php';

// Очищаем сессию
session_destroy();

// Перенаправляем на главную страницу
header('Location: ' . SITE_URL . '/index.php');
exit; 