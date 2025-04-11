<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Очищаем существующие данные
$db->query("SET FOREIGN_KEY_CHECKS = 0");
$db->query("TRUNCATE TABLE investments");
$db->query("TRUNCATE TABLE comments");
$db->query("TRUNCATE TABLE projects");
$db->query("TRUNCATE TABLE users");
$db->query("SET FOREIGN_KEY_CHECKS = 1");

// Создаем тестовых пользователей
$users = [
    ['username' => 'developer1', 'password' => password_hash('test123', PASSWORD_DEFAULT), 'role' => 'admin'],
    ['username' => 'developer2', 'password' => password_hash('test123', PASSWORD_DEFAULT), 'role' => 'admin'],
    ['username' => 'user1', 'password' => password_hash('test123', PASSWORD_DEFAULT), 'role' => 'user'],
    ['username' => 'user2', 'password' => password_hash('test123', PASSWORD_DEFAULT), 'role' => 'user']
];

$user_ids = [];
foreach ($users as $user) {
    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Ошибка подготовки запроса (users): " . $db->error);
    }
    $stmt->bind_param("sss", $user['username'], $user['password'], $user['role']);
    if (!$stmt->execute()) {
        die("Ошибка выполнения запроса (users): " . $stmt->error);
    }
    $user_ids[] = $db->insert_id;
}

// Создаем тестовые проекты
$projects = [
    [
        'user_id' => $user_ids[0],
        'title' => 'Космический Симулятор',
        'description' => 'Реалистичный симулятор космических полетов с детальной физикой и красивой графикой. Исследуйте галактику, стройте космические станции и торгуйте с другими игроками.',
        'category_id' => 5, // Симуляторы
        'goal_amount' => 1500000,
        'current_amount' => 750000,
        'duration' => 60,
        'image_url' => '/assets/images/projects/space-sim.jpg'
    ],
    [
        'user_id' => $user_ids[0],
        'title' => 'Подземелья и Головоломки',
        'description' => 'Увлекательная RPG с элементами головоломки. Исследуйте древние подземелья, решайте сложные загадки и сражайтесь с монстрами в пошаговых боях.',
        'category_id' => 3, // RPG
        'goal_amount' => 800000,
        'current_amount' => 600000,
        'duration' => 45,
        'image_url' => '/assets/images/projects/dungeon-puzzle.jpg'
    ],
    [
        'user_id' => $user_ids[1],
        'title' => 'Стратегия Завоевания',
        'description' => 'Масштабная стратегия в реальном времени. Развивайте свою империю, исследуйте технологии и участвуйте в эпических сражениях.',
        'category_id' => 2, // Стратегии
        'goal_amount' => 2000000,
        'current_amount' => 1200000,
        'duration' => 90,
        'image_url' => '/assets/images/projects/strategy.jpg'
    ],
    [
        'user_id' => $user_ids[1],
        'title' => 'Приключения в Лесу',
        'description' => 'Атмосферная приключенческая игра с элементами выживания. Исследуйте таинственный лес, собирайте ресурсы и раскрывайте его секреты.',
        'category_id' => 4, // Приключения
        'goal_amount' => 500000,
        'current_amount' => 450000,
        'duration' => 30,
        'image_url' => '/assets/images/projects/forest-adventure.jpg'
    ]
];

$project_ids = [];
foreach ($projects as $project) {
    $stmt = $db->prepare("INSERT INTO projects (user_id, title, description, category_id, goal_amount, current_amount, duration, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Ошибка подготовки запроса (projects): " . $db->error);
    }
    $stmt->bind_param("issiiiis", 
        $project['user_id'], 
        $project['title'], 
        $project['description'], 
        $project['category_id'],
        $project['goal_amount'],
        $project['current_amount'],
        $project['duration'],
        $project['image_url']
    );
    if (!$stmt->execute()) {
        die("Ошибка выполнения запроса (projects): " . $stmt->error);
    }
    $project_ids[] = $db->insert_id;
}

// Добавляем тестовые инвестиции
foreach ($project_ids as $project_id) {
    $investments = [
        ['investor_id' => $user_ids[1], 'amount' => 50000],
        ['investor_id' => $user_ids[0], 'amount' => 75000],
        ['investor_id' => $user_ids[2], 'amount' => 25000],
        ['investor_id' => $user_ids[3], 'amount' => 35000]
    ];

    foreach ($investments as $investment) {
        $stmt = $db->prepare("INSERT INTO investments (project_id, investor_id, amount) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("Ошибка подготовки запроса (investments): " . $db->error);
        }
        $stmt->bind_param("iii", $project_id, $investment['investor_id'], $investment['amount']);
        if (!$stmt->execute()) {
            die("Ошибка выполнения запроса (investments): " . $stmt->error);
        }
    }
}

echo "Тестовые данные успешно добавлены!";
?> 