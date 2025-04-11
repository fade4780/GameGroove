<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Проверяем авторизацию и права администратора
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user = $db->query("SELECT * FROM users WHERE id = " . $_SESSION['user_id'])->fetch_assoc();
if ($user['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Обработка действий администратора
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete_user':
                $user_id = (int)$_POST['user_id'];
                $db->query("DELETE FROM comments WHERE user_id = $user_id");
                $db->query("DELETE FROM investments WHERE investor_id = $user_id");
                $db->query("DELETE FROM projects WHERE developer_id = $user_id");
                $db->query("DELETE FROM users WHERE id = $user_id");
                break;
                
            case 'change_role':
                $user_id = (int)$_POST['user_id'];
                $new_role = $db->real_escape_string($_POST['role']);
                $db->query("UPDATE users SET role = '$new_role' WHERE id = $user_id");
                break;
                
            case 'delete_project':
                $project_id = (int)$_POST['project_id'];
                $db->query("DELETE FROM comments WHERE project_id = $project_id");
                $db->query("DELETE FROM investments WHERE project_id = $project_id");
                $db->query("DELETE FROM projects WHERE id = $project_id");
                break;
                
            case 'change_project_status':
                $project_id = (int)$_POST['project_id'];
                $new_status = $db->real_escape_string($_POST['status']);
                $db->query("UPDATE projects SET status = '$new_status' WHERE id = $project_id");
                break;
        }
        
        // Перенаправляем на ту же страницу для обновления данных
        header('Location: admin.php');
        exit();
    }
}

// Получаем данные для отображения
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC");
$projects = $db->query("
    SELECT p.*, u.username as developer_name 
    FROM projects p 
    JOIN users u ON p.developer_id = u.id 
    ORDER BY p.created_at DESC
");
$investments = $db->query("
    SELECT i.*, u.username as investor_name, p.title as project_title 
    FROM investments i 
    JOIN users u ON i.investor_id = u.id 
    JOIN projects p ON i.project_id = p.id 
    ORDER BY i.created_at DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - GameGroove</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .admin-panel {
            padding: 20px;
        }
        .admin-section {
            margin-bottom: 40px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .admin-table th, .admin-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .admin-table th {
            background-color: #f5f5f5;
            color: #000000;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .button-red {
            background-color: #dc3545;
        }
        .button-yellow {
            background-color: #ffc107;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="/" class="nav-logo">
                <i class="ri-gamepad-line"></i>
                GameGroove
            </a>
            <div class="nav-links">
                <a href="/" class="nav-link">
                    <i class="ri-home-line"></i>
                    Главная
                </a>
                <a href="projects.php" class="nav-link">
                    <i class="ri-game-line"></i>
                    Проекты
                </a>
                <a href="dashboard.php" class="nav-link">
                    <i class="ri-user-line"></i>
                    Личный кабинет
                </a>
                <a href="admin.php" class="nav-link active">
                    <i class="ri-settings-line"></i>
                    Админ-панель
                </a>
                <a href="logout.php" class="nav-link">
                    <i class="ri-logout-box-line"></i>
                    Выйти
                </a>
            </div>
        </nav>
    </header>

    <main class="admin-panel">
        <h1>Панель администратора</h1>
        
        <section class="admin-section">
            <h2>Пользователи</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th>Дата регистрации</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <select name="role" onchange="this.form.submit()">
                                        <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : ''; ?>>Пользователь</option>
                                        <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Администратор</option>
                                    </select>
                                </form>
                            </td>
                            <td><?php echo date('d.m.Y H:i', strtotime($u['created_at'])); ?></td>
                            <td>
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="button button-red" onclick="return confirm('Вы уверены?')">Удалить</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <section class="admin-section">
            <h2>Проекты</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Разработчик</th>
                        <th>Цель</th>
                        <th>Собрано</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = $projects->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo htmlspecialchars($p['title']); ?></td>
                            <td><?php echo htmlspecialchars($p['developer_name']); ?></td>
                            <td><?php echo number_format($p['funding_goal'], 0, ',', ' '); ?> ₽</td>
                            <td><?php echo number_format($p['current_funding'], 0, ',', ' '); ?> ₽</td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="change_project_status">
                                    <input type="hidden" name="project_id" value="<?php echo $p['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="active" <?php echo $p['status'] === 'active' ? 'selected' : ''; ?>>Активный</option>
                                        <option value="completed" <?php echo $p['status'] === 'completed' ? 'selected' : ''; ?>>Завершен</option>
                                        <option value="cancelled" <?php echo $p['status'] === 'cancelled' ? 'selected' : ''; ?>>Отменен</option>
                                    </select>
                                </form>
                            </td>
                            <td class="action-buttons">
                                <a href="project.php?id=<?php echo $p['id']; ?>" class="button">Просмотр</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_project">
                                    <input type="hidden" name="project_id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" class="button button-red" onclick="return confirm('Вы уверены?')">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <section class="admin-section">
            <h2>Последние инвестиции</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Инвестор</th>
                        <th>Проект</th>
                        <th>Сумма</th>
                        <th>Дата</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($i = $investments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i['id']; ?></td>
                            <td><?php echo htmlspecialchars($i['investor_name']); ?></td>
                            <td><?php echo htmlspecialchars($i['project_title']); ?></td>
                            <td><?php echo number_format($i['amount'], 0, ',', ' '); ?> ₽</td>
                            <td><?php echo date('d.m.Y H:i', strtotime($i['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($i['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 GameGroove. Все права защищены.</p>
    </footer>
</body>
</html> 