<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Функции для работы с сессией и авторизацией
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isDeveloper() {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    return $_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer';
}

function isAdmin() {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    return $_SESSION['role'] === 'admin';
}

function getUserRole() {
    return $_SESSION['role'] ?? 'user';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Функции форматирования
function formatMoney($amount) {
    return number_format($amount, 0, ',', ' ') . ' ₽';
}

function formatDate($date) {
    return date('d.m.Y', strtotime($date));
}

function calculateFundingPercentage($currentAmount, $goalAmount) {
    if ($goalAmount <= 0) return 0;
    return min(100, round(($currentAmount / $goalAmount) * 100));
}

function e($text) {
    if (is_null($text)) return '';
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Функции для работы с проектами
function getProject($projectId) {
    global $db;
    $stmt = $db->prepare("
        SELECT p.*, u.username as developer_name, c.name as category_name,
               (SELECT SUM(amount) FROM investments WHERE project_id = p.id) as current_amount
        FROM projects p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getPopularProjects($limit = 5) {
    global $db;
    $stmt = $db->prepare("
        SELECT p.*, u.username as developer_name,
               (SELECT SUM(amount) FROM investments WHERE project_id = p.id) as current_amount
        FROM projects p
        LEFT JOIN users u ON p.user_id = u.id
        WHERE p.status = 'active'
        ORDER BY (
            SELECT SUM(amount) / p.goal_amount * 100
            FROM investments
            WHERE project_id = p.id
        ) DESC
        LIMIT ?
    ");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getLatestProjects($limit = 10) {
    global $db;
    $stmt = $db->prepare("
        SELECT p.*, u.username as developer_name,
               (SELECT SUM(amount) FROM investments WHERE project_id = p.id) as current_amount
        FROM projects p
        LEFT JOIN users u ON p.user_id = u.id
        WHERE p.status = 'active'
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getUserProjects($userId) {
    global $db;
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name,
               (SELECT SUM(amount) FROM investments WHERE project_id = p.id) as current_amount
        FROM projects p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
    ");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getDeveloperProjects($userId) {
    global $db;
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name,
               (SELECT SUM(amount) FROM investments WHERE project_id = p.id) as current_amount
        FROM projects p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.user_id = ? AND p.status = 'active'
        ORDER BY p.created_at DESC
    ");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Функции для работы с категориями
function getCategories() {
    global $db;
    $result = $db->query("SELECT * FROM categories ORDER BY name");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Функции для работы с инвестициями
function getUserInvestments($userId) {
    global $db;
    $stmt = $db->prepare("
        SELECT i.*, p.title as project_title, p.image_url
        FROM investments i
        JOIN projects p ON i.project_id = p.id
        WHERE i.investor_id = ?
        ORDER BY i.created_at DESC
    ");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Функции для работы с комментариями
function getProjectComments($projectId) {
    global $db;
    $stmt = $db->prepare("
        SELECT c.*, u.username
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.project_id = ?
        ORDER BY c.created_at DESC
    ");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Функции для работы с изображениями
function uploadImage($file) {
    $target_dir = UPLOAD_DIR;
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    // Проверка на тип файла
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return false;
    }
    
    // Проверка размера файла
    if ($file["size"] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Генерация уникального имени файла
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    }
    
    return false;
}

// Функции для работы с пользователями
function getUserData($userId) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateUserProfile($userId, $data) {
    global $db;
    $stmt = $db->prepare("UPDATE users SET username = ?, bio = ? WHERE id = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("ssi", $data['username'], $data['bio'], $userId);
    return $stmt->execute();
}

function logoutUser() {
    session_destroy();
    session_start();
}

// Функция для получения информации о пользователе
function getUserInfo($userId) {
    global $db;
    $stmt = $db->prepare("
        SELECT u.*, 
               (SELECT COUNT(*) FROM projects WHERE user_id = u.id) as projects_count,
               (SELECT COUNT(*) FROM investments WHERE investor_id = u.id) as investments_count
        FROM users u
        WHERE u.id = ?
    ");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Функция для получения категории проекта
function getProjectCategory($categoryId) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Функция для получения всех категорий
function getAllCategories() {
    global $db;
    $result = $db->query("SELECT * FROM categories ORDER BY name");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
?> 