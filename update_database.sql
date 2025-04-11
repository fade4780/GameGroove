-- Создание таблицы категорий
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Добавление базовых категорий
INSERT INTO categories (name, description) VALUES
('Экшен', 'Игры с активным геймплеем и быстрым темпом'),
('РПГ', 'Ролевые игры с развитием персонажа'),
('Стратегии', 'Игры, требующие стратегического мышления'),
('Головоломки', 'Игры на логику и сообразительность'),
('Симуляторы', 'Игры, имитирующие различные виды деятельности'),
('Приключения', 'Игры с упором на исследование и сюжет');

-- Добавление колонок в таблицу проектов
ALTER TABLE projects 
ADD COLUMN category_id INT,
ADD COLUMN image_path VARCHAR(255),
ADD FOREIGN KEY (category_id) REFERENCES categories(id);

-- Создание директории для загрузки изображений
-- Выполните вручную: mkdir -p uploads/projects 