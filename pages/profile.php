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

// Проверяем и создаем необходимые таблицы
$tables = [
    "project_images" => "
        CREATE TABLE IF NOT EXISTS project_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id INT NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            is_main BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
        )
    ",
    "investments" => "
        CREATE TABLE IF NOT EXISTS investments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id INT NOT NULL,
            investor_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
            FOREIGN KEY (investor_id) REFERENCES users(id) ON DELETE CASCADE
        )
    "
];

foreach ($tables as $table_name => $create_query) {
    $check_table = $db->query("SHOW TABLES LIKE '$table_name'");
    if ($check_table->num_rows === 0) {
        if (!$db->query($create_query)) {
            error_log("Error creating $table_name table: " . $db->error);
            die("Ошибка при создании таблицы $table_name");
        }
    }
}

// Получаем проекты пользователя
$user_projects_query = "
    SELECT p.*, 
           (SELECT image_url FROM project_images WHERE project_id = p.id AND is_main = 1 LIMIT 1) as main_image,
           (SELECT COUNT(*) FROM investments WHERE project_id = p.id) as investors_count,
           (SELECT COALESCE(SUM(amount), 0) FROM investments WHERE project_id = p.id) as current_funding
    FROM projects p
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
";

$user_projects_stmt = $db->prepare($user_projects_query);
if ($user_projects_stmt === false) {
    error_log("Error preparing projects query: " . $db->error);
    die("Ошибка при подготовке запроса проектов: " . $db->error);
}

$user_projects_stmt->bind_param('i', $user_id);
if (!$user_projects_stmt->execute()) {
    error_log("Error executing projects query: " . $user_projects_stmt->error);
    die("Ошибка при выполнении запроса проектов: " . $user_projects_stmt->error);
}

$user_projects = $user_projects_stmt->get_result();
if ($user_projects === false) {
    error_log("Error getting projects result: " . $db->error);
    die("Ошибка при получении результатов запроса проектов: " . $db->error);
}

// Получаем инвестиции пользователя
$user_investments_query = "
    SELECT i.*, 
           p.title as project_title,
           p.status as project_status,
           u.username as project_author,
           (SELECT image_url FROM project_images WHERE project_id = p.id AND is_main = 1 LIMIT 1) as project_image
    FROM investments i
    JOIN projects p ON i.project_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE i.investor_id = ?
    ORDER BY i.created_at DESC
";
$user_investments_stmt = $db->prepare($user_investments_query);
if ($user_investments_stmt === false) {
    error_log("Error preparing investments query: " . $db->error);
    die("Ошибка при подготовке запроса инвестиций");
}
$user_investments_stmt->bind_param('i', $user_id);
if (!$user_investments_stmt->execute()) {
    error_log("Error executing investments query: " . $user_investments_stmt->error);
    die("Ошибка при выполнении запроса инвестиций");
}
$user_investments = $user_investments_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль - GameGroove</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/profile.css">
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

                <div class="profile-projects">
                    <h2>Мои проекты</h2>
                    <?php if ($user_projects->num_rows > 0): ?>
                        <div class="projects-grid">
                            <?php while ($project = $user_projects->fetch_assoc()): ?>
                                <div class="project-card">
                                    <div class="project-image">
                                        <img src="<?php echo $project['main_image'] ?: SITE_URL . '/assets/images/default-project.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($project['title']); ?>">
                                    </div>
                                    <div class="project-info">
                                        <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                        <p class="project-description"><?php echo htmlspecialchars($project['description']); ?></p>
                                        <div class="project-stats">
                                            <div class="stat">
                                                <i class="ri-group-line"></i>
                                                <span><?php echo $project['investors_count']; ?> инвесторов</span>
                                            </div>
                                            <div class="stat">
                                                <i class="ri-money-dollar-circle-line"></i>
                                                <span><?php echo number_format($project['current_funding'] ?? 0, 0, ',', ' '); ?> ₽</span>
                                            </div>
                                        </div>
                                        <div class="project-actions">
                                            <a href="<?php echo SITE_URL; ?>/pages/project.php?id=<?php echo $project['id']; ?>" 
                                               class="btn btn-primary">
                                                <i class="ri-eye-line"></i>
                                                Подробнее
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-projects">У вас пока нет проектов</p>
                    <?php endif; ?>
                </div>

                <div class="profile-investments">
                    <h2>Мои инвестиции</h2>
                    <?php if ($user_investments->num_rows > 0): ?>
                        <div class="investments-grid">
                            <?php while ($investment = $user_investments->fetch_assoc()): ?>
                                <div class="investment-card">
                                    <div class="investment-image">
                                        <img src="<?php echo $investment['project_image'] ?: SITE_URL . '/assets/images/default-project.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($investment['project_title']); ?>">
                                    </div>
                                    <div class="investment-info">
                                        <h3><?php echo htmlspecialchars($investment['project_title']); ?></h3>
                                        <p class="investment-author">Автор: <?php echo htmlspecialchars($investment['project_author']); ?></p>
                                        <div class="investment-details">
                                            <div class="detail">
                                                <i class="ri-money-dollar-circle-line"></i>
                                                <span>Сумма: <?php echo number_format($investment['amount'], 0, ',', ' '); ?> ₽</span>
                                            </div>
                                            <div class="detail">
                                                <i class="ri-calendar-line"></i>
                                                <span>Дата: <?php echo date('d.m.Y', strtotime($investment['created_at'])); ?></span>
                                            </div>
                                            <div class="detail">
                                                <i class="ri-information-line"></i>
                                                <span class="status-badge <?php echo $investment['project_status']; ?>">
                                                    <?php 
                                                    switch($investment['project_status']) {
                                                        case 'active':
                                                            echo 'Активный';
                                                            break;
                                                        case 'completed':
                                                            echo 'Завершен';
                                                            break;
                                                        case 'cancelled':
                                                            echo 'Отменен';
                                                            break;
                                                        default:
                                                            echo 'Неизвестно';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="investment-actions">
                                            <a href="<?php echo SITE_URL; ?>/pages/project.php?id=<?php echo $investment['project_id']; ?>" class="btn btn-primary">
                                                <i class="ri-eye-line"></i>
                                                К проекту
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-investments">У вас пока нет инвестиций</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 