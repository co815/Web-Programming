CREATE DATABASE IF NOT EXISTS lab7_xo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lab7_xo;

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS game (
    id INT PRIMARY KEY AUTO_INCREMENT,
    player_x_id INT NOT NULL,
    player_o_id INT,
    board CHAR(9) NOT NULL DEFAULT '---------',
    status ENUM('waiting','active','finished') NOT NULL DEFAULT 'waiting',
    winner_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player_x_id) REFERENCES users(id),
    FOREIGN KEY (player_o_id) REFERENCES users(id),
    FOREIGN KEY (winner_id) REFERENCES users(id)
);
