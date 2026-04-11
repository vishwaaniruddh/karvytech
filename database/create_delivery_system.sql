-- Create delivery notifications table
CREATE TABLE IF NOT EXISTS delivery_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    dispatch_id INT NOT NULL,
    vendor_id INT NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'delivery_confirmation',
    is_read BOOLEAN DEFAULT FALSE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    read_by INT NULL,
    
    FOREIGN KEY (request_id) REFERENCES material_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (dispatch_id) REFERENCES inventory_dispatches(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (read_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_request_id (request_id),
    INDEX idx_dispatch_id (dispatch_id),
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- Add delivery confirmation columns to inventory_dispatches table
ALTER TABLE inventory_dispatches 
ADD COLUMN IF NOT EXISTS delivery_date DATE NULL,
ADD COLUMN IF NOT EXISTS delivery_time TIME NULL,
ADD COLUMN IF NOT EXISTS received_by VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS received_by_phone VARCHAR(20) NULL,
ADD COLUMN IF NOT EXISTS actual_delivery_address TEXT NULL,
ADD COLUMN IF NOT EXISTS delivery_notes TEXT NULL,
ADD COLUMN IF NOT EXISTS lr_copy_path VARCHAR(500) NULL,
ADD COLUMN IF NOT EXISTS additional_documents TEXT NULL,
ADD COLUMN IF NOT EXISTS item_confirmations TEXT NULL,
ADD COLUMN IF NOT EXISTS confirmed_by INT NULL,
ADD COLUMN IF NOT EXISTS confirmation_date TIMESTAMP NULL,

ADD FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add indexes for better performance
ALTER TABLE inventory_dispatches 
ADD INDEX IF NOT EXISTS idx_dispatch_status (dispatch_status),
ADD INDEX IF NOT EXISTS idx_delivery_date (delivery_date),
ADD INDEX IF NOT EXISTS idx_confirmed_by (confirmed_by);

-- Update existing dispatches to have proper created_at column if missing
ALTER TABLE inventory_dispatches 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Create uploads directory structure (this would be done via PHP)
-- uploads/delivery_confirmations/ will be created by the PHP code