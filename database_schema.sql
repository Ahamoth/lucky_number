-- В phpMyAdmin выполните этот SQL
CREATE DATABASE IF NOT EXISTS tg_lucky_number;
USE tg_lucky_number;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tg_id BIGINT UNIQUE NOT NULL,
    username VARCHAR(255),
    first_name VARCHAR(255) NOT NULL,
    balance DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id VARCHAR(100) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'RUB',
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    transaction_hash VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status ENUM('waiting', 'active', 'finished') DEFAULT 'waiting',
    ticket_price DECIMAL(10,2) NOT NULL,
    players_count INT DEFAULT 0,
    winners_count INT DEFAULT 3,
    prize_fund DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    finished_at TIMESTAMP NULL
);

CREATE TABLE game_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    user_id INT NOT NULL,
    ticket_number INT NOT NULL,
    is_winner BOOLEAN DEFAULT FALSE,
    prize_amount DECIMAL(10,2) DEFAULT 0.00,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);