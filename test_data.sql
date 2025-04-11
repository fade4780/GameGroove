-- Добавление тестовых пользователей
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@gamegroove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), -- пароль: password
('dev1', 'dev1@gamegroove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('dev2', 'dev2@gamegroove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('investor1', 'investor1@gamegroove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('investor2', 'investor2@gamegroove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Добавление тестовых проектов
INSERT INTO projects (developer_id, title, description, funding_goal, current_funding, status) VALUES
(2, 'Космическая стратегия', 'Увлекательная стратегия в космическом сеттинге с уникальной боевой системой', 500000.00, 150000.00, 'active'),
(2, 'РПГ в стиле киберпанк', 'Ролевая игра в мире будущего с глубоким сюжетом и нелинейным прохождением', 750000.00, 300000.00, 'active'),
(3, 'Мобильная головоломка', 'Инновационная головоломка с механикой физики и красивой графикой', 250000.00, 100000.00, 'active'),
(3, 'Хоррор-квест', 'Психологический хоррор с элементами квеста и уникальной атмосферой', 400000.00, 200000.00, 'active');

-- Добавление тестовых инвестиций
INSERT INTO investments (project_id, investor_id, amount) VALUES
(1, 4, 50000.00),
(1, 5, 100000.00),
(2, 4, 150000.00),
(2, 5, 150000.00),
(3, 4, 50000.00),
(3, 5, 50000.00),
(4, 4, 100000.00),
(4, 5, 100000.00);

-- Добавление тестовых комментариев
INSERT INTO comments (project_id, user_id, content) VALUES
(1, 4, 'Отличная идея! Особенно понравилась боевая система.'),
(1, 5, 'Жду релиза! Уже инвестировал в проект.'),
(2, 4, 'Интересный сеттинг и глубокий сюжет.'),
(2, 5, 'Надеюсь, игра оправдает ожидания.'),
(3, 4, 'Простая, но увлекательная механика.'),
(3, 5, 'Отлично подойдет для мобильных устройств.'),
(4, 4, 'Атмосфера просто потрясающая!'),
(4, 5, 'Люблю хорроры, особенно с элементами квеста.'); 