<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Проверяем пользователей
    echo "<h2>Пользователи:</h2>";
    $users = $db->query("SELECT * FROM users");
    while ($user = $users->fetch_assoc()) {
        echo "ID: {$user['id']}, Имя: {$user['username']}, Email: {$user['email']}, Роль: {$user['role']}<br>";
    }
    
    // Проверяем проекты
    echo "<h2>Проекты:</h2>";
    $projects = $db->query("SELECT p.*, u.username as developer_name FROM projects p JOIN users u ON p.developer_id = u.id");
    while ($project = $projects->fetch_assoc()) {
        echo "ID: {$project['id']}, Название: {$project['title']}, Разработчик: {$project['developer_name']}<br>";
        echo "Описание: {$project['description']}<br>";
        echo "Цель: {$project['funding_goal']} ₽, Собрано: {$project['current_funding']} ₽<br><br>";
    }
    
    // Проверяем инвестиции
    echo "<h2>Инвестиции:</h2>";
    $investments = $db->query("SELECT i.*, u.username as investor_name, p.title as project_title 
                              FROM investments i 
                              JOIN users u ON i.investor_id = u.id 
                              JOIN projects p ON i.project_id = p.id");
    while ($investment = $investments->fetch_assoc()) {
        echo "Проект: {$investment['project_title']}, Инвестор: {$investment['investor_name']}, Сумма: {$investment['amount']} ₽<br>";
    }
    
    // Проверяем комментарии
    echo "<h2>Комментарии:</h2>";
    $comments = $db->query("SELECT c.*, u.username as author_name, p.title as project_title 
                           FROM comments c 
                           JOIN users u ON c.user_id = u.id 
                           JOIN projects p ON c.project_id = p.id");
    while ($comment = $comments->fetch_assoc()) {
        echo "Проект: {$comment['project_title']}, Автор: {$comment['author_name']}<br>";
        echo "Комментарий: {$comment['content']}<br><br>";
    }
    
    $db->close();
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
} 