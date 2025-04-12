<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Проверяем авторизацию и права разработчика
if (!isLoggedIn() || !isDeveloper()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

// Получаем категории
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

    // Отладочная информация
    error_log('Debug: POST request received');
    error_log('Debug: Files in $_FILES: ' . print_r($_FILES, true));

    // Валидация
    if (empty($title)) {
        $error = 'Введите название проекта';
    } elseif (empty($description)) {
        $error = 'Введите описание проекта';
    } elseif ($category_id <= 0) {
        $error = 'Выберите категорию';
    } elseif ($goal_amount <= 0) {
        $error = 'Введите корректную сумму финансирования';
    } elseif ($duration <= 0 || $duration > 90) {
        $error = 'Длительность кампании должна быть от 1 до 90 дней';
    } else {
        // Обработка загрузки изображений
        $image_urls = [];
        if (!empty($_FILES['project_images']['name'][0])) {
            error_log('Debug: Found images to upload');
            $upload_dir = '../uploads/projects/';
            
            // Создаем директорию, если её нет
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
                error_log('Debug: Created upload directory: ' . $upload_dir);
            }

            foreach ($_FILES['project_images']['tmp_name'] as $key => $tmp_name) {
                error_log('Debug: Processing image ' . $key);
                if ($_FILES['project_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_extension = strtolower(pathinfo($_FILES['project_images']['name'][$key], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                    if (!in_array($file_extension, $allowed_extensions)) {
                        $error = 'Допустимые форматы изображений: JPG, PNG, GIF, WEBP';
                        error_log('Debug: Invalid file extension: ' . $file_extension);
                        break;
                    }

                    // Генерируем уникальное имя файла
                    $filename = uniqid('project_') . '_' . time() . '.' . $file_extension;
                    $filepath = $upload_dir . $filename;

                    error_log('Debug: Attempting to move file to: ' . $filepath);
                    if (move_uploaded_file($tmp_name, $filepath)) {
                        error_log('Debug: File moved successfully');
                        $image_urls[] = '/uploads/projects/' . $filename;
                    } else {
                        $error = 'Ошибка при загрузке изображения';
                        error_log('Debug: Failed to move uploaded file. PHP Error: ' . error_get_last()['message']);
                        break;
                    }
                } else {
                    error_log('Debug: Upload error code: ' . $_FILES['project_images']['error'][$key]);
                }
            }
        } else {
            error_log('Debug: No images found in upload');
        }

        if (empty($error)) {
            try {
                $db->begin_transaction();
                error_log('Debug: Started database transaction');

                // Добавляем проект
                $query = "INSERT INTO projects (title, description, category_id, goal_amount, duration, user_id, status) 
                         VALUES (?, ?, ?, ?, ?, ?, 'active')";
                
                $stmt = $db->prepare($query);
                if (!$stmt) {
                    throw new Exception('Ошибка подготовки запроса: ' . $db->error);
                }
                
                $stmt->bind_param("ssiiii", $title, $description, $category_id, $goal_amount, $duration, $_SESSION['user_id']);
                
                if (!$stmt->execute()) {
                    throw new Exception('Ошибка при создании проекта: ' . $db->error);
                }

                $project_id = $db->insert_id;
                error_log('Debug: Created project with ID: ' . $project_id);

                // Добавляем изображения
                if (!empty($image_urls)) {
                    error_log('Debug: Adding images to database: ' . print_r($image_urls, true));
                    $image_query = "INSERT INTO project_images (project_id, image_url, is_main) VALUES (?, ?, ?)";
                    $image_stmt = $db->prepare($image_query);

                    foreach ($image_urls as $key => $url) {
                        $is_main = $key === 0 ? 1 : 0; // Первое изображение будет главным
                        $image_stmt->bind_param("isi", $project_id, $url, $is_main);
                        
                        if (!$image_stmt->execute()) {
                            throw new Exception('Ошибка при добавлении изображения: ' . $db->error);
                        }
                        error_log('Debug: Added image: ' . $url);
                    }
                    $image_stmt->close();
                }

                $db->commit();
                error_log('Debug: Transaction committed successfully');
                
                // Перенаправляем на страницу проекта
                header('Location: ' . SITE_URL . '/pages/project.php?id=' . $project_id);
                exit;

            } catch (Exception $e) {
                $db->rollback();
                error_log('Debug: Error occurred: ' . $e->getMessage());
                $error = $e->getMessage();
            }
        }
    }
}

// Подключаем header
include '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/create_project.css">
<script src="<?php echo SITE_URL; ?>/assets/js/create_project.js"></script>

<div class="create-project">
    <h1>Создание проекта</h1>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="project-form" id="projectForm">
        <div class="form-section">
            <h2>Основная информация</h2>
            
            <div class="form-group">
                <label for="title">Название проекта</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required
                       placeholder="Введите название вашей игры">
                <p class="help-text">Сделайте название кратким и запоминающимся</p>
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
        </div>

        <div class="form-section">
            <h2>Описание проекта</h2>
            
            <div class="form-group">
                <label for="description">Подробное описание</label>
                <textarea id="description" name="description" rows="8" required
                          placeholder="Расскажите о вашей игре, её особенностях и планах по разработке"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                <p class="help-text">Опишите ключевые особенности, геймплей, сюжет и планы развития</p>
            </div>
        </div>

        <div class="form-section">
            <h2>Медиа материалы</h2>
            
            <div class="media-upload" id="mediaUpload">
                <input type="file" id="project_images" name="project_images[]" accept="image/*" multiple style="display: none">
                <i class="ri-upload-cloud-line"></i>
                <h3>Перетащите изображения сюда</h3>
                <p>или нажмите для выбора файлов</p>
                <p class="help-text">До 5 изображений. Форматы: JPG, PNG, GIF, WEBP. До 5 МБ каждый.</p>
            </div>
            
            <div class="media-preview" id="mediaPreview"></div>
        </div>

        <div class="form-section">
            <h2>Параметры кампании</h2>
            
            <div class="form-group">
                <label for="goal_amount">Сумма финансирования (₽)</label>
                <input type="number" id="goal_amount" name="goal_amount" 
                       value="<?php echo htmlspecialchars($_POST['goal_amount'] ?? ''); ?>" 
                       min="10000" step="1000" required>
                <p class="help-text">Минимальная сумма: 10 000 ₽</p>
            </div>

            <div class="form-group">
                <label for="duration">Длительность кампании (в днях)</label>
                <input type="number" id="duration" name="duration" 
                       value="<?php echo htmlspecialchars($_POST['duration'] ?? ''); ?>" 
                       min="1" max="90" required>
                <p class="help-text">От 1 до 90 дней</p>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-preview" onclick="previewProject()">
                <i class="ri-eye-line"></i>
                Предпросмотр
            </button>
            <button type="submit" class="btn-submit">
                <i class="ri-rocket-line"></i>
                Создать проект
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?> 