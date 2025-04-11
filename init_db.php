<?php
$host = 'localhost';
$user = 'root';
$pass = '';

// Создаем соединение без выбора базы данных
$conn = new mysqli($host, $user, $pass);

// Проверяем соединение
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Создаем базу данных
$sql = "CREATE DATABASE IF NOT EXISTS gamegroove";
if ($conn->query($sql) === TRUE) {
    echo "База данных успешно создана<br>";
} else {
    echo "Ошибка при создании базы данных: " . $conn->error . "<br>";
}

// Выбираем базу данных
$conn->select_db('gamegroove');

// Создаем таблицу пользователей
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'developer', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Таблица users успешно создана<br>";
} else {
    echo "Ошибка при создании таблицы users: " . $conn->error . "<br>";
}

// Создаем таблицу категорий
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Таблица categories успешно создана<br>";
} else {
    echo "Ошибка при создании таблицы categories: " . $conn->error . "<br>";
}

// Создаем таблицу проектов
$sql = "CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    short_description VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(255),
    goal_amount DECIMAL(10,2) NOT NULL,
    current_amount DECIMAL(10,2) DEFAULT 0,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date DATE NOT NULL,
    status ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'draft',
    category_id INT,
    developer_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (developer_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Таблица projects успешно создана<br>";
} else {
    echo "Ошибка при создании таблицы projects: " . $conn->error . "<br>";
}

// Создаем таблицу инвестиций
$sql = "CREATE TABLE IF NOT EXISTS investments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (project_id) REFERENCES projects(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Таблица investments успешно создана<br>";
} else {
    echo "Ошибка при создании таблицы investments: " . $conn->error . "<br>";
}

// Создаем таблицу комментариев
$sql = "CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (project_id) REFERENCES projects(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Таблица comments успешно создана<br>";
} else {
    echo "Ошибка при создании таблицы comments: " . $conn->error . "<br>";
}

// Создаем директорию для загрузки изображений проектов
$uploadDir = __DIR__ . '/uploads/projects';
if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0777, true)) {
        echo "Директория для загрузки изображений создана<br>";
    } else {
        echo "Ошибка при создании директории для загрузки изображений<br>";
    }
}

$conn->close();
echo "<br>Инициализация базы данных завершена!";
?> 