<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "Пароли не совпадают";
    } else {
        if (register_user($username, $password)) {
            header('Location: ' . SITE_URL . '/pages/login.php');
            exit;
        } else {
            $error = "Ошибка при регистрации. Возможно, пользователь с таким логином уже существует.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - GameGroove</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-background">
        <div class="auth-bubbles">
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
        </div>
    </div>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-card-header">
                <h1 class="auth-title">Создать аккаунт</h1>
                <p class="auth-subtitle">Присоединяйтесь к GameGroove</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert-error">
                    <i class="ri-error-warning-line"></i>
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Логин</label>
                    <div class="input-group">
                        <i class="ri-user-line"></i>
                        <input type="text" id="username" name="username" required placeholder="Придумайте логин">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <div class="input-group">
                        <i class="ri-lock-line"></i>
                        <input type="password" id="password" name="password" required placeholder="Придумайте пароль">
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Подтвердите пароль</label>
                    <div class="input-group">
                        <i class="ri-lock-line"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Повторите пароль">
                    </div>
                </div>
                <button type="submit" name="register" class="btn-block">
                    <span>Зарегистрироваться</span>
                    <i class="ri-arrow-right-line"></i>
                </button>
            </form>

            <div class="auth-divider">
                <span>или</span>
            </div>

            <div class="social-login">
                <button class="btn-social">
                    <i class="ri-google-fill"></i>
                    Зарегистрироваться через Google
                </button>
                <button class="btn-social">
                    <i class="ri-github-fill"></i>
                    Зарегистрироваться через GitHub
                </button>
            </div>

            <p class="auth-footer">
                Уже есть аккаунт? <a href="<?php echo SITE_URL; ?>/pages/login.php">Войти</a>
            </p>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 