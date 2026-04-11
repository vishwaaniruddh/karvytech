-- Create installation progress attachments table
CREATE TABLE IF NOT EXISTS installation_progress_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    installation_id INT NOT NULL,
    progress_id INT NULL, -- Links to installation_progress table if exists
    attachment_type ENUM('final_report', 'site_snap', 'excel_sheet', 'drawing_attachment', 'other') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    uploaded_by INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    INDEX idx_installation_id (installation_id),
    INDEX idx_progress_id (progress_id),
    INDEX idx_attachment_type (attachment_type),
    FOREIGN KEY (installation_id) REFERENCES installation_delegations(id) ON DELETE CASCADE
);

-- Add columns to installation_progress table if it exists
-- (This will fail silently if table doesn't exist or columns already exist)
ALTER TABLE installation_progress 
ADD COLUMN has_final_report BOOLEAN DEFAULT FALSE,
ADD COLUMN has_site_snaps BOOLEAN DEFAULT FALSE,
ADD COLUMN has_excel_sheet BOOLEAN DEFAULT FALSE,
ADD COLUMN has_drawing_attachment BOOLEAN DEFAULT FALSE,
ADD COLUMN total_attachments INT DEFAULT 0;