-- Создание базы данных
DROP DATABASE IF EXISTS gamegroove;
CREATE DATABASE gamegroove;
USE gamegroove;

-- Создание таблицы пользователей
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'developer', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Создание таблицы категорий
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Создание таблицы проектов
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    short_description VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(255),
    goal_amount DECIMAL(10,2) NOT NULL,
    current_amount DECIMAL(10,2) DEFAULT 0,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date DATE NOT NULL,
    status ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'draft',
    category_id INT,
    developer_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (developer_id) REFERENCES users(id)
);

-- Создание таблицы инвестиций
CREATE TABLE investments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

-- Создание таблицы комментариев
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

-- Вставка тестовых данных

-- Пользователи
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@gamegroove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('developer1', 'dev1@gamegroove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'developer'),
('developer2', 'dev2@gamegroove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'developer'),
('investor1', 'inv1@gamegroove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('investor2', 'inv2@gamegroove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Категории
INSERT INTO categories (name, description) VALUES
('RPG', 'Ролевые игры с глубоким сюжетом и развитием персонажа'),
('Action', 'Динамичные игры с активным геймплеем'),
('Strategy', 'Стратегические игры с тактическим планированием'),
('Adventure', 'Приключенческие игры с исследованием мира'),
('Puzzle', 'Головоломки и логические игры'),
('Simulation', 'Симуляторы различных видов деятельности');

-- Проекты
INSERT INTO projects (title, short_description, description, image_url, goal_amount, current_amount, end_date, status, category_id, developer_id) VALUES
('Dark Chronicles', 'Мрачное фэнтезийное RPG с уникальной боевой системой', 'Погрузитесь в мир тьмы и магии, где каждое решение влияет на судьбу мира.', '/uploads/projects/dark_chronicles.jpg', 1000000, 750000, '2024-06-30', 'active', 1, 2),
('Space Commander', 'Космическая стратегия с элементами 4X', 'Исследуйте галактику, развивайте свою империю.', '/uploads/projects/space_commander.jpg', 500000, 300000, '2024-07-15', 'active', 3, 2),
('Mind Maze', 'Инновационная головоломка с процедурной генерацией', 'Каждый уровень уникален благодаря процедурной генерации.', '/uploads/projects/mind_maze.jpg', 200000, 180000, '2024-05-20', 'active', 5, 3),
('Farm Life', 'Симулятор фермы с элементами социального взаимодействия', 'Развивайте свою ферму, общайтесь с жителями деревни.', '/uploads/projects/farm_life.jpg', 300000, 150000, '2024-08-01', 'active', 6, 2),
('Ninja Way', 'Динамичный экшен о приключениях ниндзя', 'Станьте мастером боевых искусств.', '/uploads/projects/ninja_way.jpg', 800000, 600000, '2024-07-01', 'active', 2, 3);

-- Инвестиции
INSERT INTO investments (user_id, project_id, amount, created_at) VALUES
(4, 1, 250000, '2024-03-01'),
(5, 1, 500000, '2024-03-02'),
(4, 2, 150000, '2024-03-03'),
(5, 2, 150000, '2024-03-04'),
(4, 3, 90000, '2024-03-05'),
(5, 3, 90000, '2024-03-06'),
(4, 4, 75000, '2024-03-07'),
(5, 4, 75000, '2024-03-08'),
(4, 5, 300000, '2024-03-09'),
(5, 5, 300000, '2024-03-10');

-- Комментарии
INSERT INTO comments (user_id, project_id, content, created_at) VALUES
(4, 1, 'Отличный проект! Жду релиза с нетерпением.', '2024-03-01'),
(5, 1, 'Боевая система выглядит очень интересно.', '2024-03-02'),
(4, 2, 'Давно ждал хорошую космическую стратегию.', '2024-03-03'),
(5, 2, 'Графика впечатляет!', '2024-03-04'),
(4, 3, 'Интересная механика головоломок.', '2024-03-05'),
(5, 3, 'Процедурная генерация - отличная идея.', '2024-03-06'),
(4, 4, 'Очень уютная атмосфера.', '2024-03-07'),
(5, 4, 'Хочу поиграть в бета-версию!', '2024-03-08'),
(4, 5, 'Боевая система выглядит динамично.', '2024-03-09'),
(5, 5, 'Надеюсь на хороший сюжет.', '2024-03-10'); 