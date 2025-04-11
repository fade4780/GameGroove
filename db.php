<?php
$host = "localhost"; // или IP облачной БД
$dbname = "gamegroove";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Успешное подключение к БД!";
} catch(PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage();
}
?>