CREATE TABLE investments (
    investment_id INT AUTO_INCREMENT PRIMARY KEY,
    sys_user_id INT NOT NULL,
    account_id INT NOT NULL,
    asset_name VARCHAR(50) NOT NULL,
    asset_type VARCHAR(20) NOT NULL,
    amount_invested DECIMAL(12,2) NOT NULL,
    created_at DATE NOT NULL
);