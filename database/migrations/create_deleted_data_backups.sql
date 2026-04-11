-- Create table to store backups of deleted data
-- This allows restoration of permanently deleted records

CREATE TABLE IF NOT EXISTS deleted_data_backups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL COMMENT 'Reference to superadmin_requests table',
    table_name VARCHAR(100) NOT NULL COMMENT 'Name of the table the data was deleted from',
    record_id INT NOT NULL COMMENT 'Original ID of the deleted record',
    data JSON NOT NULL COMMENT 'Complete record data as JSON',
    deleted_by INT NULL COMMENT 'User who approved the deletion',
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    restored_at TIMESTAMP NULL COMMENT 'When the record was restored',
    restored_by INT NULL COMMENT 'User who restored the record',
    status ENUM('deleted', 'restored') DEFAULT 'deleted',
    notes TEXT NULL COMMENT 'Additional notes about deletion/restoration',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_request_id (request_id),
    INDEX idx_table_name (table_name),
    INDEX idx_status (status),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_deleted_by (deleted_by),
    INDEX idx_restored_by (restored_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraints separately
ALTER TABLE deleted_data_backups
ADD CONSTRAINT fk_backup_request FOREIGN KEY (request_id) REFERENCES superadmin_requests(id) ON DELETE CASCADE;

ALTER TABLE deleted_data_backups
ADD CONSTRAINT fk_backup_deleted_by FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE deleted_data_backups
ADD CONSTRAINT fk_backup_restored_by FOREIGN KEY (restored_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add comments
ALTER TABLE deleted_data_backups 
COMMENT = 'Stores JSON backups of deleted data for potential restoration';

