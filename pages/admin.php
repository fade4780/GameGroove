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
                $db->query("DELETE FROM projects WHERE user_id = $user_id");
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
        
        header('Location: admin.php?tab=' . ($_GET['tab'] ?? 'dashboard'));
        exit();
    }
}

// Получаем статистику
$stats = [
    'users' => $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'projects' => $db->query("SELECT COUNT(*) as count FROM projects")->fetch_assoc()['count'],
    'active_projects' => $db->query("SELECT COUNT(*) as count FROM projects WHERE status = 'active'")->fetch_assoc()['count'],
    'total_invested' => $db->query("SELECT SUM(amount) as sum FROM investments")->fetch_assoc()['sum'] ?? 0
];

// Получаем данные в зависимости от выбранной вкладки
$current_tab = $_GET['tab'] ?? 'dashboard';

$users_query = "
    SELECT id, username, role, created_at 
    FROM users 
    ORDER BY created_at DESC
";
$users_result = $db->query($users_query);

$projects = $db->query("
    SELECT p.*, u.username as developer_name,
           COUNT(DISTINCT i.id) as investors_count,
           SUM(i.amount) as current_amount
    FROM projects p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN investments i ON p.id = i.project_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");

$recent_activities = $db->query("
    (SELECT 
        'investment' as type,
        i.created_at,
        u.username,
        p.title as target,
        i.amount as value
    FROM investments i
    JOIN users u ON i.investor_id = u.id
    JOIN projects p ON i.project_id = p.id
    ORDER BY i.created_at DESC
    LIMIT 5)
    UNION ALL
    (SELECT 
        'project' as type,
        p.created_at,
        u.username,
        p.title as target,
        p.goal_amount as value
    FROM projects p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
    LIMIT 5)
    ORDER BY created_at DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - GameGroove</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="admin-container">
        <div class="admin-header">
            <h1>Панель администратора</h1>
            <div class="header-actions">
                <button class="btn btn-primary">
                    <i class="ri-download-line"></i>
                    Экспорт данных
                </button>
            </div>
        </div>

        <div class="admin-stats">
            <div class="stat-card">
                <h3><i class="ri-user-line"></i> Пользователей</h3>
                <div class="value"><?php echo number_format($stats['users']); ?></div>
                <div class="trend up">
                    <i class="ri-arrow-up-line"></i>
                    <span>12% за месяц</span>
                </div>
            </div>
            <div class="stat-card">
                <h3><i class="ri-gamepad-line"></i> Проектов</h3>
                <div class="value"><?php echo number_format($stats['projects']); ?></div>
                <div class="trend up">
                    <i class="ri-arrow-up-line"></i>
                    <span>8% за месяц</span>
                </div>
            </div>
            <div class="stat-card">
                <h3><i class="ri-rocket-line"></i> Активных проектов</h3>
                <div class="value"><?php echo number_format($stats['active_projects']); ?></div>
                <div class="trend down">
                    <i class="ri-arrow-down-line"></i>
                    <span>3% за месяц</span>
                </div>
            </div>
            <div class="stat-card">
                <h3><i class="ri-money-dollar-circle-line"></i> Инвестировано</h3>
                <div class="value"><?php echo number_format($stats['total_invested']); ?> ₽</div>
                <div class="trend up">
                    <i class="ri-arrow-up-line"></i>
                    <span>15% за месяц</span>
                </div>
            </div>
        </div>

        <div class="admin-tabs">
            <button class="tab-button <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>" onclick="window.location.href='?tab=dashboard'">
                <i class="ri-dashboard-line"></i>
                Обзор
            </button>
            <button class="tab-button <?php echo $current_tab === 'users' ? 'active' : ''; ?>" onclick="window.location.href='?tab=users'">
                <i class="ri-user-line"></i>
                Пользователи
            </button>
            <button class="tab-button <?php echo $current_tab === 'projects' ? 'active' : ''; ?>" onclick="window.location.href='?tab=projects'">
                <i class="ri-gamepad-line"></i>
                Проекты
            </button>
            <button class="tab-button <?php echo $current_tab === 'settings' ? 'active' : ''; ?>" onclick="window.location.href='?tab=settings'">
                <i class="ri-settings-line"></i>
                Настройки
            </button>
        </div>

        <?php if ($current_tab === 'dashboard'): ?>
            <div class="admin-table-container">
                <div class="table-header">
                    <h2>Последние действия</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Тип</th>
                            <th>Пользователь</th>
                            <th>Действие</th>
                            <th>Значение</th>
                            <th>Дата</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($activity = $recent_activities->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="status-badge <?php echo $activity['type'] === 'investment' ? 'status-active' : 'status-pending'; ?>">
                                        <i class="ri-<?php echo $activity['type'] === 'investment' ? 'money-dollar-circle-line' : 'gamepad-line'; ?>"></i>
                                        <?php echo $activity['type'] === 'investment' ? 'Инвестиция' : 'Новый проект'; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($activity['username']); ?></td>
                                <td><?php echo htmlspecialchars($activity['target']); ?></td>
                                <td><?php echo number_format($activity['value']); ?> ₽</td>
                                <td><?php echo date('d.m.Y H:i', strtotime($activity['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($current_tab === 'users'): ?>
            <div class="admin-table-container">
                <div class="table-header">
                    <h2>Пользователи</h2>
                    <div class="table-actions">
                        <input type="text" class="form-control" placeholder="Поиск пользователей...">
                        <button class="btn btn-primary">
                            <i class="ri-add-line"></i>
                            Добавить пользователя
                        </button>
                    </div>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя пользователя</th>
                            <th>Роль</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td>
                                    <form method="POST" class="role-form">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <select name="role" onchange="this.form.submit()">
                                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Пользователь</option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Администратор</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <form method="POST" class="delete-form" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="delete-button">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($current_tab === 'projects'): ?>
            <div class="admin-table-container">
                <div class="table-header">
                    <h2>Проекты</h2>
                    <div class="table-actions">
                        <input type="text" class="form-control" placeholder="Поиск проектов...">
                        <button class="btn btn-primary">
                            <i class="ri-add-line"></i>
                            Добавить проект
                        </button>
                    </div>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Разработчик</th>
                            <th>Цель</th>
                            <th>Собрано</th>
                            <th>Инвесторов</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($p = $projects->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $p['id']; ?></td>
                                <td>
                                    <div class="project-info">
                                        <img src="<?php echo $p['image_url'] ?: SITE_URL . '/assets/images/placeholder.jpg'; ?>" alt="Project" class="project-thumbnail">
                                        <?php echo htmlspecialchars($p['title']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($p['developer_name']); ?></td>
                                <td><?php echo number_format($p['goal_amount'], 0, ',', ' '); ?> ₽</td>
                                <td><?php echo number_format($p['current_amount'] ?? 0, 0, ',', ' '); ?> ₽</td>
                                <td><?php echo $p['investors_count']; ?></td>
                                <td>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="action" value="change_project_status">
                                        <input type="hidden" name="project_id" value="<?php echo $p['id']; ?>">
                                        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="active" <?php echo $p['status'] === 'active' ? 'selected' : ''; ?>>Активный</option>
                                            <option value="completed" <?php echo $p['status'] === 'completed' ? 'selected' : ''; ?>>Завершен</option>
                                            <option value="cancelled" <?php echo $p['status'] === 'cancelled' ? 'selected' : ''; ?>>Отменен</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a href="<?php echo SITE_URL; ?>/pages/project.php?id=<?php echo $p['id']; ?>" class="action-btn" title="Просмотр">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <button class="action-btn" title="Редактировать">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="action" value="delete_project">
                                            <input type="hidden" name="project_id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" class="action-btn delete" title="Удалить" onclick="return confirm('Вы уверены?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($current_tab === 'settings'): ?>
            <div class="admin-form">
                <h2>Настройки сайта</h2>
                <form method="POST" class="settings-form">
                    <div class="form-group">
                        <label>Название сайта</label>
                        <input type="text" class="form-control" value="GameGroove" name="site_name">
                    </div>
                    <div class="form-group">
                        <label>Описание</label>
                        <textarea class="form-control" name="site_description" rows="3">Платформа для краудфандинга игровых проектов</textarea>
                    </div>
                    <div class="form-group">
                        <label>Email администратора</label>
                        <input type="email" class="form-control" value="admin@gamegroove.com" name="admin_email">
                    </div>
                    <div class="form-group">
                        <label>Комиссия платформы (%)</label>
                        <input type="number" class="form-control" value="5" name="platform_fee">
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить настройки</button>
                </form>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 