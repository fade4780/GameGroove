<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Удаляем существующие таблицы в правильном порядке
$db->query("SET FOREIGN_KEY_CHECKS = 0");
$db->query("DROP TABLE IF EXISTS investments");
$db->query("DROP TABLE IF EXISTS comments");
$db->query("DROP TABLE IF EXISTS projects");
$db->query("DROP TABLE IF EXISTS categories");
$db->query("DROP TABLE IF EXISTS users");
$db->query("SET FOREIGN_KEY_CHECKS = 1");

// Создание таблицы пользователей
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($db->query($sql)) {
    echo "Таблица пользователей успешно создана<br>";
} else {
    echo "Ошибка при создании таблицы пользователей: " . $db->error . "<br>";
}

// Создание таблицы категорий
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($db->query($sql)) {
    echo "Таблица категорий успешно создана<br>";
} else {
    echo "Ошибка при создании таблицы категорий: " . $db->error . "<br>";
}

// Создание таблицы проектов
$sql = "CREATE TABLE IF NOT EXISTS projects (
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
)";

if ($db->query($sql)) {
    echo "Таблица проектов успешно создана<br>";
} else {
    echo "Ошибка при создании таблицы проектов: " . $db->error . "<br>";
}

// Создание таблицы инвестиций
$sql = "CREATE TABLE IF NOT EXISTS investments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    investor_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (investor_id) REFERENCES users(id)
)";

if ($db->query($sql)) {
    echo "Таблица инвестиций успешно создана<br>";
} else {
    echo "Ошибка при создании таблицы инвестиций: " . $db->error . "<br>";
}

// Создание таблицы комментариев
$sql = "CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($db->query($sql)) {
    echo "Таблица комментариев успешно создана<br>";
} else {
    echo "Ошибка при создании таблицы комментариев: " . $db->error . "<br>";
}

// Добавление базовых категорий
$categories = [
    ['name' => 'Экшен', 'description' => 'Игры с активным геймплеем'],
    ['name' => 'Стратегии', 'description' => 'Игры, требующие стратегического мышления'],
    ['name' => 'RPG', 'description' => 'Ролевые игры с развитием персонажа'],
    ['name' => 'Приключения', 'description' => 'Игры с увлекательным сюжетом'],
    ['name' => 'Симуляторы', 'description' => 'Игры, имитирующие реальность'],
    ['name' => 'Головоломки', 'description' => 'Игры на логику и мышление']
];

foreach ($categories as $category) {
    $stmt = $db->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $category['name'], $category['description']);
    $stmt->execute();
}

echo "База данных успешно инициализирована!";
?> 