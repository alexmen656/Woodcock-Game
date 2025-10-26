CREATE DATABASE IF NOT EXISTS woodcock_game 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE woodcock_game;

CREATE TABLE IF NOT EXISTS wg_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_online BOOLEAN DEFAULT FALSE,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wg_leaderboard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    total_points INT DEFAULT 0,
    nest_level INT DEFAULT 0,
    eggs INT DEFAULT 0,
    decorations INT DEFAULT 0,
    highscore INT DEFAULT 0,
    games_played INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES wg_users(id) ON DELETE CASCADE,
    INDEX idx_total_points (total_points DESC),
    INDEX idx_highscore (highscore DESC),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wg_friendships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES wg_users(id) ON DELETE CASCADE,
    FOREIGN KEY (friend_id) REFERENCES wg_users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_friendship (user_id, friend_id),
    INDEX idx_user_friendships (user_id),
    INDEX idx_friend_id (friend_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wg_game_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    score INT NOT NULL,
    leaves_collected INT DEFAULT 0,
    duration INT DEFAULT 0,
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES wg_users(id) ON DELETE CASCADE,
    INDEX idx_user_sessions (user_id),
    INDEX idx_played_at (played_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--mock data

INSERT INTO wg_users (username, email, is_online) VALUES
    ('WaldMeister', 'wald@example.com', TRUE),
    ('BaumFreund', 'baum@example.com', TRUE),
    ('NestBauer', 'nest@example.com', FALSE),
    ('Vogel123', 'vogel@example.com', TRUE),
    ('FeatherKing', 'feather@example.com', FALSE),
    ('TreeHugger', 'tree@example.com', TRUE),
    ('LeafCollector', 'leaf@example.com', FALSE),
    ('WoodcockPro', 'pro@example.com', TRUE),
    ('NatureLover', 'nature@example.com', TRUE),
    ('ForestRunner', 'forest@example.com', FALSE)
ON DUPLICATE KEY UPDATE username=username;

INSERT INTO wg_leaderboard (user_id, username, total_points, nest_level, eggs, decorations, highscore, games_played) VALUES
    (1, 'WaldMeister', 3200, 9, 5, 8, 850, 45),
    (2, 'BaumFreund', 2800, 8, 5, 7, 720, 38),
    (3, 'NestBauer', 2500, 7, 4, 6, 680, 32),
    (4, 'Vogel123', 2100, 7, 4, 5, 620, 29),
    (5, 'FeatherKing', 1800, 6, 3, 5, 580, 25),
    (6, 'TreeHugger', 1500, 5, 3, 4, 520, 22),
    (7, 'LeafCollector', 1200, 5, 2, 3, 480, 18),
    (8, 'WoodcockPro', 1000, 4, 2, 3, 450, 15),
    (9, 'NatureLover', 800, 3, 2, 2, 400, 12),
    (10, 'ForestRunner', 600, 3, 1, 2, 350, 10)
ON DUPLICATE KEY UPDATE total_points=VALUES(total_points);
