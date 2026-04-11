-- Update site_surveys table to match the expected schema
-- First, let's add the missing columns

-- Add delegation_id column
ALTER TABLE site_surveys 
ADD COLUMN delegation_id INT NULL AFTER vendor_id;

-- Update survey_status column to match expected enum values
ALTER TABLE site_surveys 
MODIFY COLUMN status ENUM('pending', 'completed', 'approved', 'rejected') DEFAULT 'pending';

-- Rename status to survey_status for consistency
ALTER TABLE site_surveys 
CHANGE COLUMN status survey_status ENUM('pending', 'completed', 'approved', 'rejected') DEFAULT 'pending';

-- Add submitted_date column
ALTER TABLE site_surveys 
ADD COLUMN submitted_date DATETIME DEFAULT CURRENT_TIMESTAMP AFTER survey_date;

-- Add site feasibility columns
ALTER TABLE site_surveys 
ADD COLUMN site_accessibility ENUM('good', 'moderate', 'poor') NULL AFTER submitted_date,
ADD COLUMN power_availability ENUM('available', 'partial', 'unavailable') NULL AFTER site_accessibility,
ADD COLUMN network_connectivity ENUM('excellent', 'good', 'poor', 'none') NULL AFTER power_availability,
ADD COLUMN space_adequacy ENUM('adequate', 'tight', 'inadequate') NULL AFTER network_connectivity,
ADD COLUMN security_level ENUM('high', 'medium', 'low') NULL AFTER space_adequacy;

-- Add technical requirements columns
ALTER TABLE site_surveys 
ADD COLUMN electrical_work_required BOOLEAN DEFAULT FALSE AFTER security_level,
ADD COLUMN civil_work_required BOOLEAN DEFAULT FALSE AFTER electrical_work_required,
ADD COLUMN network_work_required BOOLEAN DEFAULT FALSE AFTER civil_work_required,
ADD COLUMN additional_equipment_needed TEXT NULL AFTER network_work_required;

-- Add survey findings columns
ALTER TABLE site_surveys 
ADD COLUMN site_photos TEXT NULL AFTER additional_equipment_needed,
ADD COLUMN technical_remarks TEXT NULL AFTER site_photos,
ADD COLUMN challenges_identified TEXT NULL AFTER technical_remarks,
ADD COLUMN recommendations TEXT NULL AFTER challenges_identified,
ADD COLUMN estimated_completion_days INT NULL AFTER recommendations;

-- Add approval workflow columns
ALTER TABLE site_surveys 
ADD COLUMN approved_by INT NULL AFTER estimated_completion_days,
ADD COLUMN approved_date DATETIME NULL AFTER approved_by,
ADD COLUMN approval_remarks TEXT NULL AFTER approved_date;

-- Add foreign key constraints
ALTER TABLE site_surveys 
ADD CONSTRAINT fk_site_surveys_delegation 
FOREIGN KEY (delegation_id) REFERENCES site_delegations(id) ON DELETE CASCADE;

ALTER TABLE site_surveys 
ADD CONSTRAINT fk_site_surveys_approved_by 
FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add indexes
CREATE INDEX idx_site_surveys_delegation_id ON site_surveys(delegation_id);
CREATE INDEX idx_site_surveys_survey_status ON site_surveys(survey_status);

-- Update survey_date to DATETIME to match schema
ALTER TABLE site_surveys 
MODIFY COLUMN survey_date DATETIME NULL;