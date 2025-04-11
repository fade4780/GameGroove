<?php
require_once __DIR__ . '/includes/config.php';

// Получаем популярные проекты
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, u.username as developer_name,
           (SELECT COUNT(DISTINCT user_id) FROM investments WHERE project_id = p.id) as investors_count,
           (SELECT COALESCE(SUM(amount), 0) FROM investments WHERE project_id = p.id) as current_amount
    FROM projects p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE p.status = 'active'
    GROUP BY p.id
    ORDER BY (
        SELECT COALESCE(SUM(amount), 0) / p.goal_amount 
        FROM investments 
        WHERE project_id = p.id
    ) DESC
    LIMIT 5
");

$popular_projects = [];
while ($row = $stmt->fetch()) {
    $row['funding_percentage'] = calculateFundingPercentage($row['current_amount'], $row['goal_amount']);
    $popular_projects[] = $row;
}

include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Инвестируйте в будущее игровой индустрии</h1>
            <p>GameGroove - платформа, где разработчики игр находят инвесторов, а игроки становятся частью успешных проектов</p>
            <div class="hero-buttons">
                <?php if (!isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/pages/register.php" class="btn btn-primary">Регистрация</a>
                    <a href="<?php echo SITE_URL; ?>/pages/login.php" class="btn btn-outline">Вход</a>
                <?php elseif (isDeveloper()): ?>
                    <a href="<?php echo SITE_URL; ?>/pages/create_project.php" class="btn btn-primary">Создать проект</a>
                    <a href="<?php echo SITE_URL; ?>/pages/projects.php" class="btn btn-outline">Смотреть проекты</a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/pages/projects.php" class="btn btn-primary">Смотреть проекты</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($popular_projects)): ?>
<section class="popular-projects">
    <div class="container">
        <div class="section-header">
            <h2>Популярные проекты</h2>
            <a href="<?php echo SITE_URL; ?>/pages/projects.php" class="view-all">
                Смотреть все
                <i class="ri-arrow-right-line"></i>
            </a>
        </div>

        <div class="projects-grid">
            <?php foreach ($popular_projects as $project): ?>
                <div class="project-card">
                    <div class="project-image">
                        <?php if (!empty($project['image'])): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/<?php echo e($project['image']); ?>" alt="<?php echo e($project['title']); ?>">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="ri-image-line"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="project-info">
                        <h3><?php echo e($project['title']); ?></h3>
                        <p class="project-developer">
                            <i class="ri-user-line"></i>
                            <?php echo e($project['developer_name']); ?>
                        </p>
                        <p class="project-category">
                            <i class="ri-gamepad-line"></i>
                            <?php echo e($project['category_name']); ?>
                        </p>
                        <div class="project-progress">
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $project['funding_percentage']; ?>%"></div>
                            </div>
                            <div class="progress-stats">
                                <span><?php echo formatMoney($project['current_amount']); ?></span>
                                <span><?php echo $project['funding_percentage']; ?>%</span>
                            </div>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/pages/project.php?id=<?php echo $project['id']; ?>" class="btn btn-outline">Подробнее</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script>
    new Swiper('.popular-projects-slider', {
        slidesPerView: 1,
        spaceBetween: 20,
        pagination: {
            el: '.swiper-pagination',
            clickable: true
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev'
        },
        breakpoints: {
            640: {
                slidesPerView: 2
            },
            1024: {
                slidesPerView: 3
            }
        }
    });
</script>
<script src="assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
});
</script>
</body>
</html> 