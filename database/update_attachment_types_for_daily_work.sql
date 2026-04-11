-- Update installation_progress_attachments table to support daily work attachments
-- This adds new attachment types for daily work progress tracking

ALTER TABLE installation_progress_attachments 
MODIFY COLUMN attachment_type ENUM(
    'final_report', 
    'site_snap', 
    'excel_sheet', 
    'drawing_attachment', 
    'daily_work_site',
    'daily_work_material',
    'other'
) NOT NULL;

-- Add index for better query performance on daily work attachments
CREATE INDEX IF NOT EXISTS idx_attachment_type_installation 
ON installation_progress_attachments(attachment_type, installation_id);
