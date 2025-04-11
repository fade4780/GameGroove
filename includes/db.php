<?php
// Настройки сессии (должны быть до session_start)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);
    session_start();
}

require_once __DIR__ . '/config.php';

// Глобальная переменная для соединения с БД
global $db;

// Подключение к базе данных (только если еще не подключены)
if (!isset($db)) {
    try {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($db->connect_error) {
            throw new Exception("Ошибка подключения: " . $db->connect_error);
        }
        
        $db->set_charset("utf8mb4");
    } catch (Exception $e) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }
}

// Создание таблиц, если они не существуют
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        user_id INT NOT NULL,
        category_id INT NOT NULL,
        goal_amount DECIMAL(10,2) NOT NULL,
        current_amount DECIMAL(10,2) DEFAULT 0,
        duration INT NOT NULL,
        image_url VARCHAR(255),
        status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS investments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        investor_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (project_id) REFERENCES projects(id),
        FOREIGN KEY (investor_id) REFERENCES users(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        user_id INT NOT NULL,
        text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (project_id) REFERENCES projects(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )"
];

// Создаем таблицы
foreach ($tables as $sql) {
    if (!$db->query($sql)) {
        die("Ошибка создания таблицы: " . $db->error);
    }
}

// Добавляем базовые категории, если их нет
$default_categories = [
    ['name' => 'Экшен', 'description' => 'Игры с активным геймплеем'],
    ['name' => 'Стратегии', 'description' => 'Игры, требующие стратегического мышления'],
    ['name' => 'RPG', 'description' => 'Ролевые игры с развитием персонажа'],
    ['name' => 'Приключения', 'description' => 'Игры с увлекательным сюжетом'],
    ['name' => 'Симуляторы', 'description' => 'Игры, имитирующие реальность'],
    ['name' => 'Головоломки', 'description' => 'Игры на логику и мышление']
];

$check_categories = $db->query("SELECT COUNT(*) as count FROM categories");
$categories_count = $check_categories->fetch_assoc()['count'];

if ($categories_count == 0) {
    $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    foreach ($default_categories as $category) {
        $stmt->bind_param("ss", $category['name'], $category['description']);
        $stmt->execute();
    }
}

// Функции для работы с пользователями
function register_user($username, $password) {
    global $db;
    
    // Проверяем, существует ли пользователь с таким логином
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return false; // Пользователь с таким логином уже существует
    }
    
    // Хешируем пароль
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Создаем нового пользователя
    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
    if (!$stmt) {
        error_log("Error preparing statement: " . $db->error);
        return false;
    }
    
    $stmt->bind_param("ss", $username, $hashed_password);
    $success = $stmt->execute();
    
    if (!$success) {
        error_log("Error executing statement: " . $stmt->error);
        return false;
    }
    
    return true;
}

function login_user($username, $password) {
    global $db;
    
    // Отладочная информация
    error_log("Попытка входа для пользователя: " . $username);
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    if (!$stmt) {
        error_log("Ошибка подготовки запроса: " . $db->error);
        return false;
    }
    
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        error_log("Ошибка выполнения запроса: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        error_log("Пользователь не найден: " . $username);
        return false;
    }
    
    if (!password_verify($password, $user['password'])) {
        error_log("Неверный пароль для пользователя: " . $username);
        return false;
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    
    error_log("Успешный вход для пользователя: " . $username);
    return true;
}

function get_user($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_logged_in_user() {
    global $db;
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    if (!$stmt) {
        error_log("Ошибка подготовки запроса: " . $db->error);
        return null;
    }
    
    $stmt->bind_param("i", $_SESSION['user_id']);
    if (!$stmt->execute()) {
        error_log("Ошибка выполнения запроса: " . $stmt->error);
        return null;
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        error_log("Ошибка получения результата: " . $stmt->error);
        return null;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user;
}

function is_admin() {
    $user = get_logged_in_user();
    return $user && $user['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: /pages/login.php");
        exit();
    }
}

function require_admin() {
    if (!is_admin()) {
        header("Location: /pages/login.php");
        exit();
    }
}

// Функции для работы с проектами
function get_projects($limit = null) {
    global $db;
    
    $query = "SELECT p.*, u.username as developer_name, c.name as category_name,
              (SELECT COUNT(*) FROM investments WHERE project_id = p.id) as investors_count
              FROM projects p 
              JOIN users u ON p.user_id = u.id
              JOIN categories c ON p.category_id = c.id
              ORDER BY p.created_at DESC";
              
    if ($limit) {
        $query .= " LIMIT " . (int)$limit;
    }
    
    $result = $db->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function get_project($id) {
    global $db;
    
    $query = "SELECT p.*, u.username as developer_name, c.name as category_name,
              (SELECT COUNT(*) FROM investments WHERE project_id = p.id) as investors_count
              FROM projects p 
              JOIN users u ON p.user_id = u.id
              JOIN categories c ON p.category_id = c.id
              WHERE p.id = ?";
              
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_user_projects($user_id) {
    global $db;
    
    $query = "SELECT p.*, c.name as category_name,
              (SELECT COUNT(*) FROM investments WHERE project_id = p.id) as investors_count,
              (SELECT SUM(amount) FROM investments WHERE project_id = p.id) as total_invested
              FROM projects p 
              JOIN categories c ON p.category_id = c.id
              WHERE p.user_id = ?
              ORDER BY p.created_at DESC";
              
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_user_investments($user_id) {
    global $db;
    
    $query = "SELECT i.*, p.title as project_title, p.image_url, 
              u.username as developer_name, c.name as category_name
              FROM investments i 
              JOIN projects p ON i.project_id = p.id
              JOIN users u ON p.user_id = u.id
              JOIN categories c ON p.category_id = c.id
              WHERE i.investor_id = ?
              ORDER BY i.created_at DESC";
              
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function create_project($title, $description, $category_id, $goal_amount, $duration, $image_url) {
    global $db;
    
    if (!is_logged_in()) {
        return false;
    }
    
    $query = "INSERT INTO projects (title, description, category_id, goal_amount, duration, image_url, user_id) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
              
    $stmt = $db->prepare($query);
    $stmt->bind_param("ssiisis", $title, $description, $category_id, $goal_amount, $duration, $image_url, $_SESSION['user_id']);
    
    return $stmt->execute();
}

function add_comment($project_id, $text) {
    global $db;
    
    if (!is_logged_in()) {
        return false;
    }
    
    $stmt = $db->prepare("INSERT INTO comments (project_id, user_id, text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $project_id, $_SESSION['user_id'], $text);
    
    return $stmt->execute();
}
?> 