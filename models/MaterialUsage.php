<?php
require_once __DIR__ . '/../config/database.php';

class MaterialUsage {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Initialize materials for an installation (called once when installation starts)
    public function initializeInstallationMaterials($installationId, $materials) {
        try {
            $this->db->beginTransaction();
            
            // Clear existing materials for this installation
            $stmt = $this->db->prepare("DELETE FROM installation_materials WHERE installation_id = ?");
            $stmt->execute([$installationId]);
            
            // Insert new materials
            $sql = "INSERT INTO installation_materials (installation_id, material_name, material_unit, total_quantity) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            foreach ($materials as $material) {
                $stmt->execute([
                    $installationId,
                    $material['name'],
                    $material['unit'],
                    $material['total_qty']
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    // Get all materials for an installation
    public function getInstallationMaterials($installationId) {
        $sql = "SELECT * FROM installation_materials WHERE installation_id = ? ORDER BY material_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$installationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Save daily material usage
    public function saveDailyMaterialUsage($installationId, $dayNumber, $workDate, $engineerName, $materialUsage, $remarks, $report) {
        try {
            $this->db->beginTransaction();
            
            $hasAnyMaterial = false;
            
            $currentDateTime = date('Y-m-d H:i:s');
            
            foreach ($materialUsage as $usage) {
                if ($usage['quantity_used'] > 0) {
                    $hasAnyMaterial = true;
                    $sql = "INSERT INTO daily_material_usage 
                            (installation_id, material_id, day_number, work_date, engineer_name, quantity_used, remarks, work_report, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE 
                            quantity_used = VALUES(quantity_used),
                            engineer_name = VALUES(engineer_name),
                            remarks = VALUES(remarks),
                            work_report = VALUES(work_report),
                            updated_at = VALUES(updated_at)";
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        $installationId,
                        $usage['material_id'],
                        $dayNumber,
                        $workDate,
                        $engineerName,
                        $usage['quantity_used'],
                        $remarks,
                        $report,
                        $currentDateTime,
                        $currentDateTime
                    ]);
                }
            }
            
            // If no materials were used, still create a record to track the day's work
            if (!$hasAnyMaterial) {
                $sql = "INSERT INTO daily_material_usage 
                        (installation_id, material_id, day_number, work_date, engineer_name, quantity_used, remarks, work_report, created_at, updated_at) 
                        VALUES (?, NULL, ?, ?, ?, 0, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        engineer_name = VALUES(engineer_name),
                        remarks = VALUES(remarks),
                        work_report = VALUES(work_report),
                        updated_at = VALUES(updated_at)";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $installationId,
                    $dayNumber,
                    $workDate,
                    $engineerName,
                    $remarks,
                    $report,
                    $currentDateTime,
                    $currentDateTime
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error saving daily material usage: " . $e->getMessage());
            return false;
        }
    }
    
    // Check out a day (update main material quantities)
    public function checkoutDay($installationId, $dayNumber) {
        try {
            $this->db->beginTransaction();
            
            // Get all material usage for this day
            $sql = "SELECT material_id, SUM(quantity_used) as total_used 
                    FROM daily_material_usage 
                    WHERE installation_id = ? AND day_number = ? AND is_checked_out = FALSE
                    GROUP BY material_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$installationId, $dayNumber]);
            $dayUsage = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Update main material quantities
            foreach ($dayUsage as $usage) {
                $updateSql = "UPDATE installation_materials 
                              SET used_quantity = used_quantity + ? 
                              WHERE id = ?";
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute([$usage['total_used'], $usage['material_id']]);
            }
            
            // Mark day as checked out
            $checkoutDateTime = date('Y-m-d H:i:s');
            $checkoutSql = "UPDATE daily_material_usage 
                            SET is_checked_out = TRUE, checked_out_at = ? 
                            WHERE installation_id = ? AND day_number = ?";
            $checkoutStmt = $this->db->prepare($checkoutSql);
            $checkoutStmt->execute([$checkoutDateTime, $installationId, $dayNumber]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    // Get daily work data for an installation
    public function getDailyWork($installationId) {
        $sql = "SELECT dmu.*, im.material_name 
                FROM daily_material_usage dmu
                LEFT JOIN installation_materials im ON dmu.material_id = im.id
                WHERE dmu.installation_id = ?
                ORDER BY dmu.day_number, im.material_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$installationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get daily work grouped by day
    public function getDailyWorkByDay($installationId) {
        $sql = "SELECT DISTINCT day_number, work_date, engineer_name, remarks, work_report, is_checked_out, checked_out_at
                FROM daily_material_usage 
                WHERE installation_id = ?
                ORDER BY day_number";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$installationId]);
        $days = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get material usage for each day
        foreach ($days as &$day) {
            $materialSql = "SELECT dmu.*, im.material_name 
                            FROM daily_material_usage dmu
                            LEFT JOIN installation_materials im ON dmu.material_id = im.id
                            WHERE dmu.installation_id = ? AND dmu.day_number = ? AND dmu.material_id IS NOT NULL";
            $materialStmt = $this->db->prepare($materialSql);
            $materialStmt->execute([$installationId, $day['day_number']]);
            $day['materials'] = $materialStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $days;
    }
    
    // Check if day is already checked out
    public function isDayCheckedOut($installationId, $dayNumber) {
        $sql = "SELECT COUNT(*) FROM daily_material_usage 
                WHERE installation_id = ? AND day_number = ? AND is_checked_out = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$installationId, $dayNumber]);
        return $stmt->fetchColumn() > 0;
    }
    
    // Get material usage summary
    public function getMaterialUsageSummary($installationId) {
        $sql = "SELECT 
                    im.*,
                    COALESCE(SUM(dmu.quantity_used), 0) as daily_total_used
                FROM installation_materials im
                LEFT JOIN daily_material_usage dmu ON im.id = dmu.material_id AND dmu.is_checked_out = TRUE
                WHERE im.installation_id = ?
                GROUP BY im.id
                ORDER BY im.material_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$installationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get daily material usage for a specific day
    public function getDailyMaterialUsage($installationId, $dayNumber) {
        $sql = "SELECT dmu.*, im.material_name, im.material_unit
                FROM daily_material_usage dmu
                JOIN installation_materials im ON dmu.material_id = im.id
                WHERE dmu.installation_id = ? AND dmu.day_number = ? AND dmu.quantity_used > 0
                ORDER BY im.material_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$installationId, $dayNumber]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get daily work photos for a specific day
    public function getDailyWorkPhotos($installationId, $dayNumber) {
        $sql = "SELECT * FROM daily_work_photos 
                WHERE installation_id = ? AND day_number = ?
                ORDER BY uploaded_at";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$installationId, $dayNumber]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Save daily work photo
    public function saveDailyWorkPhoto($installationId, $dayNumber, $fileName, $filePath, $fileType, $fileSize) {
        $sql = "INSERT INTO daily_work_photos 
                (installation_id, day_number, file_name, file_path, file_type, file_size) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$installationId, $dayNumber, $fileName, $filePath, $fileType, $fileSize]);
    }
    
    // Add received materials to installation quantities (from delivery confirmations)
    public function addReceivedMaterialsToInstallation($installationId, $materialName, $quantity, $unit, $notes = null) {
        try {
            // Check if material already exists in installation
            $checkSql = "SELECT id, total_quantity FROM installation_materials 
                         WHERE installation_id = ? AND material_name = ?";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$installationId, $materialName]);
            $existingMaterial = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingMaterial) {
                // Update existing material quantity
                $updateSql = "UPDATE installation_materials 
                              SET total_quantity = total_quantity + ?, 
                                  updated_at = CURRENT_TIMESTAMP 
                              WHERE id = ?";
                $updateStmt = $this->db->prepare($updateSql);
                $result = $updateStmt->execute([$quantity, $existingMaterial['id']]);
                
                // Log the material addition
                if ($notes) {
                    $logSql = "INSERT INTO material_addition_log 
                               (installation_id, material_id, quantity_added, notes, added_at) 
                               VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
                    $logStmt = $this->db->prepare($logSql);
                    $logStmt->execute([$installationId, $existingMaterial['id'], $quantity, $notes]);
                }
                
                return $result;
            } else {
                // Create new material entry
                $insertSql = "INSERT INTO installation_materials 
                              (installation_id, material_name, material_unit, total_quantity, used_quantity, created_at, updated_at) 
                              VALUES (?, ?, ?, ?, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
                $insertStmt = $this->db->prepare($insertSql);
                $result = $insertStmt->execute([$installationId, $materialName, $unit, $quantity]);
                
                // Log the material addition
                if ($result && $notes) {
                    $materialId = $this->db->lastInsertId();
                    $logSql = "INSERT INTO material_addition_log 
                               (installation_id, material_id, quantity_added, notes, added_at) 
                               VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
                    $logStmt = $this->db->prepare($logSql);
                    $logStmt->execute([$installationId, $materialId, $quantity, $notes]);
                }
                
                return $result;
            }
        } catch (Exception $e) {
            error_log("Error adding received materials to installation: " . $e->getMessage());
            return false;
        }
    }
}
?>