<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Получаем данные пользователя
$user_id = $_SESSION['user_id'];
$user = $db->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Получаем проекты пользователя (если он разработчик)
$projects = $db->query("SELECT * FROM projects WHERE developer_id = $user_id");

// Получаем инвестиции пользователя (если он инвестор)
$investments = $db->query("
    SELECT i.*, p.title as project_title, p.description 
    FROM investments i 
    JOIN projects p ON i.project_id = p.id 
    WHERE i.investor_id = $user_id
");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - GameGroove</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav>
            <a href="/" class="nav-logo">
                <i class="ri-gamepad-line"></i>
                GameGroove
            </a>
            <div class="nav-links">
                <a href="/" class="nav-link">
                    <i class="ri-home-line"></i>
                    Главная
                </a>
                <a href="projects.php" class="nav-link">
                    <i class="ri-game-line"></i>
                    Проекты
                </a>
                <a href="dashboard.php" class="nav-link active">
                    <i class="ri-user-line"></i>
                    Личный кабинет
                </a>
                <?php if ($user['role'] === 'admin'): ?>
                <a href="admin.php" class="nav-link">
                    <i class="ri-settings-line"></i>
                    Админ-панель
                </a>
                <?php endif; ?>
                <a href="logout.php" class="nav-link">
                    <i class="ri-logout-box-line"></i>
                    Выйти
                </a>
            </div>
        </nav>
    </header>

    <main>
        <h1>Личный кабинет</h1>
        
        <section class="user-info">
            <h2>Информация о пользователе</h2>
            <p>Имя пользователя: <?php echo htmlspecialchars($user['username']); ?></p>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            <p>Роль: <?php echo htmlspecialchars($user['role']); ?></p>
        </section>

        <?php if ($user['role'] == 'admin' || $projects->num_rows > 0): ?>
        <section class="user-projects">
            <h2>Ваши проекты</h2>
            <?php while ($project = $projects->fetch_assoc()): ?>
                <div class="project-card">
                    <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                    <p><?php echo htmlspecialchars($project['description']); ?></p>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?php 
                            echo min(100, ($project['current_funding'] / $project['funding_goal']) * 100); 
                        ?>%"></div>
                    </div>
                    <p>Собрано: <?php echo number_format($project['current_funding'], 0, ',', ' '); ?> ₽ 
                       из <?php echo number_format($project['funding_goal'], 0, ',', ' '); ?> ₽</p>
                    <a href="project.php?id=<?php echo $project['id']; ?>" class="button">Подробнее</a>
                </div>
            <?php endwhile; ?>
            
            <?php if ($user['role'] == 'admin' || $user['role'] == 'user'): ?>
                <a href="create_project.php" class="button">Создать новый проект</a>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <?php if ($investments->num_rows > 0): ?>
        <section class="user-investments">
            <h2>Ваши инвестиции</h2>
            <?php while ($investment = $investments->fetch_assoc()): ?>
                <div class="investment-card">
                    <h3><?php echo htmlspecialchars($investment['project_title']); ?></h3>
                    <p>Сумма инвестиции: <?php echo number_format($investment['amount'], 0, ',', ' '); ?> ₽</p>
                    <p>Дата: <?php echo date('d.m.Y', strtotime($investment['created_at'])); ?></p>
                    <a href="project.php?id=<?php echo $investment['project_id']; ?>" class="button">Перейти к проекту</a>
                </div>
            <?php endwhile; ?>
        </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 GameGroove. Все права защищены.</p>
    </footer>
</body>
</html> 