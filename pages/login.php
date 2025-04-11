<?php
// Проверяем статус сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/db.php';

$error = null;

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    error_log("Получены данные для входа: username=" . $username);
    
    if (empty($username) || empty($password)) {
        $error = "Пожалуйста, заполните все поля";
        error_log("Пустые поля при попытке входа");
    } else {
        if (login_user($username, $password)) {
            error_log("Перенаправление на главную страницу");
            header('Location: ' . SITE_URL);
            exit;
        } else {
            $error = "Неверный логин или пароль";
            error_log("Ошибка входа для пользователя: " . $username);
        }
    }
}

// Проверяем, авторизован ли пользователь
if (isset($_SESSION['user_id'])) {
    error_log("Пользователь уже авторизован, перенаправление на главную");
    header('Location: ' . SITE_URL);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="auth-page">
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="logo">
                    <i class="ri-gamepad-line"></i>
                    <a href="<?php echo SITE_URL; ?>">GameGroove</a>
                </div>
                <ul class="nav-menu">
                    <li><a href="<?php echo SITE_URL; ?>">Главная</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/projects.php">Проекты</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-card-header">
                        <h1 class="auth-title">Добро пожаловать!</h1>
                        <p class="auth-subtitle">Войдите в свой аккаунт, чтобы продолжить</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="ri-error-warning-line"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="auth-form">
                        <div class="form-group">
                            <label for="username">Логин</label>
                            <div class="input-group">
                                <i class="ri-user-line"></i>
                                <input type="text" id="username" name="username" required placeholder="Введите ваш логин" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Пароль</label>
                            <div class="input-group">
                                <i class="ri-lock-line"></i>
                                <input type="password" id="password" name="password" required placeholder="Введите ваш пароль">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-container">
                                <input type="checkbox" id="remember" name="remember">
                                <span class="checkmark"></span>
                                Запомнить меня
                            </label>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary">
                            <span>Войти</span>
                            <i class="ri-arrow-right-line"></i>
                        </button>
                    </form>

                    <div class="auth-divider">
                        <span>или</span>
                    </div>

                    <div class="social-login">
                        <button class="btn btn-social">
                            <i class="ri-google-fill"></i>
                            Войти через Google
                        </button>
                        <button class="btn btn-social">
                            <i class="ri-github-fill"></i>
                            Войти через GitHub
                        </button>
                    </div>

                    <p class="auth-footer">
                        Нет аккаунта? <a href="<?php echo SITE_URL; ?>/pages/register.php">Зарегистрироваться</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 