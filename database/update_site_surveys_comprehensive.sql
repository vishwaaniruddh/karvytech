-- Comprehensive update for site_surveys table to support detailed feasibility assessment

-- Add check-in/check-out fields
ALTER TABLE site_surveys 
ADD COLUMN checkin_datetime DATETIME NULL AFTER survey_date,
ADD COLUMN checkout_datetime DATETIME NULL AFTER checkin_datetime,
ADD COLUMN working_hours VARCHAR(100) NULL AFTER checkout_datetime;

-- Add site information fields
ALTER TABLE site_surveys 
ADD COLUMN store_model VARCHAR(255) NULL AFTER working_hours;

-- Add floor and ceiling assessment fields
ALTER TABLE site_surveys 
ADD COLUMN floor_height DECIMAL(5,2) NULL AFTER store_model,
ADD COLUMN floor_height_photos TEXT NULL AFTER floor_height,
ADD COLUMN ceiling_type ENUM('False', 'Open') NULL AFTER floor_height_photos,
ADD COLUMN ceiling_photos TEXT NULL AFTER ceiling_type;

-- Add camera assessment fields
ALTER TABLE site_surveys 
ADD COLUMN total_cameras INT NULL AFTER ceiling_photos,
ADD COLUMN analytic_cameras INT NULL AFTER total_cameras,
ADD COLUMN analytic_photos TEXT NULL AFTER analytic_cameras;

-- Add POE rack assessment fields
ALTER TABLE site_surveys 
ADD COLUMN existing_poe_rack INT NULL AFTER analytic_photos,
ADD COLUMN existing_poe_photos TEXT NULL AFTER existing_poe_rack,
ADD COLUMN space_new_rack ENUM('Yes', 'No') NULL AFTER existing_poe_photos,
ADD COLUMN space_new_rack_photos TEXT NULL AFTER space_new_rack,
ADD COLUMN new_poe_rack INT NULL AFTER space_new_rack_photos,
ADD COLUMN new_poe_photos TEXT NULL AFTER new_poe_rack;

-- Add zone assessment fields
ALTER TABLE site_surveys 
ADD COLUMN zones_recommended INT NULL AFTER new_poe_photos;

-- Add material status fields
ALTER TABLE site_surveys 
ADD COLUMN rrl_delivery_status ENUM('Yes', 'No') NULL AFTER zones_recommended,
ADD COLUMN rrl_photos TEXT NULL AFTER rrl_delivery_status,
ADD COLUMN kptl_space ENUM('Yes', 'No') NULL AFTER rrl_photos,
ADD COLUMN kptl_photos TEXT NULL AFTER kptl_space;

-- Update existing technical remarks field if it doesn't exist
ALTER TABLE site_surveys 
ADD COLUMN technical_remarks TEXT NULL AFTER kptl_photos;

-- Add challenges and recommendations fields if they don't exist
ALTER TABLE site_surveys 
ADD COLUMN challenges_identified TEXT NULL AFTER technical_remarks,
ADD COLUMN recommendations TEXT NULL AFTER challenges_identified,
ADD COLUMN estimated_completion_days INT NULL AFTER recommendations;

-- Create indexes for better performance
CREATE INDEX idx_site_surveys_checkin ON site_surveys(checkin_datetime);
CREATE INDEX idx_site_surveys_checkout ON site_surveys(checkout_datetime);
CREATE INDEX idx_site_surveys_store_model ON site_surveys(store_model);
CREATE INDEX idx_site_surveys_total_cameras ON site_surveys(total_cameras);