<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Правильные пути к файлам
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

// Проверка подключения к базе данных
if (!$db) {
    die("Ошибка подключения к базе данных: " . mysqli_connect_error());
}

try {
    // Очищаем все таблицы
    $db->query("DELETE FROM investments") or die("Ошибка очистки таблицы investments: " . $db->error);
    $db->query("DELETE FROM comments") or die("Ошибка очистки таблицы comments: " . $db->error);
    $db->query("DELETE FROM projects") or die("Ошибка очистки таблицы projects: " . $db->error);
    $db->query("DELETE FROM users") or die("Ошибка очистки таблицы users: " . $db->error);

    // Создаем новых пользователей
    $test_users = [
        [
            'username' => 'admin1',
            'password' => password_hash('Admin123!', PASSWORD_DEFAULT),
            'role' => 'admin'
        ],
        [
            'username' => 'admin2',
            'password' => password_hash('Admin123!', PASSWORD_DEFAULT),
            'role' => 'admin'
        ],
        [
            'username' => 'user1',
            'password' => password_hash('User123!', PASSWORD_DEFAULT),
            'role' => 'user'
        ],
        [
            'username' => 'user2',
            'password' => password_hash('User123!', PASSWORD_DEFAULT),
            'role' => 'user'
        ]
    ];

    // Добавляем пользователей в базу данных
    foreach ($test_users as $user) {
        $query = "INSERT INTO users (username, password, role, created_at) 
                 VALUES (
                     '" . $db->real_escape_string($user['username']) . "',
                     '" . $db->real_escape_string($user['password']) . "',
                     '" . $db->real_escape_string($user['role']) . "',
                     NOW()
                 )";
        
        if (!$db->query($query)) {
            throw new Exception("Ошибка при добавлении пользователя {$user['username']}: " . $db->error);
        }
    }

    echo "<pre>";
    echo "Тестовые пользователи успешно созданы!\n\n";
    echo "Данные для входа:\n\n";
    echo "Администраторы:\n";
    echo "1. Логин: admin1 / Пароль: Admin123!\n";
    echo "2. Логин: admin2 / Пароль: Admin123!\n\n";
    echo "Пользователи:\n";
    echo "1. Логин: user1 / Пароль: User123!\n";
    echo "2. Логин: user2 / Пароль: User123!\n";
    echo "</pre>";

} catch (Exception $e) {
    die("Произошла ошибка: " . $e->getMessage());
} 