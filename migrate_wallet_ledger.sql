-- Run once before deploying the idempotent refund helper.
CREATE TABLE IF NOT EXISTS wallet_ledger (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    entry_type VARCHAR(32) NOT NULL,
    reference_id VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    source VARCHAR(64) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_wallet_ledger_entry (entry_type, reference_id),
    KEY idx_wallet_ledger_user (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;