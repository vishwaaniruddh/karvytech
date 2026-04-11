-- Add day_number column to installation_progress_attachments table
-- This allows us to link attachments to specific daily work entries

ALTER TABLE installation_progress_attachments 
ADD COLUMN day_number INT NULL AFTER progress_id,
ADD INDEX idx_day_number (day_number);

-- Update description to be more flexible
ALTER TABLE installation_progress_attachments 
MODIFY COLUMN description TEXT NULL;
