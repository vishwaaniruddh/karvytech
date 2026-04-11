-- Add missing fields to site_surveys table
-- This adds only the fields that are missing

-- Photo remarks fields
ALTER TABLE site_surveys ADD COLUMN floor_height_photo_remarks TEXT NULL;
ALTER TABLE site_surveys ADD COLUMN ceiling_photo_remarks TEXT NULL;
ALTER TABLE site_surveys ADD COLUMN slp_cameras INT NULL;
ALTER TABLE site_surveys ADD COLUMN analytic_photos_remarks TEXT NULL;
ALTER TABLE site_surveys ADD COLUMN existing_poe_photos_remarks TEXT NULL;
ALTER TABLE site_surveys ADD COLUMN space_new_rack_photo_remarks TEXT NULL;
ALTER TABLE site_surveys ADD COLUMN new_poe_photos_remarks TEXT NULL;
ALTER TABLE site_surveys ADD COLUMN rrl_photos_remarks TEXT NULL;
ALTER TABLE site_surveys ADD COLUMN kptl_photos_remarks TEXT NULL;
ALTER TABLE site_surveys ADD COLUMN site_photos_remarks TEXT NULL;

-- Technical assessment new fields
ALTER TABLE site_surveys ADD COLUMN site_accessibility_others VARCHAR(255) NULL;
ALTER TABLE site_surveys ADD COLUMN nos_of_ladder INT NULL;
ALTER TABLE site_surveys ADD COLUMN ladder_size VARCHAR(20) NULL;

-- Update existing columns to support new values
ALTER TABLE site_surveys MODIFY COLUMN site_accessibility VARCHAR(50) NULL;
ALTER TABLE site_surveys MODIFY COLUMN power_availability VARCHAR(50) NULL;
