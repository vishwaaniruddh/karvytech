-- Add new fields to site_surveys table for enhanced survey form
-- Run this migration to add all new fields

-- Time tracking fields
ALTER TABLE site_surveys 
ADD COLUMN IF NOT EXISTS checkin_datetime DATETIME NULL AFTER submitted_date,
ADD COLUMN IF NOT EXISTS checkout_datetime DATETIME NULL AFTER checkin_datetime,
ADD COLUMN IF NOT EXISTS working_hours VARCHAR(50) NULL AFTER checkout_datetime;

-- Site information
ALTER TABLE site_surveys 
ADD COLUMN IF NOT EXISTS store_model VARCHAR(255) NULL AFTER working_hours;

-- Floor and ceiling assessment
ALTER TABLE site_surveys 
ADD COLUMN IF NOT EXISTS floor_height DECIMAL(10,2) NULL AFTER store_model,
ADD COLUMN IF NOT EXISTS ceiling_type VARCHAR(50) NULL AFTER floor_height,
ADD COLUMN IF NOT EXISTS floor_height_photos TEXT NULL AFTER ceiling_type,
ADD COLUMN IF NOT EXISTS floor_height_photo_remarks TEXT NULL AFTER floor_height_photos,
ADD COLUMN IF NOT EXISTS ceiling_photos TEXT NULL AFTER floor_height_photo_remarks,
ADD COLUMN IF NOT EXISTS ceiling_photo_remarks TEXT NULL AFTER ceiling_photos;

-- Camera assessment
ALTER TABLE site_surveys 
ADD COLUMN IF NOT EXISTS total_cameras INT NULL AFTER ceiling_photo_remarks,
ADD COLUMN IF NOT EXISTS slp_cameras INT NULL AFTER total_cameras,
ADD COLUMN IF NOT EXISTS analytic_cameras INT NULL AFTER slp_cameras,
ADD COLUMN IF NOT EXISTS analytic_photos TEXT NULL AFTER analytic_cameras,
ADD COLUMN IF NOT EXISTS analytic_photos_remarks TEXT NULL AFTER analytic_photos;

-- POE Rack assessment
ALTER TABLE site_surveys 
ADD COLUMN IF NOT EXISTS existing_poe_rack INT NULL AFTER analytic_photos_remarks,
ADD COLUMN IF NOT EXISTS existing_poe_photos TEXT NULL AFTER existing_poe_rack,
ADD COLUMN IF NOT EXISTS existing_poe_photos_remarks TEXT NULL AFTER existing_poe_photos,
ADD COLUMN IF NOT EXISTS space_new_rack VARCHAR(10) NULL AFTER existing_poe_photos_remarks,
ADD COLUMN IF NOT EXISTS space_new_rack_photos TEXT NULL AFTER space_new_rack,
ADD COLUMN IF NOT EXISTS space_new_rack_photo_remarks TEXT NULL AFTER space_new_rack_photos,
ADD COLUMN IF NOT EXISTS new_poe_rack INT NULL AFTER space_new_rack_photo_remarks,
ADD COLUMN IF NOT EXISTS new_poe_photos TEXT NULL AFTER new_poe_rack,
ADD COLUMN IF NOT EXISTS new_poe_photos_remarks TEXT NULL AFTER new_poe_photos;

-- Zone assessment
ALTER TABLE site_surveys 
ADD COLUMN IF NOT EXISTS zones_recommended INT NULL AFTER new_poe_photos_remarks;

-- Material status
ALTER TABLE site_surveys 
ADD COLUMN IF NOT EXISTS rrl_delivery_status VARCHAR(10) NULL AFTER zones_recommended,
ADD COLUMN IF NOT EXISTS rrl_photos TEXT NULL AFTER rrl_delivery_status,
ADD COLUMN IF NOT EXISTS rrl_photos_remarks TEXT NULL AFTER rrl_photos,
ADD COLUMN IF NOT EXISTS kptl_space VARCHAR(10) NULL AFTER rrl_photos_remarks,
ADD COLUMN IF NOT EXISTS kptl_photos TEXT NULL AFTER kptl_space,
ADD COLUMN IF NOT EXISTS kptl_photos_remarks TEXT NULL AFTER kptl_photos;

-- Update technical assessment fields to support new values
ALTER TABLE site_surveys 
MODIFY COLUMN site_accessibility VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS site_accessibility_others VARCHAR(255) NULL AFTER site_accessibility,
MODIFY COLUMN power_availability VARCHAR(50) NULL;

-- Ladder requirements
ALTER TABLE site_surveys 
ADD COLUMN IF NOT EXISTS nos_of_ladder INT NULL AFTER power_availability,
ADD COLUMN IF NOT EXISTS ladder_size VARCHAR(20) NULL AFTER nos_of_ladder;

-- Photo remarks for site photos
ALTER TABLE site_surveys 
ADD COLUMN IF NOT EXISTS site_photos_remarks TEXT NULL AFTER site_photos;

-- Update indexes for better performance
CREATE INDEX IF NOT EXISTS idx_site_surveys_checkin ON site_surveys(checkin_datetime);
CREATE INDEX IF NOT EXISTS idx_site_surveys_submitted ON site_surveys(submitted_date);
