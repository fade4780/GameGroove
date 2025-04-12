<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/project.css">

<div class="project-preview">
    <div class="preview-header">
        <h1>Предпросмотр проекта</h1>
        <div class="preview-notice">
            <i class="ri-eye-line"></i>
            <span>Это предварительный просмотр. Проект ещё не опубликован.</span>
        </div>
    </div>

    <div id="projectContent">
        <div class="project-loading">
            <i class="ri-loader-4-line"></i>
            <span>Загрузка проекта...</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Получаем данные проекта из localStorage
    const projectData = JSON.parse(localStorage.getItem('projectPreview'));
    
    if (!projectData) {
        document.getElementById('projectContent').innerHTML = `
            <div class="preview-error">
                <i class="ri-error-warning-line"></i>
                <h2>Данные проекта не найдены</h2>
                <p>Вернитесь на страницу создания проекта и попробуйте снова.</p>
                <a href="<?php echo SITE_URL; ?>/pages/create_project.php" class="btn btn-primary">
                    <i class="ri-arrow-left-line"></i>
                    Вернуться к созданию проекта
                </a>
            </div>
        `;
        return;
    }

    // Загружаем категорию проекта
    fetch(`<?php echo SITE_URL; ?>/api/get_category.php?id=${projectData.category_id}`)
        .then(response => response.json())
        .then(category => {
            // Форматируем сумму
            const formattedAmount = new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                minimumFractionDigits: 0
            }).format(projectData.goal_amount);

            // Отображаем проект
            document.getElementById('projectContent').innerHTML = `
                <div class="project-details">
                    <div class="project-main">
                        <div class="project-header">
                            <h1>${projectData.title}</h1>
                            <span class="project-category">
                                <i class="ri-gamepad-line"></i>
                                ${category.name}
                            </span>
                        </div>

                        <div class="project-media">
                            <div class="media-placeholder">
                                <i class="ri-image-line"></i>
                                <span>Здесь будут отображаться загруженные изображения</span>
                            </div>
                        </div>

                        <div class="project-description">
                            <h2>Описание проекта</h2>
                            ${formatDescription(projectData.description)}
                        </div>
                    </div>

                    <div class="project-sidebar">
                        <div class="funding-status preview">
                            <div class="funding-goal">
                                <h3>Цель</h3>
                                <div class="amount">${formattedAmount}</div>
                            </div>
                            
                            <div class="funding-progress">
                                <div class="progress-bar">
                                    <div class="progress" style="width: 0%"></div>
                                </div>
                                <div class="progress-stats">
                                    <div class="stat">
                                        <span class="label">Собрано</span>
                                        <span class="value">0 ₽</span>
                                    </div>
                                    <div class="stat">
                                        <span class="label">Осталось дней</span>
                                        <span class="value">${projectData.duration}</span>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-primary btn-large" disabled>
                                <i class="ri-money-dollar-circle-line"></i>
                                Поддержать проект
                            </button>
                        </div>

                        <div class="project-creator preview">
                            <h3>Разработчик</h3>
                            <div class="creator-info">
                                <img src="<?php echo SITE_URL; ?>/assets/images/default-avatar.png" alt="Avatar" class="creator-avatar">
                                <div class="creator-details">
                                    <div class="creator-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                                    <div class="creator-stats">
                                        <span>0 проектов</span>
                                        <span>•</span>
                                        <span>Новый разработчик</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Ошибка загрузки категории:', error);
            document.getElementById('projectContent').innerHTML = `
                <div class="preview-error">
                    <i class="ri-error-warning-line"></i>
                    <h2>Ошибка загрузки данных</h2>
                    <p>Произошла ошибка при загрузке данных проекта. Пожалуйста, попробуйте снова.</p>
                </div>
            `;
        });
});

function formatDescription(text) {
    return text
        .split('\n')
        .map(paragraph => paragraph.trim())
        .filter(paragraph => paragraph.length > 0)
        .map(paragraph => `<p>${paragraph}</p>`)
        .join('');
}
</script>

<?php include '../includes/footer.php'; ?> 