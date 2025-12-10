CREATE TABLE money_savings_goals (
    goal_id INT AUTO_INCREMENT PRIMARY KEY,
    sys_user_id INT NOT NULL,
    goal_name VARCHAR(100) NOT NULL,
    target_amount DECIMAL(12,2) NOT NULL,
    current_saved DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    target_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
