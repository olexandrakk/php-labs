CREATE DATABASE IF NOT EXISTS practicum4
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE practicum4;

CREATE TABLE IF NOT EXISTS topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    author VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_pinned TINYINT(1) NOT NULL DEFAULT 0
);

INSERT INTO topics (title, author, is_pinned) VALUES
    ('Правила форуму', 'Адміністратор', 1),
    ('Як налаштувати XAMPP для PHP?', 'Андрій К.', 0),
    ('Різниця між echo та print', 'Марія С.', 0),
    ('Асоціативні масиви: приклади', 'Андрій К.', 0);