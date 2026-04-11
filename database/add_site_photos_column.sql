-- Add site_photos column to site_surveys table if it doesn't exist
-- This ensures the general site photos can be stored

-- Check if column exists and add it if missing
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'site_surveys' 
  AND COLUMN_NAME = 'site_photos';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE site_surveys ADD COLUMN site_photos TEXT NULL COMMENT "JSON array of general site photo paths" AFTER kptl_photos',
    'SELECT "Column site_photos already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify the column was added
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'site_surveys' 
  AND COLUMN_NAME = 'site_photos';