-- Update vendors table with comprehensive vendor information
ALTER TABLE vendors 
ADD COLUMN vendor_code VARCHAR(50) UNIQUE AFTER id,
ADD COLUMN mobility_id VARCHAR(100) AFTER vendor_code,
ADD COLUMN mobility_password VARCHAR(255) AFTER mobility_id,
ADD COLUMN company_name VARCHAR(255) AFTER name,
ADD COLUMN bank_name VARCHAR(255) AFTER address,
ADD COLUMN account_number VARCHAR(50) AFTER bank_name,
ADD COLUMN ifsc_code VARCHAR(20) AFTER account_number,
ADD COLUMN gst_number VARCHAR(50) AFTER ifsc_code,
ADD COLUMN pan_card_number VARCHAR(20) AFTER gst_number,
ADD COLUMN aadhaar_number VARCHAR(20) AFTER pan_card_number,
ADD COLUMN msme_number VARCHAR(50) AFTER aadhaar_number,
ADD COLUMN esic_number VARCHAR(50) AFTER msme_number,
ADD COLUMN pf_number VARCHAR(50) AFTER esic_number,
ADD COLUMN pvc_status ENUM('Yes', 'No') DEFAULT 'No' AFTER pf_number,
ADD COLUMN experience_letter_path VARCHAR(500) AFTER pvc_status,
ADD COLUMN photograph_path VARCHAR(500) AFTER experience_letter_path;

-- Add indexes for better performance
ALTER TABLE vendors 
ADD INDEX idx_vendor_code (vendor_code),
ADD INDEX idx_gst_number (gst_number),
ADD INDEX idx_pan_card (pan_card_number);

-- Update existing vendors with sample vendor codes
UPDATE vendors SET vendor_code = CONCAT('VND', LPAD(id, 4, '0')) WHERE vendor_code IS NULL;