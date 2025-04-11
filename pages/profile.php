<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

// Получаем данные пользователя
$user_id = getUserId();
$stmt = $db->prepare("SELECT username, role, created_at FROM users WHERE id = ?");
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $db->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("Пользователь не найден");
}

// Получаем проекты пользователя
$stmt = $db->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC");
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $db->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Получаем инвестиции пользователя
$stmt = $db->prepare("
    SELECT p.title, p.image_url, i.amount, i.created_at 
    FROM investments i 
    JOIN projects p ON i.project_id = p.id 
    WHERE i.investor_id = ? 
    ORDER BY i.created_at DESC
");
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $db->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$investments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль - GameGroove</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/styles/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="main">
        <div class="container">
            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="ri-user-fill"></i>
                    </div>
                    <div class="profile-info">
                        <h1 class="profile-username"><?php echo e($user['username']); ?></h1>
                        <p class="profile-role"><?php echo ucfirst($user['role']); ?></p>
                        <p class="profile-date">На платформе с <?php echo date('d.m.Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <div class="profile-actions">
                        <?php if ($user['role'] === 'user'): ?>
                            <a href="<?php echo SITE_URL; ?>/pages/become_developer.php" class="btn btn-primary">
                                <i class="ri-code-box-line"></i>
                                Стать разработчиком
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="btn btn-secondary">
                            <i class="ri-logout-box-line"></i>
                            Выйти
                        </a>
                    </div>
                </div>

                <?php if (!empty($projects)): ?>
                <div class="profile-section">
                    <h2 class="section-title">Мои проекты</h2>
                    <div class="projects-grid">
                        <?php foreach ($projects as $project): ?>
                            <div class="project-card">
                                <div class="project-image">
                                    <img src="<?php echo $project['image_url'] ?: SITE_URL . '/assets/images/placeholder.jpg'; ?>" alt="<?php echo e($project['title']); ?>">
                                </div>
                                <div class="project-info">
                                    <h3 class="project-title"><?php echo e($project['title']); ?></h3>
                                    <div class="project-stats">
                                        <div class="stat">
                                            <i class="ri-money-dollar-circle-line"></i>
                                            <span><?php echo number_format($project['current_amount'], 0, '.', ' '); ?> ₽</span>
                                        </div>
                                        <div class="stat">
                                            <i class="ri-time-line"></i>
                                            <span><?php echo $project['duration']; ?> дней</span>
                                        </div>
                                    </div>
                                    <a href="<?php echo SITE_URL; ?>/pages/project.php?id=<?php echo $project['id']; ?>" class="btn btn-secondary">
                                        Подробнее
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($investments)): ?>
                <div class="profile-section">
                    <h2 class="section-title">Мои инвестиции</h2>
                    <div class="investments-list">
                        <?php foreach ($investments as $investment): ?>
                            <div class="investment-card">
                                <div class="investment-image">
                                    <img src="<?php echo $investment['image_url'] ?: SITE_URL . '/assets/images/placeholder.jpg'; ?>" alt="<?php echo e($investment['title']); ?>">
                                </div>
                                <div class="investment-info">
                                    <h3 class="investment-title"><?php echo e($investment['title']); ?></h3>
                                    <div class="investment-amount">
                                        <i class="ri-money-dollar-circle-line"></i>
                                        <span><?php echo number_format($investment['amount'], 0, '.', ' '); ?> ₽</span>
                                    </div>
                                    <p class="investment-date">
                                        <?php echo date('d.m.Y', strtotime($investment['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 