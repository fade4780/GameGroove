<?php
require_once __DIR__ . '/db.php';

// Создание таблиц
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
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
        developer_id INT NOT NULL,
        category_id INT NOT NULL,
        goal_amount DECIMAL(10,2) NOT NULL,
        current_amount DECIMAL(10,2) DEFAULT 0,
        duration INT NOT NULL,
        image_url VARCHAR(255),
        status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (developer_id) REFERENCES users(id),
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

echo "База данных успешно инициализирована!"; 