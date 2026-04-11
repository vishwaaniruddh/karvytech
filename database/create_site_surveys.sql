-- Create site surveys table for vendor feasibility checks
CREATE TABLE IF NOT EXISTS site_surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    vendor_id INT NOT NULL,
    delegation_id INT NOT NULL,
    survey_status ENUM('pending', 'completed', 'approved', 'rejected') DEFAULT 'pending',
    survey_date DATETIME NULL,
    submitted_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Site feasibility details
    site_accessibility ENUM('good', 'moderate', 'poor') NULL,
    power_availability ENUM('available', 'partial', 'unavailable') NULL,
    network_connectivity ENUM('excellent', 'good', 'poor', 'none') NULL,
    space_adequacy ENUM('adequate', 'tight', 'inadequate') NULL,
    security_level ENUM('high', 'medium', 'low') NULL,
    
    -- Technical requirements
    electrical_work_required BOOLEAN DEFAULT FALSE,
    civil_work_required BOOLEAN DEFAULT FALSE,
    network_work_required BOOLEAN DEFAULT FALSE,
    additional_equipment_needed TEXT NULL,
    
    -- Survey findings
    site_photos TEXT NULL, -- JSON array of photo paths
    technical_remarks TEXT NULL,
    challenges_identified TEXT NULL,
    recommendations TEXT NULL,
    estimated_completion_days INT NULL,
    
    -- Approval workflow
    approved_by INT NULL,
    approved_date DATETIME NULL,
    approval_remarks TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (delegation_id) REFERENCES site_delegations(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_site_surveys_site_id (site_id),
    INDEX idx_site_surveys_vendor_id (vendor_id),
    INDEX idx_site_surveys_delegation_id (delegation_id),
    INDEX idx_site_surveys_status (survey_status)
);