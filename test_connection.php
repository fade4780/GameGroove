<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $conn = new mysqli($host, $user, $pass);
    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения: " . $conn->connect_error);
    }
    echo "Подключение к MySQL успешно установлено<br>";
    
    // Проверяем существование базы данных
    $result = $conn->query("SHOW DATABASES LIKE 'gamegroove'");
    if ($result->num_rows > 0) {
        echo "База данных 'gamegroove' существует<br>";
        
        // Выбираем базу данных
        $conn->select_db('gamegroove');
        
        // Проверяем существование таблиц
        $tables = ['users', 'categories', 'projects', 'investments', 'comments'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                echo "Таблица '$table' существует<br>";
                
                // Проверяем количество записей в таблице
                $result = $conn->query("SELECT COUNT(*) as count FROM $table");
                $row = $result->fetch_assoc();
                echo "Количество записей в таблице '$table': " . $row['count'] . "<br>";
            } else {
                echo "Таблица '$table' не существует<br>";
            }
        }
    } else {
        echo "База данных 'gamegroove' не существует<br>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}
?> 