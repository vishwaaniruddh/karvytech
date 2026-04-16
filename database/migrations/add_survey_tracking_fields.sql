-- Add fields to track survey progress and timing
-- This allows users to start, pause, resume, and preview surveys before final submission

ALTER TABLE dynamic_survey_responses 
ADD COLUMN IF NOT EXISTS survey_started_at DATETIME NULL COMMENT 'When the survey was started',
ADD COLUMN IF NOT EXISTS survey_ended_at DATETIME NULL COMMENT 'When the survey was ended (before preview)',
ADD COLUMN IF NOT EXISTS is_draft TINYINT(1) DEFAULT 1 COMMENT '1 = draft/in-progress, 0 = submitted',
ADD COLUMN IF NOT EXISTS last_saved_at DATETIME NULL COMMENT 'Last time the draft was saved',
ADD COLUMN IF NOT EXISTS approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' COMMENT 'Approval status after submission',
ADD COLUMN IF NOT EXISTS approved_by INT NULL COMMENT 'User ID who approved',
ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL COMMENT 'When it was approved',
ADD COLUMN IF NOT EXISTS rejection_reason TEXT NULL COMMENT 'Reason for rejection if rejected';

-- Add index for faster queries
CREATE INDEX IF NOT EXISTS idx_survey_draft ON dynamic_survey_responses(is_draft);
CREATE INDEX IF NOT EXISTS idx_survey_approval ON dynamic_survey_responses(approval_status);
CREATE INDEX IF NOT EXISTS idx_delegation_draft ON dynamic_survey_responses(delegation_id, is_draft);
