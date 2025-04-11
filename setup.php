<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Создаем соединение
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения: " . $conn->connect_error);
    }
    
    // Читаем SQL-файл
    $sql = file_get_contents('setup_database.sql');
    
    // Выполняем SQL-запросы
    if ($conn->multi_query($sql)) {
        do {
            // Обрабатываем результаты каждого запроса
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        
        echo "База данных успешно создана и заполнена тестовыми данными!<br>";
        echo "Вы можете войти, используя следующие учетные данные:<br>";
        echo "Администратор: admin@gamegroove.com / password<br>";
        echo "Разработчик 1: dev1@gamegroove.com / password<br>";
        echo "Разработчик 2: dev2@gamegroove.com / password<br>";
        echo "Инвестор 1: inv1@gamegroove.com / password<br>";
        echo "Инвестор 2: inv2@gamegroove.com / password<br>";
    } else {
        throw new Exception("Ошибка при выполнении SQL-запросов: " . $conn->error);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Произошла ошибка: " . $e->getMessage();
}
?> 