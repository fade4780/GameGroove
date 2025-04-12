<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Получаем ID проекта
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем данные проекта
$query = "
    SELECT p.id, p.title, p.description, p.category_id, p.goal_amount, p.current_amount,
           p.duration, p.user_id, p.created_at, p.status,
           u.username as developer_name, c.name as category_name 
    FROM projects p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?";

$stmt = $db->prepare($query);
if (!$stmt) {
    die('Ошибка подготовки запроса: ' . $db->error);
}

$stmt->bind_param('i', $project_id);
if (!$stmt->execute()) {
    die('Ошибка выполнения запроса: ' . $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    die('Ошибка получения результата: ' . $db->error);
}

$project = $result->fetch_assoc();
$stmt->close();

if (!$project) {
    header('Location: projects.php');
    exit();
}

// Получаем изображения проекта
$images_query = "SELECT * FROM project_images WHERE project_id = ? ORDER BY is_main DESC, created_at ASC";
$stmt = $db->prepare($images_query);
$stmt->bind_param('i', $project_id);
$stmt->execute();
$images_result = $stmt->get_result();
$images = [];
while ($image = $images_result->fetch_assoc()) {
    $images[] = $image;
}
$stmt->close();

// Отладочная информация
echo '<!-- Debug: Project ID = ' . $project_id . ' -->';
echo '<!-- Debug: Number of images = ' . count($images) . ' -->';

// Получаем комментарии к проекту
$comments = null;
$comments_query = "
    SELECT c.*, u.username as author_name
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.project_id = ? AND c.parent_id IS NULL
    ORDER BY c.created_at DESC
";

$comments_stmt = $db->prepare($comments_query);
if ($comments_stmt) {
    $comments_stmt->bind_param('i', $project_id);
    if ($comments_stmt->execute()) {
        $comments = $comments_stmt->get_result();
    }
    $comments_stmt->close();
} else {
    // Если возникла ошибка при подготовке запроса
    error_log("Error preparing comments query: " . $db->error);
}

// Обработка добавления комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    if (isset($_POST['comment'])) {
        $user_id = $_SESSION['user_id'];
        $comment_text = trim($_POST['comment']);
        $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        
        if (!empty($comment_text)) {
            // Проверяем существование таблицы comments
            $check_table = $db->query("SHOW TABLES LIKE 'comments'");
            if ($check_table->num_rows === 0) {
                // Создаем таблицу, если она не существует
                $create_table_query = "
                    CREATE TABLE IF NOT EXISTS comments (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        project_id INT NOT NULL,
                        user_id INT NOT NULL,
                        parent_id INT DEFAULT NULL,
                        comment_text TEXT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                        FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
                    )
                ";
                if (!$db->query($create_table_query)) {
                    error_log("Error creating comments table: " . $db->error);
                    $error_message = "Ошибка при создании таблицы комментариев.";
                }
            } else {
                // Проверяем наличие колонки parent_id
                $check_column = $db->query("SHOW COLUMNS FROM comments LIKE 'parent_id'");
                if ($check_column->num_rows === 0) {
                    // Добавляем колонку parent_id, если её нет
                    $alter_table_query = "ALTER TABLE comments ADD COLUMN parent_id INT DEFAULT NULL AFTER user_id";
                    if (!$db->query($alter_table_query)) {
                        error_log("Error adding parent_id column: " . $db->error);
                        $error_message = "Ошибка при обновлении таблицы комментариев.";
                    }
                }
            }

            // Добавляем комментарий
            $insert_query = "INSERT INTO comments (project_id, user_id, parent_id, comment_text) VALUES (?, ?, ?, ?)";
            $insert_stmt = $db->prepare($insert_query);
            
            if ($insert_stmt === false) {
                error_log("Error preparing comment insert: " . $db->error);
                $error_message = "Ошибка при подготовке запроса: " . $db->error;
            } else {
                $insert_stmt->bind_param('iiis', $project_id, $user_id, $parent_id, $comment_text);
                
                if ($insert_stmt->execute()) {
                    $insert_stmt->close();
                    // Перенаправляем на ту же страницу для предотвращения повторной отправки формы
                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit();
                } else {
                    error_log("Error executing comment insert: " . $insert_stmt->error);
                    $error_message = "Не удалось добавить комментарий: " . $insert_stmt->error;
                }
                $insert_stmt->close();
            }
        } else {
            $error_message = "Комментарий не может быть пустым.";
        }
    } elseif (isset($_POST['delete_comment']) && isset($_POST['comment_id'])) {
        // Обработка удаления комментария
        $comment_id = (int)$_POST['comment_id'];
        $user_id = $_SESSION['user_id'];
        
        // Проверяем, является ли пользователь автором комментария или администратором
        $check_query = "SELECT user_id FROM comments WHERE id = ?";
        $check_stmt = $db->prepare($check_query);
        if ($check_stmt === false) {
            error_log("Error preparing check query: " . $db->error);
            $error_message = "Ошибка при проверке прав доступа.";
        } else {
            $check_stmt->bind_param('i', $comment_id);
            if (!$check_stmt->execute()) {
                error_log("Error executing check query: " . $check_stmt->error);
                $error_message = "Ошибка при проверке прав доступа.";
            } else {
                $result = $check_stmt->get_result();
                $comment = $result->fetch_assoc();
                $check_stmt->close();
                
                if ($comment && ($comment['user_id'] == $user_id || $_SESSION['role'] === 'admin')) {
                    // Удаляем комментарий и все его ответы (благодаря ON DELETE CASCADE)
                    $delete_query = "DELETE FROM comments WHERE id = ?";
                    $delete_stmt = $db->prepare($delete_query);
                    
                    if ($delete_stmt === false) {
                        error_log("Error preparing delete query: " . $db->error);
                        $error_message = "Ошибка при подготовке запроса на удаление.";
                    } else {
                        $delete_stmt->bind_param('i', $comment_id);
                        
                        if ($delete_stmt->execute()) {
                            $delete_stmt->close();
                            // Перенаправляем на ту же страницу для предотвращения повторной отправки формы
                            header("Location: " . $_SERVER['REQUEST_URI']);
                            exit();
                        } else {
                            error_log("Error executing delete query: " . $delete_stmt->error);
                            $error_message = "Не удалось удалить комментарий.";
                        }
                        $delete_stmt->close();
                    }
                } else {
                    $error_message = "У вас нет прав для удаления этого комментария.";
                }
            }
        }
    }
}

// Обработка инвестиции
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_POST['amount'])) {
    $user_id = $_SESSION['user_id'];
    $amount = (float)$_POST['amount'];
    
    if ($amount > 0) {
        $db->query("INSERT INTO investments (project_id, investor_id, amount) VALUES ($project_id, $user_id, $amount)");
        $db->query("UPDATE projects SET current_amount = current_amount + $amount WHERE id = $project_id");
        header("Location: project.php?id=$project_id");
        exit();
    }
}

// Обработка загрузки изображений
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_FILES['project_images'])) {
    if ($_SESSION['user_id'] == $project['user_id']) { // Проверяем, что это владелец проекта
        $upload_dir = '../uploads/projects/';
        
        // Создаем папку, если её нет
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $files = $_FILES['project_images'];
        $file_count = count($files['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$i];
                $name = $files['name'][$i];
                
                // Генерируем уникальное имя файла
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $new_name = uniqid('project_' . $project_id . '_') . '.' . $extension;
                $file_path = $upload_dir . $new_name;
                
                // Проверяем тип файла
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($files['type'][$i], $allowed_types)) {
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        // Сохраняем информацию в базе данных
                        $relative_path = '/uploads/projects/' . $new_name;
                        $is_main = empty($images) ? 1 : 0; // Если нет изображений, делаем первое главным
                        
                        $stmt = $db->prepare("INSERT INTO project_images (project_id, image_url, is_main) VALUES (?, ?, ?)");
                        $stmt->bind_param('isi', $project_id, $relative_path, $is_main);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        }
        
        // Перенаправляем обратно на страницу проекта
        header("Location: project.php?id=$project_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['title']); ?> - GameGroove</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/project.css">
    <link rel="stylesheet" href="../assets/css/gallery.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="project-details">
            <h1 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h1>
            
            <div class="gallery-container">
                <div class="main-image-container">
                    <?php if (!empty($images)): ?>
                        <?php
                        echo '<!-- Debug: Main image URL = ' . htmlspecialchars($images[0]['image_url']) . ' -->';
                        ?>
                        <img src="<?php echo htmlspecialchars($images[0]['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($project['title']); ?>" 
                             class="main-image">
                    <?php else: ?>
                        <?php
                        echo '<!-- Debug: Using default image -->';
                        $default_image = '/assets/images/default-project.jpg';
                        echo '<!-- Debug: Default image path = ' . $default_image . ' -->';
                        ?>
                        <img src="<?php echo $default_image; ?>" 
                             alt="<?php echo htmlspecialchars($project['title']); ?>" 
                             class="main-image">
                    <?php endif; ?>
                </div>
                <div class="thumbnails-container">
                    <?php 
                    foreach ($images as $index => $image): 
                        echo '<!-- Debug: Thumbnail ' . $index . ' URL = ' . htmlspecialchars($image['image_url']) . ' -->';
                    ?>
                        <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                             alt="Изображение проекта" 
                             class="thumbnail<?php echo $image['is_main'] ? ' active' : ''; ?>">
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="project-info">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $project['user_id']): ?>
                    <div class="image-upload-form">
                        <h3>Загрузка изображений</h3>
                        <form method="POST" enctype="multipart/form-data" class="upload-form">
                            <div class="file-input-container">
                                <input type="file" name="project_images[]" multiple accept="image/jpeg,image/png,image/gif" 
                                       class="file-input" required>
                                <button type="submit" class="upload-button">
                                    <i class="ri-upload-2-line"></i>
                                    Загрузить изображения
                                </button>
                            </div>
                            <p class="upload-info">Поддерживаются форматы: JPG, PNG, GIF</p>
                        </form>
                    </div>
                <?php endif; ?>
                
                <div class="project-description">
                    <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                    
                    <div class="creator-info">
                        <img src="../assets/images/default-avatar.png" alt="<?php echo htmlspecialchars($project['developer_name']); ?>" class="creator-avatar">
                        <div class="creator-details">
                            <div class="creator-name"><?php echo htmlspecialchars($project['developer_name']); ?></div>
                            <div class="creator-role">Разработчик</div>
                        </div>
                    </div>
                </div>
                
                <div class="project-stats">
                    <div class="stat-item">
                        <span class="stat-label">Собрано</span>
                        <div class="stat-value"><?php echo number_format((float)$project['current_amount'], 0, '.', ' '); ?> ₽</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Цель</span>
                        <div class="stat-value"><?php echo number_format((float)$project['goal_amount'], 0, '.', ' '); ?> ₽</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Осталось дней</span>
                        <div class="stat-value"><?php 
                            $end_date = strtotime($project['created_at'] . ' + ' . $project['duration'] . ' days');
                            $days_left = ceil(($end_date - time()) / (60 * 60 * 24));
                            echo max(0, $days_left);
                        ?></div>
                    </div>
                    
                    <div class="funding-progress">
                        <div class="progress-bar" style="width: <?php 
                            $percentage = $project['goal_amount'] > 0 ? 
                                min(100, ((float)$project['current_amount'] / (float)$project['goal_amount']) * 100) : 0;
                            echo $percentage;
                        ?>%"></div>
                    </div>
                    
                    <?php if (isset($_SESSION['user_id']) && $project['status'] === 'active'): ?>
                        <form method="POST" style="margin-top: 1rem;">
                            <div class="investment-input">
                                <input type="number" id="amount" name="amount" min="100" step="100" 
                                       placeholder="Сумма инвестиции (мин. 100 ₽)" required
                                       class="form-control">
                            </div>
                            <button type="submit" class="invest-button">
                                <i class="ri-money-dollar-circle-line"></i>
                                Инвестировать
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="project-comments">
                <h2>Комментарии</h2>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" class="comment-form">
                        <textarea id="comment" name="comment" placeholder="Напишите комментарий..." required></textarea>
                        <button type="submit">
                            <i class="ri-send-plane-fill"></i>
                            Отправить
                        </button>
                    </form>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>Чтобы оставить комментарий, пожалуйста, <a href="login.php">войдите</a> или <a href="register.php">зарегистрируйтесь</a></p>
                    </div>
                <?php endif; ?>

                <div class="comments-list">
                    <?php if ($comments && $comments->num_rows > 0): ?>
                        <?php while ($comment = $comments->fetch_assoc()): ?>
                            <div class="comment" id="comment-<?php echo $comment['id']; ?>">
                                <div class="comment-header">
                                    <span class="comment-author"><?php echo htmlspecialchars($comment['author_name']); ?></span>
                                    <span class="comment-date"><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?></span>
                                    <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $comment['user_id'] || $_SESSION['role'] === 'admin')): ?>
                                        <form method="POST" class="delete-comment-form">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                            <button type="submit" name="delete_comment" class="delete-comment">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <div class="comment-text">
                                    <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                                </div>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form method="POST" class="reply-form">
                                        <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                        <textarea name="comment" placeholder="Ответить..." required></textarea>
                                        <button type="submit">
                                            <i class="ri-reply-line"></i>
                                            Ответить
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php
                                // Получаем ответы на комментарий
                                $replies_query = "
                                    SELECT c.*, u.username as author_name
                                    FROM comments c 
                                    JOIN users u ON c.user_id = u.id 
                                    WHERE c.parent_id = ?
                                    ORDER BY c.created_at ASC
                                ";
                                $replies_stmt = $db->prepare($replies_query);
                                $replies_stmt->bind_param('i', $comment['id']);
                                $replies_stmt->execute();
                                $replies = $replies_stmt->get_result();
                                
                                if ($replies && $replies->num_rows > 0):
                                ?>
                                    <div class="replies">
                                        <?php while ($reply = $replies->fetch_assoc()): ?>
                                            <div class="reply" id="comment-<?php echo $reply['id']; ?>">
                                                <div class="comment-header">
                                                    <span class="comment-author"><?php echo htmlspecialchars($reply['author_name']); ?></span>
                                                    <span class="comment-date"><?php echo date('d.m.Y H:i', strtotime($reply['created_at'])); ?></span>
                                                    <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $reply['user_id'] || $_SESSION['role'] === 'admin')): ?>
                                                        <form method="POST" class="delete-comment-form">
                                                            <input type="hidden" name="comment_id" value="<?php echo $reply['id']; ?>">
                                                            <button type="submit" name="delete_comment" class="delete-comment">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="comment-text">
                                                    <?php echo nl2br(htmlspecialchars($reply['comment_text'])); ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php 
                                endif;
                                $replies_stmt->close();
                                ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-comments">Пока нет комментариев. Будьте первым!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/gallery.js"></script>

    <footer>
        <p>&copy; 2024 GameGroove. Все права защищены.</p>
    </footer>
</body>
</html> 