<?php
require_once __DIR__ . '/includes/config.php';

try {
    // Очистка существующих данных (кроме категорий)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE comments");
    $pdo->exec("TRUNCATE TABLE investments");
    $pdo->exec("TRUNCATE TABLE projects");
    $pdo->exec("TRUNCATE TABLE users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Создание тестовых пользователей
    $users = [
        [
            'username' => 'admin',
            'email' => 'admin@gamegroove.ru',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin'
        ],
        [
            'username' => 'developer1',
            'email' => 'dev1@gamegroove.ru',
            'password' => password_hash('dev123', PASSWORD_DEFAULT),
            'role' => 'developer'
        ],
        [
            'username' => 'developer2',
            'email' => 'dev2@gamegroove.ru',
            'password' => password_hash('dev123', PASSWORD_DEFAULT),
            'role' => 'developer'
        ],
        [
            'username' => 'user1',
            'email' => 'user1@gamegroove.ru',
            'password' => password_hash('user123', PASSWORD_DEFAULT),
            'role' => 'user'
        ],
        [
            'username' => 'user2',
            'email' => 'user2@gamegroove.ru',
            'password' => password_hash('user123', PASSWORD_DEFAULT),
            'role' => 'user'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute([$user['username'], $user['email'], $user['password'], $user['role']]);
    }

    // Получаем ID пользователей
    $developer1Id = $pdo->query("SELECT id FROM users WHERE username = 'developer1'")->fetch()['id'];
    $developer2Id = $pdo->query("SELECT id FROM users WHERE username = 'developer2'")->fetch()['id'];
    
    // Получаем ID категорий
    $categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();
    $categoryIds = array_column($categories, 'id', 'name');

    // Создание тестовых проектов
    $projects = [
        [
            'user_id' => $developer1Id,
            'category_id' => $categoryIds['Экшен'],
            'title' => 'Космический шутер "StarBattle"',
            'description' => 'Динамичный космический шутер с элементами RPG. Исследуйте галактику, сражайтесь с пиратами и улучшайте свой корабль.',
            'goal_amount' => 500000,
            'status' => 'active',
            'end_date' => date('Y-m-d', strtotime('+30 days'))
        ],
        [
            'user_id' => $developer1Id,
            'category_id' => $categoryIds['Ролевые игры'],
            'title' => 'Фэнтези RPG "Легенды Древних Земель"',
            'description' => 'Погрузитесь в мир магии и приключений. Создайте своего героя и отправьтесь в эпическое путешествие.',
            'goal_amount' => 1000000,
            'status' => 'active',
            'end_date' => date('Y-m-d', strtotime('+60 days'))
        ],
        [
            'user_id' => $developer2Id,
            'category_id' => $categoryIds['Стратегии'],
            'title' => 'Градостроительный симулятор "MegaCity"',
            'description' => 'Постройте город своей мечты! Управляйте ресурсами, развивайте инфраструктуру и сделайте жителей счастливыми.',
            'goal_amount' => 750000,
            'status' => 'active',
            'end_date' => date('Y-m-d', strtotime('+45 days'))
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO projects (user_id, category_id, title, description, goal_amount, status, end_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    foreach ($projects as $project) {
        $stmt->execute([
            $project['user_id'],
            $project['category_id'],
            $project['title'],
            $project['description'],
            $project['goal_amount'],
            $project['status'],
            $project['end_date']
        ]);
    }

    // Получаем ID проектов
    $projectIds = $pdo->query("SELECT id FROM projects")->fetchAll(PDO::FETCH_COLUMN);
    
    // Создание тестовых инвестиций
    $investor1Id = $pdo->query("SELECT id FROM users WHERE username = 'user1'")->fetch()['id'];
    $investor2Id = $pdo->query("SELECT id FROM users WHERE username = 'user2'")->fetch()['id'];

    $investments = [
        ['user_id' => $investor1Id, 'project_id' => $projectIds[0], 'amount' => 50000],
        ['user_id' => $investor2Id, 'project_id' => $projectIds[0], 'amount' => 75000],
        ['user_id' => $investor1Id, 'project_id' => $projectIds[1], 'amount' => 100000],
        ['user_id' => $investor2Id, 'project_id' => $projectIds[2], 'amount' => 150000]
    ];

    $stmt = $pdo->prepare("INSERT INTO investments (user_id, project_id, amount) VALUES (?, ?, ?)");
    foreach ($investments as $investment) {
        $stmt->execute([$investment['user_id'], $investment['project_id'], $investment['amount']]);
    }

    // Создание тестовых комментариев
    $comments = [
        [
            'user_id' => $investor1Id,
            'project_id' => $projectIds[0],
            'content' => 'Отличная идея! Давно ждал подобный проект.'
        ],
        [
            'user_id' => $investor2Id,
            'project_id' => $projectIds[0],
            'content' => 'Графика выглядит впечатляюще. Когда планируется альфа-версия?'
        ],
        [
            'user_id' => $investor1Id,
            'project_id' => $projectIds[1],
            'content' => 'Интересная концепция. Будет ли поддержка модов?'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO comments (user_id, project_id, content) VALUES (?, ?, ?)");
    foreach ($comments as $comment) {
        $stmt->execute([$comment['user_id'], $comment['project_id'], $comment['content']]);
    }

    echo "Тестовые данные успешно добавлены!\n\n";
    echo "Данные для входа:\n\n";
    echo "Администратор:\n";
    echo "Email: admin@gamegroove.ru\n";
    echo "Пароль: admin123\n\n";
    echo "Разработчик:\n";
    echo "Email: dev1@gamegroove.ru\n";
    echo "Пароль: dev123\n\n";
    echo "Пользователь:\n";
    echo "Email: user1@gamegroove.ru\n";
    echo "Пароль: user123\n";

} catch (PDOException $e) {
    die("Ошибка при добавлении тестовых данных: " . $e->getMessage());
}
?> 