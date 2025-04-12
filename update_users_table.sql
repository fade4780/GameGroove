-- Проверяем существование колонки created_at
SET @dbname = 'gamegroove';
SET @tablename = 'users';
SET @columnname = 'created_at';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  'ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Обновляем существующие колонки
ALTER TABLE users
MODIFY COLUMN password VARCHAR(255) NOT NULL,
MODIFY COLUMN role ENUM('user', 'admin') NOT NULL DEFAULT 'user'; 