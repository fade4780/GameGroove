<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . 'pages/login.php');
    exit;
}

$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $db->query($categories_query);
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $goal_amount = floatval(str_replace(' ', '', $_POST['goal_amount'] ?? 0));
    $duration = intval($_POST['duration'] ?? 0);

    // Валидация
    if (empty($title)) {
        $error = 'Введите название проекта';
    } elseif (empty($description)) {
        $error = 'Введите описание проекта';
    } elseif ($category_id <= 0) {
        $error = 'Выберите категорию';
    } elseif ($goal_amount <= 0) {
        $error = 'Введите корректную сумму финансирования';
    } elseif ($duration <= 0) {
        $error = 'Введите корректную длительность проекта';
    } else {
        // Обработка загрузки изображения
        $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_extension, $allowed_extensions)) {
                $error = 'Допустимые форматы изображений: JPG, PNG, GIF';
            } else {
                $filename = uniqid() . '.' . $file_extension;
                $filepath = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                    $image_url = 'uploads/' . $filename;
                } else {
                    $error = 'Ошибка при загрузке изображения';
                }
            }
        }

        if (empty($error)) {
            $query = "INSERT INTO projects (title, description, category_id, goal_amount, current_amount, duration, image_url, developer_id, created_at) 
                     VALUES (?, ?, ?, ?, 0, ?, ?, ?, NOW())";
            
            if ($stmt = $db->prepare($query)) {
                $stmt->bind_param("ssiisis", $title, $description, $category_id, $goal_amount, $duration, $image_url, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $success = 'Проект успешно создан!';
                    // Перенаправляем на страницу проекта
                    header('Location: ' . SITE_URL . 'pages/project.php?id=' . $db->insert_id);
                    exit;
                } else {
                    $error = 'Ошибка при создании проекта: ' . $db->error;
                }
                $stmt->close();
            } else {
                $error = 'Ошибка при подготовке запроса: ' . $db->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание проекта - GameGroove</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo SITE_URL; ?>" class="logo">
                    <i class="ri-gamepad-line"></i>
                    <span>GameGroove</span>
                </a>
                <nav class="nav">
                    <a href="<?php echo SITE_URL; ?>" class="nav-link">Главная</a>
                    <a href="<?php echo SITE_URL; ?>pages/projects.php" class="nav-link">Проекты</a>
                    <a href="<?php echo SITE_URL; ?>pages/create_project.php" class="nav-link active">Создать проект</a>
                </nav>
                <div class="header-actions">
                    <a href="<?php echo SITE_URL; ?>pages/profile.php" class="btn btn-outline">Профиль</a>
                    <a href="<?php echo SITE_URL; ?>pages/logout.php" class="btn btn-primary">Выйти</a>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="form-container">
                <h1>Создание проекта</h1>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" class="form">
                    <div class="form-group">
                        <label for="title">Название проекта</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Категория</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Выберите категорию</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Описание проекта</label>
                        <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="goal_amount">Сумма финансирования (₽)</label>
                        <input type="number" id="goal_amount" name="goal_amount" value="<?php echo htmlspecialchars($_POST['goal_amount'] ?? ''); ?>" min="1000" required>
                    </div>

                    <div class="form-group">
                        <label for="duration">Длительность кампании (в днях)</label>
                        <input type="number" id="duration" name="duration" value="<?php echo htmlspecialchars($_POST['duration'] ?? ''); ?>" min="1" max="90" required>
                    </div>

                    <div class="form-group">
                        <label for="image">Изображение проекта</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                        <p class="help-text">Рекомендуемый размер: 1280x720 пикселей. Максимальный размер: 5 МБ</p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Создать проект</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="ri-gamepad-line"></i>
                    <span>GameGroove</span>
                </div>
                <div class="footer-links">
                    <a href="<?php echo SITE_URL; ?>pages/about.php">О нас</a>
                    <a href="<?php echo SITE_URL; ?>pages/terms.php">Условия использования</a>
                    <a href="<?php echo SITE_URL; ?>pages/privacy.php">Политика конфиденциальности</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html> 