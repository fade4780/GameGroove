<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Подключаем конфигурацию
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    // Проверяем подключение
    if (!$db) {
        throw new Exception("Ошибка подключения к базе данных: " . mysqli_connect_error());
    }

    // Читаем SQL-файл
    $sql = file_get_contents(__DIR__ . '/update_users_table.sql');
    
    // Выполняем SQL-запросы
    if ($db->multi_query($sql)) {
        do {
            // Очищаем результаты каждого запроса
            if ($result = $db->store_result()) {
                $result->free();
            }
        } while ($db->next_result());
    }

    if ($db->error) {
        throw new Exception("Ошибка при выполнении SQL: " . $db->error);
    }

    echo "База данных успешно обновлена!";
    echo "\nТеперь вы можете запустить setup_test_users.php для создания тестовых пользователей.";

} catch (Exception $e) {
    die("Произошла ошибка: " . $e->getMessage());
} 