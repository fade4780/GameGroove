<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Получаем параметры фильтрации
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Базовый SQL запрос
$sql = "SELECT p.*, c.name as category_name, u.username as developer_name,
        (SELECT COUNT(*) FROM investments WHERE project_id = p.id) as investors_count,
        (SELECT image_url FROM project_images WHERE project_id = p.id AND is_main = 1 LIMIT 1) as main_image
        FROM projects p 
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.user_id = u.id
        WHERE p.status = 'active'";

// Добавляем фильтр по категории
if ($category_id) {
    $sql .= " AND p.category_id = " . $category_id;
}

// Добавляем сортировку
switch ($sort) {
    case 'popular':
        $sql .= " ORDER BY investors_count DESC";
        break;
    case 'funded':
        $sql .= " ORDER BY (p.current_amount / p.goal_amount) DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.created_at DESC";
}

$projects = $db->query($sql);

if (!$projects) {
    die('Ошибка запроса: ' . $db->error);
}

$projects = $projects->fetch_all(MYSQLI_ASSOC);

// Получаем все категории для фильтра
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проекты - GameGroove</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="main">
        <div class="container">
            <div class="projects-header">
                <h1>Игровые проекты</h1>
                <div class="projects-filters">
                    <div class="filter-group">
                        <label>Категория:</label>
                        <select onchange="window.location.href='?category=' + this.value + '&sort=<?php echo $sort; ?>'">
                            <option value="">Все категории</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Сортировка:</label>
                        <select onchange="window.location.href='?category=<?php echo $category_id; ?>&sort=' + this.value">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Сначала новые</option>
                            <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>По популярности</option>
                            <option value="funded" <?php echo $sort == 'funded' ? 'selected' : ''; ?>>По % финансирования</option>
                        </select>
                    </div>
                </div>
            </div>

            <?php if (empty($projects)): ?>
                <div class="no-projects">
                    <i class="ri-game-line"></i>
                    <h2>Проекты не найдены</h2>
                    <p>По вашему запросу не найдено ни одного проекта</p>
                    <?php if ($category_id): ?>
                        <a href="<?php echo SITE_URL; ?>/pages/projects.php" class="btn-primary">Показать все проекты</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="projects-grid">
                    <?php foreach ($projects as $project): ?>
                        <div class="project-card">
                            <div class="project-image">
                                <img src="<?php echo $project['main_image'] ? '../' . $project['main_image'] : '../assets/images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($project['title']); ?>">
                                <div class="project-category"><?php echo htmlspecialchars($project['category_name']); ?></div>
                            </div>
                            <div class="project-info">
                                <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                <p class="project-developer">
                                    <i class="ri-user-line"></i>
                                    <?php echo htmlspecialchars($project['developer_name']); ?>
                                </p>
                                <p class="project-description"><?php echo mb_substr(htmlspecialchars($project['description']), 0, 100); ?>...</p>
                                <div class="project-stats">
                                    <div class="stat">
                                        <i class="ri-money-dollar-circle-line"></i>
                                        <span><?php echo number_format((float)$project['current_amount'], 0, '.', ' '); ?> ₽</span>
                                        <small>из <?php echo number_format((float)$project['goal_amount'], 0, '.', ' '); ?> ₽</small>
                                    </div>
                                    <div class="stat">
                                        <i class="ri-group-line"></i>
                                        <span><?php echo $project['investors_count']; ?></span>
                                        <small>инвесторов</small>
                                    </div>
                                </div>
                                <div class="project-progress">
                                    <div class="progress-bar">
                                        <div class="progress" style="width: <?php 
                                            $percentage = $project['goal_amount'] > 0 ? 
                                                min(100, ((float)$project['current_amount'] / (float)$project['goal_amount']) * 100) : 0;
                                            echo $percentage;
                                        ?>%"></div>
                                    </div>
                                    <span class="progress-text">
                                        <?php 
                                            $percentage = $project['goal_amount'] > 0 ? 
                                                round(((float)$project['current_amount'] / (float)$project['goal_amount']) * 100) : 0;
                                            echo $percentage;
                                        ?>%
                                    </span>
                                </div>
                                <a href="project.php?id=<?php echo $project['id']; ?>" class="btn-primary btn-block">
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 