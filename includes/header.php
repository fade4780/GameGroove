<?php
require_once __DIR__ . '/config.php';
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameGroove - Платформа для инди-игр</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="logo">
                    <i class="ri-gamepad-line"></i>
                    <a href="<?php echo SITE_URL; ?>">GameGroove</a>
                </div>
                <button class="menu-toggle" aria-label="Открыть меню">
                    <i class="ri-menu-line"></i>
                </button>
                <ul class="nav-menu">
                    <li><a href="<?php echo SITE_URL; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">Главная</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/projects.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'projects.php' ? 'active' : ''; ?>">Проекты</a></li>
                    <?php if (isDeveloper()): ?>
                    <li><a href="<?php echo SITE_URL; ?>/pages/create_project.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'create_project.php' ? 'active' : ''; ?>">Создать проект</a></li>
                    <?php endif; ?>
                    <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo SITE_URL; ?>/pages/profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">Профиль</a></li>
                    <?php if (isAdmin()): ?>
                    <li><a href="<?php echo SITE_URL; ?>/pages/admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'admin.php' ? 'active' : ''; ?>">
                        <i class="ri-settings-line"></i>
                        Админ-панель
                    </a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo SITE_URL; ?>/pages/logout.php">Выход</a></li>
                    <?php else: ?>
                    <li><a href="<?php echo SITE_URL; ?>/pages/login.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'login.php' ? 'active' : ''; ?>">Вход</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/register.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'register.php' ? 'active' : ''; ?>">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.nav');
    
    if (menuToggle && nav) {
        menuToggle.addEventListener('click', function() {
            nav.classList.toggle('active');
        });
    }
});
</script> 