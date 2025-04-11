<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Получаем ID проекта
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем данные проекта
$project = $db->query("
    SELECT p.*, u.username as developer_name, c.name as category_name 
    FROM projects p 
    JOIN users u ON p.developer_id = u.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = $project_id
")->fetch_assoc();

if (!$project) {
    header('Location: projects.php');
    exit();
}

// Получаем комментарии к проекту
$comments = $db->query("
    SELECT c.*, u.username as author_name 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.project_id = $project_id 
    ORDER BY c.created_at DESC
");

// Обработка добавления комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_POST['comment'])) {
    $user_id = $_SESSION['user_id'];
    $comment = $db->real_escape_string($_POST['comment']);
    
    $db->query("INSERT INTO comments (project_id, user_id, content) VALUES ($project_id, $user_id, '$comment')");
    header("Location: project.php?id=$project_id");
    exit();
}

// Обработка инвестиции
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_POST['amount'])) {
    $user_id = $_SESSION['user_id'];
    $amount = (float)$_POST['amount'];
    
    if ($amount > 0) {
        $db->query("INSERT INTO investments (project_id, investor_id, amount) VALUES ($project_id, $user_id, $amount)");
        $db->query("UPDATE projects SET current_funding = current_funding + $amount WHERE id = $project_id");
        header("Location: project.php?id=$project_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['title']); ?> - GameGroove</title>
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
                <a href="projects.php" class="nav-link active">
                    <i class="ri-game-line"></i>
                    Проекты
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php 
                    // Получаем роль пользователя
                    $user = $db->query("SELECT role FROM users WHERE id = " . $_SESSION['user_id'])->fetch_assoc();
                    ?>
                    <a href="dashboard.php" class="nav-link">
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
                <?php else: ?>
                    <a href="login.php" class="nav-link">
                        <i class="ri-login-box-line"></i>
                        Войти
                    </a>
                    <a href="register.php" class="nav-link">
                        <i class="ri-user-add-line"></i>
                        Регистрация
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <article class="project-details">
            <h1><?php echo htmlspecialchars($project['title']); ?></h1>
            <p class="project-meta">
                Разработчик: <?php echo htmlspecialchars($project['developer_name']); ?> | 
                Категория: <?php echo htmlspecialchars($project['category_name']); ?>
            </p>
            
            <?php if ($project['image_path']): ?>
                <div class="project-image">
                    <img src="../<?php echo htmlspecialchars($project['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($project['title']); ?>">
                </div>
            <?php endif; ?>
            
            <div class="project-description">
                <?php echo nl2br(htmlspecialchars($project['description'])); ?>
            </div>
            
            <div class="funding-info">
                <div class="progress-bar">
                    <div class="progress" style="width: <?php 
                        echo min(100, ($project['current_funding'] / $project['funding_goal']) * 100); 
                    ?>%"></div>
                </div>
                <p>
                    Собрано: <?php echo number_format($project['current_funding'], 0, ',', ' '); ?> ₽ 
                    из <?php echo number_format($project['funding_goal'], 0, ',', ' '); ?> ₽
                </p>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
            <section class="investment-form">
                <h2>Инвестировать в проект</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="amount">Сумма инвестиции (₽):</label>
                        <input type="number" id="amount" name="amount" min="1" step="1" required>
                    </div>
                    <button type="submit" class="button">Инвестировать</button>
                </form>
            </section>
            <?php endif; ?>

            <section class="comments">
                <h2>Комментарии</h2>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" class="comment-form">
                    <div class="form-group">
                        <label for="comment">Ваш комментарий:</label>
                        <textarea id="comment" name="comment" required></textarea>
                    </div>
                    <button type="submit" class="button">Отправить</button>
                </form>
                <?php endif; ?>

                <div class="comments-list">
                    <?php while ($comment = $comments->fetch_assoc()): ?>
                        <div class="comment">
                            <p class="comment-author"><?php echo htmlspecialchars($comment['author_name']); ?></p>
                            <p class="comment-date"><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?></p>
                            <p class="comment-content"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        </article>
    </main>

    <footer>
        <p>&copy; 2024 GameGroove. Все права защищены.</p>
    </footer>
</body>
</html> 