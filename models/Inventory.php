<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

class Inventory {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // ==================== STOCK MANAGEMENT ====================
    
    public function getStockOverview($search = '', $category = '', $lowStock = false, $warehouseId = '') {
        $params = [];
        $conditions = [];
        
        try {
            // If warehouse filter is applied, use warehouse-specific query
            if (!empty($warehouseId)) {
                if (!empty($search)) {
                    $conditions[] = "(item_name LIKE ? OR item_code LIKE ?)";
                    $searchTerm = "%$search%";
                    $params = array_merge($params, [$searchTerm, $searchTerm]);
                }
                
                if (!empty($category)) {
                    $conditions[] = "category = ?";
                    $params[] = $category;
                }
                
                $conditions[] = "warehouse_id = ?";
                $params[] = $warehouseId;
                
                $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
                
                // Use warehouse_stock_summary view for warehouse-specific data
                $sql = "SELECT * FROM warehouse_stock_summary $whereClause ORDER BY item_name";
            } else {
                // Use global inventory summary
                if (!empty($search)) {
                    $conditions[] = "(item_name LIKE ? OR item_code LIKE ?)";
                    $searchTerm = "%$search%";
                    $params = array_merge($params, [$searchTerm, $searchTerm]);
                }
                
                if (!empty($category)) {
                    $conditions[] = "category = ?";
                    $params[] = $category;
                }
                
                $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
                
                // Use the inventory_summary view for aggregated stock data across all warehouses
                $sql = "SELECT *, 'All Warehouses' as warehouse_name FROM inventory_summary $whereClause ORDER BY item_name";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ensure warehouse_name exists in all results
            foreach ($results as &$result) {
                if (!isset($result['warehouse_name'])) {
                    $result['warehouse_name'] = 'All Warehouses';
                }
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getStockOverview: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAvailableStock($boqItemId, $requiredQuantity = null) {
        $sql = "SELECT ist.*, bi.item_name, bi.item_code, bi.unit
                FROM inventory_stock ist
                JOIN boq_items bi ON CAST(ist.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                WHERE ist.boq_item_id = ? 
                AND CAST(ist.item_status AS CHAR) = CAST('available' AS CHAR)
                AND CAST(ist.quality_status AS CHAR) = CAST('good' AS CHAR)
                ORDER BY ist.id ASC";
        
        $params = [$boqItemId];
        if ($requiredQuantity) {
            $sql .= " LIMIT ?";
            $params[] = $requiredQuantity;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getStockBySerialNumbers($serialNumbers) {
        if (empty($serialNumbers)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($serialNumbers) - 1) . '?';
        $sql = "SELECT ist.*, bi.item_name, bi.item_code, bi.unit
                FROM inventory_stock ist
                JOIN boq_items bi ON CAST(ist.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                WHERE ist.serial_number IN ($placeholders)
                AND CAST(ist.item_status AS CHAR) = CAST('available' AS CHAR)
                ORDER BY ist.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($serialNumbers);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getIndividualStockEntries($boqItemId = null, $search = '', $location = '', $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        if ($boqItemId) {
            $conditions[] = "ist.boq_item_id = ?";
            $params[] = $boqItemId;
        }
        
        if (!empty($search)) {
            $conditions[] = "(bi.item_name LIKE ? OR bi.item_code LIKE ? OR ist.batch_number LIKE ? OR ist.serial_number LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($location)) {
            $conditions[] = "ist.location_type = ?";
            $params[] = $location;
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) 
                     FROM inventory_stock ist
                     JOIN boq_items bi ON CAST(ist.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                     $whereClause";
        
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results
        $sql = "SELECT ist.*, bi.item_name, bi.item_code, bi.unit, bi.category, bi.icon_class
                FROM inventory_stock ist
                JOIN boq_items bi ON CAST(ist.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                $whereClause
                ORDER BY bi.item_name, ist.batch_number, ist.serial_number
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'entries' => $entries,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    public function getStockByItem($boqItemId) {
        $sql = "SELECT ist.*, bi.item_name, bi.item_code, bi.unit, bi.category
                FROM inventory_stock ist
                JOIN boq_items bi ON CAST(ist.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                WHERE ist.boq_item_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$boqItemId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateStockLevels($boqItemId, $minStock, $maxStock, $unitCost) {
        $currentUser = Auth::getCurrentUser();
        
        // In the new schema, we don't have min/max stock per item
        // This method is now primarily for updating unit costs
        if ($unitCost > 0) {
            $sql = "UPDATE inventory_stock 
                    SET unit_cost = ?, updated_by = ?
                    WHERE boq_item_id = ? AND CAST(item_status AS CHAR) = CAST('available' AS CHAR)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$unitCost, $currentUser['id'], $boqItemId]);
        } else {
            // Just update the updated_by field to mark as processed
            $sql = "UPDATE inventory_stock 
                    SET updated_by = ?
                    WHERE boq_item_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$currentUser['id'], $boqItemId]);
        }
        
        return $result;
    }
    
    
    public function inventoryAuditLogging($itemId){
        
        
    }
    
    // public function deleteIndividualStock($itemId){
        
    //     // update Inventory activity_status = deleted
        
    //      $sql = "UPDATE inventory_stock 
    //                 SET activity_status="deleted"
    //                 WHERE id=".$itemId."";
        
    //     // make a new table inventory_audit 
        
        
        
    // }


    // public function deleteIndividualStock($itemId)
    // {
    //     $currentUser = Auth::getCurrentUser();
    
    //     $sql = "UPDATE inventory_stock
    //             SET activity_status = 'deleted', item_status = 'deleted',
    //                 updated_by = ?
    //             WHERE id = ?";
    
    //     $stmt = $this->db->prepare($sql);
    //     return $stmt->execute([
    //         $currentUser['id'],
    //         $itemId
    //     ]);
    // }


   public function deleteIndividualStock($itemId, $remark)
    {
        $currentUser = Auth::getCurrentUser();
    
        $sql = "
            UPDATE inventory_stock
            SET activity_status = 'deleted',
                item_status = 'deleted',
                delete_remark = ?,
                updated_by = ?
            WHERE id = ?
        ";
    
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $remark,
            $currentUser['id'],
            $itemId
        ]);
    }




    
    
    public function updateStockLevelsNew($boqItemId, $minStock, $maxStock, $unitCost, $stockQuantity) {
        $currentUser = Auth::getCurrentUser();
        
        // In the new schema, we don't have min/max stock per item
        // This method is now primarily for updating unit costs
        if ($unitCost > 0) {
            $sql = "UPDATE inventory_stock 
                    SET unit_cost = ?, updated_by = ?
                    WHERE boq_item_id = ? AND CAST(item_status AS CHAR) = CAST('available' AS CHAR)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$unitCost, $currentUser['id'], $boqItemId]);
            
             $sql_stock = "UPDATE inventory_summary 
                    SET available_stock = ?
                    WHERE boq_item_id = ?";
            
            $stmt_stock = $this->db->prepare($sql_stock);
            $result_stock = $stmt->execute([$stockQuantity, $boqItemId]);
            
        } else {
            // Just update the updated_by field to mark as processed
            $sql = "UPDATE inventory_stock 
                    SET updated_by = ?
                    WHERE boq_item_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$currentUser['id'], $boqItemId]);
            
            $sql_stock = "UPDATE inventory_summary 
                    SET available_stock = ?
                    WHERE boq_item_id = ?";
            
            $stmt_stock = $this->db->prepare($sql_stock);
            $result_stock = $stmt->execute([$stockQuantity, $boqItemId]);
            
        }
        
        return $result;
    }
    
    public function addIndividualStockEntry($data) {
        $currentUser = Auth::getCurrentUser();
        $userId = $currentUser ? $currentUser['id'] : null;
        
        $sql = "INSERT INTO inventory_stock 
                (boq_item_id, serial_number, batch_number, unit_cost, purchase_date, 
                 expiry_date, warranty_period, location_type, location_id, location_name,
                 item_status, quality_status, supplier_name, purchase_order_number, 
                 invoice_number, notes, created_by, updated_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['boq_item_id'],
            $data['serial_number'] ?? null,
            $data['batch_number'] ?? null,
            $data['unit_cost'],
            $data['purchase_date'] ?? null,
            $data['expiry_date'] ?? null,
            $data['warranty_period'] ?? null,
            $data['location_type'] ?? 'warehouse',
            $data['location_id'] ?? null,
            $data['location_name'] ?? 'Main Warehouse',
            'available', // Default status
            $data['quality_status'] ?? 'good',
            $data['supplier_name'] ?? null,
            $data['purchase_order_number'] ?? null,
            $data['invoice_number'] ?? null,
            $data['notes'] ?? null,
            $userId,
            $userId
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    public function addBulkStockEntries($boqItemId, $quantity, $data) {
        $stockIds = [];
        
        for ($i = 0; $i < $quantity; $i++) {
            $itemData = $data;
            $itemData['boq_item_id'] = $boqItemId;
            
            // Generate serial number if not provided
            if (empty($itemData['serial_number']) && !empty($data['serial_prefix'])) {
                $itemData['serial_number'] = $data['serial_prefix'] . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            }
            
            $stockId = $this->addIndividualStockEntry($itemData);
            if ($stockId) {
                $stockIds[] = $stockId;
            }
        }
        
        return $stockIds;
    }
    
    public function updateIndividualStockEntry($id, $data) {
        $currentUser = Auth::getCurrentUser();
        
        $fields = [];
        $values = [];
        
        $allowedFields = [
            'unit_cost', 'batch_number', 'serial_number', 'location_type', 'location_id', 'location_name',
            'purchase_date', 'expiry_date', 'supplier_name', 'quality_status', 'warranty_period', 
            'item_status', 'notes'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        // Always update the updated_by field
        $fields[] = "updated_by = ?";
        $values[] = $currentUser['id'];
        
        $sql = "UPDATE inventory_stock SET " . implode(', ', $fields) . " WHERE id = ?";
        $values[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    // ==================== INWARD RECEIPTS ====================
    
    public function createInwardReceipt($data) {
        $currentUser = Auth::getCurrentUser();
        
        $sql = "INSERT INTO inventory_inwards 
                (receipt_number, receipt_date, supplier_name, supplier_contact, 
                 purchase_order_number, invoice_number, invoice_date, total_amount, 
                 received_by, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['receipt_number'],
            $data['receipt_date'],
            $data['supplier_name'],
            $data['supplier_contact'],
            $data['purchase_order_number'],
            $data['invoice_number'],
            $data['invoice_date'],
            $data['total_amount'],
            $currentUser['id'],
            $data['remarks']
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    public function addInwardItems($inwardId, $items) {
        $currentUser = Auth::getCurrentUser();
        
        // Get inward receipt details for supplier info
        $inwardSql = "SELECT receipt_date, supplier_name FROM inventory_inwards WHERE id = ?";
        $inwardStmt = $this->db->prepare($inwardSql);
        $inwardStmt->execute([$inwardId]);
        $inwardData = $inwardStmt->fetch(PDO::FETCH_ASSOC);
        
        $sql = "INSERT INTO inventory_inward_items 
                (inward_id, boq_item_id, quantity_received, unit_cost, batch_number, 
                 serial_numbers, expiry_date, quality_status, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($items as $item) {
            $serialNumbers = !empty($item['serial_numbers']) ? json_encode($item['serial_numbers']) : null;
            
            // Add to inward_items table
            $stmt->execute([
                $inwardId,
                $item['boq_item_id'],
                $item['quantity_received'],
                $item['unit_cost'],
                $item['batch_number'],
                $serialNumbers,
                $item['expiry_date'],
                $item['quality_status'] ?? 'good',
                $item['remarks']
            ]);
            
            // Create individual stock entries
            $this->createIndividualStockEntries(
                $item['boq_item_id'],
                $item['quantity_received'],
                $item['unit_cost'],
                $item['batch_number'],
                $item['serial_numbers'] ?? [],
                $inwardData['supplier_name'] ?? null,
                $inwardData['receipt_date'] ?? null,
                $item['expiry_date'] ?? null,
                $item['quality_status'] ?? 'good',
                $item['remarks'] ?? null
            );
        }
        
        return true;
    }
    
    private function createIndividualStockEntries($boqItemId, $quantity, $unitCost, $batchNumber, $serialNumbers, $supplier, $purchaseDate, $expiryDate, $qualityStatus, $remarks) {
        $currentUser = Auth::getCurrentUser();
        
        if (!empty($serialNumbers) && is_array($serialNumbers)) {
            // Create individual entries for each serial number
            foreach ($serialNumbers as $serialNumber) {
                $this->createSingleStockEntry($boqItemId, 1, $unitCost, $batchNumber, $serialNumber, $supplier, $purchaseDate, $expiryDate, $qualityStatus, $remarks, $currentUser['id']);
            }
        } else {
            // Create a single entry for the batch quantity
            $this->createSingleStockEntry($boqItemId, $quantity, $unitCost, $batchNumber, null, $supplier, $purchaseDate, $expiryDate, $qualityStatus, $remarks, $currentUser['id']);
        }
    }
    
    private function createSingleStockEntry($boqItemId, $quantity, $unitCost, $batchNumber, $serialNumber, $supplier, $purchaseDate, $expiryDate, $qualityStatus, $remarks, $userId) {
        // In the new schema, each item is individual, so quantity should always be 1
        // This method creates individual entries for each item
        for ($i = 0; $i < $quantity; $i++) {
            $itemData = [
                'boq_item_id' => $boqItemId,
                'unit_cost' => $unitCost,
                'batch_number' => $batchNumber,
                'serial_number' => $serialNumber,
                'supplier_name' => $supplier,
                'purchase_date' => $purchaseDate,
                'expiry_date' => $expiryDate,
                'quality_status' => $qualityStatus,
                'notes' => $remarks
            ];
            
            $this->addIndividualStockEntry($itemData);
        }
    }
    
    public function getInwardReceipts($page = 1, $limit = 20, $search = '', $status = '') {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        if (!empty($search)) {
            $conditions[] = "(receipt_number LIKE ? OR supplier_name LIKE ? OR invoice_number LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($status)) {
            $conditions[] = "status = ?";
            $params[] = $status;
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM inventory_inwards $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results
        $sql = "SELECT ii.*, u.username as received_by_name,
                       (SELECT COUNT(*) FROM inventory_inward_items WHERE inward_id = ii.id) as item_count
                FROM inventory_inwards ii
                LEFT JOIN users u ON ii.received_by = u.id
                $whereClause
                ORDER BY ii.receipt_date DESC, ii.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'receipts' => $receipts,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    public function getInwardReceiptDetails($inwardId) {
        $sql = "SELECT ii.*, u.username as received_by_name, u2.username as verified_by_name
                FROM inventory_inwards ii
                LEFT JOIN users u ON ii.received_by = u.id
                LEFT JOIN users u2 ON ii.verified_by = u2.id
                WHERE ii.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$inwardId]);
        $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($receipt) {
            // Get receipt items
            $itemsSql = "SELECT iii.*, bi.item_name, bi.item_code, bi.unit, bi.category
                         FROM inventory_inward_items iii
                         JOIN boq_items bi ON CAST(iii.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                         WHERE iii.inward_id = ?
                         ORDER BY bi.item_name";
            
            $stmt = $this->db->prepare($itemsSql);
            $stmt->execute([$inwardId]);
            $receipt['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $receipt;
    }
    
    // ==================== DISPATCHES ====================
    
    public function createDispatch($data) {
        $currentUser = Auth::getCurrentUser();
        
        $sql = "INSERT INTO inventory_dispatches 
                (dispatch_number, dispatch_date, material_request_id, site_id, vendor_id,
                 contact_person_name, contact_person_phone, delivery_address, courier_name,
                 tracking_number, expected_delivery_date, dispatch_status, dispatched_by, delivery_remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['dispatch_number'],
            $data['dispatch_date'],
            $data['material_request_id'],
            $data['site_id'],
            $data['vendor_id'],
            $data['contact_person_name'],
            $data['contact_person_phone'],
            $data['delivery_address'],
            $data['courier_name'],
            $data['tracking_number'],
            $data['expected_delivery_date'],
            $data['dispatch_status'] ?? 'dispatched',
            $currentUser['id'],
            $data['delivery_remarks'] ?? null
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    public function addDispatchItems($dispatchId, $items) {
        $currentUser = Auth::getCurrentUser();
        $totalItems = 0;
        $totalValue = 0;
        
        // Start transaction for better performance
        $this->db->beginTransaction();
        
        try {
            foreach ($items as $item) {
                $boqItemId = $item['boq_item_id'];
                $requestedQuantity = intval($item['quantity_dispatched']);
                
                // Check if item requires serial numbers
                $sql = "SELECT need_serial_number FROM boq_items WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$boqItemId]);
                $boqItem = $stmt->fetch(PDO::FETCH_ASSOC);
                $needsSerial = $boqItem && $boqItem['need_serial_number'] == 1;
                
                if ($needsSerial) {
                    // For serialized items, use individual serial numbers
                    $individualRecords = !empty($item['individual_records']) ? json_decode($item['individual_records'], true) : [];
                    
                    if (empty($individualRecords)) {
                        throw new Exception("Serial numbers required for BOQ item ID $boqItemId");
                    }
                    
                    // Get stock items by serial numbers
                    $serialNumbers = array_column($individualRecords, 'serial_number');
                    $serialNumbers = array_filter($serialNumbers); // Remove empty values
                    
                    if (count($serialNumbers) < $requestedQuantity) {
                        throw new Exception("Not enough serial numbers provided for BOQ item ID $boqItemId");
                    }
                    
                    $stockItems = $this->getStockBySerialNumbers($serialNumbers);
                    
                    // Check if all specified serials are available
                    $foundSerials = array_column($stockItems, 'serial_number');
                    $missingSerials = array_diff($serialNumbers, $foundSerials);
                    
                    if (!empty($missingSerials)) {
                        throw new Exception("Serial numbers not available: " . implode(', ', $missingSerials));
                    }
                    
                    // Dispatch each serialized item
                    foreach ($stockItems as $stockItem) {
                        // Insert dispatch item record
                        $sql = "INSERT INTO inventory_dispatch_items 
                                (dispatch_id, inventory_stock_id, boq_item_id, unit_cost, 
                                 item_condition, batch_number, dispatch_notes, warranty_period)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        
                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([
                            $dispatchId,
                            $stockItem['id'],
                            $boqItemId,
                            $stockItem['unit_cost'],
                            $item['item_condition'] ?? 'new',
                            $item['batch_number'] ?? $stockItem['batch_number'], // Use form batch or stock batch
                            $item['remarks'] ?? null,
                            $item['warranty_period'] ?? null
                        ]);
                        
                        // Update stock item status to 'dispatched'
                        $this->updateStockItemStatus($stockItem['id'], 'dispatched', $dispatchId);
                        
                        $totalItems++;
                        $totalValue += $stockItem['unit_cost'];
                    }
                } else {
                    // For non-serialized items, use bulk update approach for better performance
                    // Get available stock IDs with limit
                    $sql = "SELECT id, unit_cost 
                            FROM inventory_stock 
                            WHERE boq_item_id = ? 
                            AND CAST(item_status AS CHAR) = CAST('available' AS CHAR)
                            AND CAST(quality_status AS CHAR) = CAST('good' AS CHAR)
                            AND CAST(activity_status AS CHAR) = CAST('active' AS CHAR)
                            ORDER BY id ASC
                            LIMIT ?";
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$boqItemId, $requestedQuantity]);
                    $stockItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($stockItems) < $requestedQuantity) {
                        $available = count($stockItems);
                        throw new Exception("Insufficient stock for BOQ item ID $boqItemId. Requested: $requestedQuantity, Available: $available");
                    }
                    
                    // Batch insert dispatch items
                    $stockIds = array_column($stockItems, 'id');
                    $avgUnitCost = array_sum(array_column($stockItems, 'unit_cost')) / count($stockItems);
                    
                    // Prepare batch insert
                    $values = [];
                    $placeholders = [];
                    foreach ($stockItems as $stockItem) {
                        $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?)";
                        $values[] = $dispatchId;
                        $values[] = $stockItem['id'];
                        $values[] = $boqItemId;
                        $values[] = $stockItem['unit_cost'];
                        $values[] = $item['item_condition'] ?? 'new';
                        $values[] = $item['batch_number'] ?? $stockItem['batch_number']; // Use form batch or stock batch
                        $values[] = $item['remarks'] ?? null;
                        $values[] = $item['warranty_period'] ?? null;
                        
                        $totalValue += $stockItem['unit_cost'];
                    }
                    
                    // Batch insert dispatch items
                    $sql = "INSERT INTO inventory_dispatch_items 
                            (dispatch_id, inventory_stock_id, boq_item_id, unit_cost, 
                             item_condition, batch_number, dispatch_notes, warranty_period)
                            VALUES " . implode(', ', $placeholders);
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($values);
                    
                    // Batch update stock status
                    $stockIdsList = implode(',', $stockIds);
                    $sql = "UPDATE inventory_stock 
                            SET item_status = 'dispatched', 
                                dispatch_id = ?,
                                dispatched_at = NOW(),
                                updated_by = ?
                            WHERE id IN ($stockIdsList)";
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$dispatchId, $currentUser['id']]);
                    
                    $totalItems += count($stockItems);
                }
            }
            
            // Commit transaction
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            // Rollback on error
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function updateStockItemStatus($stockId, $status, $dispatchId = null) {
        $currentUser = Auth::getCurrentUser();
        
        $sql = "UPDATE inventory_stock 
                SET item_status = ?, 
                    dispatch_id = ?,
                    dispatched_at = CASE WHEN CAST(? AS CHAR) = CAST('dispatched' AS CHAR) THEN NOW() ELSE dispatched_at END,
                    delivered_at = CASE WHEN CAST(? AS CHAR) = CAST('delivered' AS CHAR) THEN NOW() ELSE delivered_at END,
                    updated_by = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $status,
            $dispatchId,
            $status,
            $status,
            $currentUser['id'],
            $stockId
        ]);
    }
    
    private function updateDispatchTotals($dispatchId, $totalItems = null, $totalValue = null) {
        if ($totalItems === null || $totalValue === null) {
            // Calculate from database
            $sql = "UPDATE inventory_dispatches 
                    SET total_items = (SELECT COUNT(*) FROM inventory_dispatch_items WHERE dispatch_id = ?),
                        total_value = (SELECT SUM(unit_cost) FROM inventory_dispatch_items WHERE dispatch_id = ?)
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dispatchId, $dispatchId, $dispatchId]);
        } else {
            // Use provided values
            $sql = "UPDATE inventory_dispatches 
                    SET total_items = ?, total_value = ?
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$totalItems, $totalValue, $dispatchId]);
        }
    }
    
    public function getDispatches($page = 1, $limit = 20, $search = '', $status = '', $siteId = null) {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        if (!empty($search)) {
            $conditions[] = "(id.dispatch_number LIKE ? OR id.contact_person_name LIKE ? OR id.tracking_number LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($status)) {
            $conditions[] = "id.dispatch_status = ?";
            $params[] = $status;
        }
        
        if ($siteId) {
            $conditions[] = "id.site_id = ?";
            $params[] = $siteId;
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM inventory_dispatches id $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results with calculated totals
        $sql = "SELECT id.*, 
                       s.site_id as site_code, 
                       v.name as vendor_name, 
                       v.company_name as vendor_company_name,
                       u.username as dispatched_by_name,
                       (SELECT COUNT(*) FROM inventory_dispatch_items WHERE dispatch_id = id.id) as total_items,
                       (SELECT COALESCE(SUM(unit_cost), 0) FROM inventory_dispatch_items WHERE dispatch_id = id.id) as total_value
                FROM inventory_dispatches id
                LEFT JOIN sites s ON id.site_id = s.id
                LEFT JOIN vendors v ON id.vendor_id = v.id
                LEFT JOIN users u ON id.dispatched_by = u.id
                $whereClause
                ORDER BY id.dispatch_date DESC, id.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $dispatches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'dispatches' => $dispatches,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    // ==================== TRACKING ====================
    
    public function getMaterialTracking($search = '', $siteId = null, $vendorId = null, $status = '') {
        $whereClause = '';
        $params = [];
        $conditions = [];
        
        if (!empty($search)) {
            $conditions[] = "(bi.item_name LIKE ? OR bi.item_code LIKE ? OR it.serial_number LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($siteId) {
            $conditions[] = "it.site_id = ?";
            $params[] = $siteId;
        }
        
        if ($vendorId) {
            $conditions[] = "it.vendor_id = ?";
            $params[] = $vendorId;
        }
        
        if (!empty($status)) {
            $conditions[] = "it.status = ?";
            $params[] = $status;
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        $sql = "SELECT it.*, bi.item_name, bi.item_code, bi.unit,
                       s.site_id as site_code, v.name as vendor_name,
                       id.dispatch_number, id.dispatch_date
                FROM inventory_tracking it
                JOIN boq_items bi ON CAST(it.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                LEFT JOIN sites s ON it.site_id = s.id
                LEFT JOIN vendors v ON it.vendor_id = v.id
                LEFT JOIN inventory_dispatches id ON it.dispatch_id = id.id
                $whereClause
                ORDER BY it.last_movement_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateMaterialLocation($trackingId, $newLocation, $locationId, $remarks) {
        $currentUser = Auth::getCurrentUser();
        
        $sql = "UPDATE inventory_tracking 
                SET current_location_type = ?, current_location_id = ?, 
                    movement_remarks = ?, last_movement_date = NOW(), updated_by = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$newLocation, $locationId, $remarks, $currentUser['id'], $trackingId]);
    }
    
    // ==================== REPORTS & ANALYTICS ====================
    
    public function getInventoryStats() {
        $stats = [];
        
        // Use the new inventory_summary view for aggregated data
        $sql = "SELECT 
                    COUNT(DISTINCT boq_item_id) as total_items,
                    SUM(total_stock) as total_quantity,
                    SUM(total_value) as total_value,
                    SUM(available_stock) as available_quantity,
                    SUM(dispatched_stock) as dispatched_quantity,
                    SUM(delivered_stock) as delivered_quantity
                FROM inventory_summary";
        
        $stmt = $this->db->query($sql);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Total individual entries
        $sql = "SELECT COUNT(*) as total_entries
                FROM inventory_stock ist
                JOIN boq_items bi ON CAST(ist.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                WHERE CAST(bi.status AS CHAR) = CAST('active' AS CHAR)";
        
        $stmt = $this->db->query($sql);
        $stats['total_entries'] = $stmt->fetchColumn();
        
        // Recent additions (last 7 days)
        $sql = "SELECT COUNT(*) as recent_additions
                FROM inventory_stock 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        
        $stmt = $this->db->query($sql);
        $stats['recent_additions'] = $stmt->fetchColumn();
        
        // Pending dispatches
        $sql = "SELECT COUNT(*) as pending_dispatches
                FROM inventory_dispatches 
                WHERE dispatch_status IN ('prepared', 'dispatched', 'in_transit')";
        
        $stmt = $this->db->query($sql);
        $stats['pending_dispatches'] = $stmt->fetchColumn();
        
        // Status-wise breakdown
        $sql = "SELECT item_status, COUNT(*) as count
                FROM inventory_stock 
                GROUP BY item_status";
        
        $stmt = $this->db->query($sql);
        $statusStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stats['by_status'] = [];
        foreach ($statusStats as $status) {
            $stats['by_status'][$status['item_status']] = $status['count'];
        }
        
        // Location-wise breakdown
        $sql = "SELECT location_type, COUNT(*) as count
                FROM inventory_stock 
                GROUP BY location_type";
        
        $stmt = $this->db->query($sql);
        $locationStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stats['by_location'] = [];
        foreach ($locationStats as $location) {
            $stats['by_location'][$location['location_type']] = $location['count'];
        }
        
        return $stats;
    }
    
    public function generateReceiptNumber() {
        $prefix = 'RCP';
        $date = date('Ymd');
        
        $sql = "SELECT COUNT(*) + 1 as next_number 
                FROM inventory_inwards 
                WHERE receipt_number LIKE ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . $date . '%']);
        $nextNumber = $stmt->fetchColumn();
        
        return $prefix . $date . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    public function generateDispatchNumber() {
        $prefix = 'DSP';
        $date = date('Ymd');
        
        $sql = "SELECT COUNT(*) + 1 as next_number 
                FROM inventory_dispatches 
                WHERE dispatch_number LIKE ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . $date . '%']);
        $nextNumber = $stmt->fetchColumn();
        
        return $prefix . $date . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    public function createTrackingEntry($data) {
        $currentUser = Auth::getCurrentUser();
        $userId = $currentUser ? $currentUser['id'] : null;
        
        $sql = "INSERT INTO inventory_tracking 
                (boq_item_id, batch_number, serial_number, current_location_type, 
                 current_location_id, current_location_name, site_id, vendor_id, 
                 dispatch_id, inward_id, quantity, status, movement_remarks, updated_by, last_movement_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['boq_item_id'],
            $data['batch_number'] ?? null,
            $data['serial_number'] ?? null,
            $data['current_location_type'],
            $data['current_location_id'] ?? null,
            $data['current_location_name'] ?? null,
            $data['site_id'] ?? null,
            $data['vendor_id'] ?? null,
            $data['dispatch_id'] ?? null,
            $data['inward_id'] ?? null,
            $data['quantity'],
            $data['status'] ?? 'available',
            $data['movement_remarks'] ?? null,
            $userId
        ]);
    }
    
    public function getDispatchByRequestId($requestId) {
        $sql = "SELECT * FROM inventory_dispatches 
                WHERE material_request_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$requestId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function confirmDelivery($dispatchId, $deliveryData) {
        // Update with all delivery confirmation fields
        $sql = "UPDATE inventory_dispatches 
                SET dispatch_status = 'confirmed',
                    delivery_date = ?,
                    delivery_time = ?,
                    received_by = ?,
                    received_by_phone = ?,
                    actual_delivery_address = ?,
                    delivery_notes = ?,
                    lr_copy_path = ?,
                    additional_documents = ?,
                    item_confirmations = ?,
                    confirmed_by = ?,
                    confirmation_date = ?,
                    delivery_remarks = ?
                WHERE id = ?";
        
        // Prepare delivery remarks as backup/summary
        $deliveryRemarks = [];
        
        if (!empty($deliveryData['delivery_notes'])) {
            $deliveryRemarks[] = "Notes: " . $deliveryData['delivery_notes'];
        }
        
        if (!empty($deliveryData['received_by'])) {
            $deliveryRemarks[] = "Received by: " . $deliveryData['received_by'];
        }
        
        if (!empty($deliveryData['received_by_phone'])) {
            $deliveryRemarks[] = "Phone: " . $deliveryData['received_by_phone'];
        }
        
        if (!empty($deliveryData['delivery_date'])) {
            $deliveryRemarks[] = "Delivery Date: " . $deliveryData['delivery_date'];
        }
        
        if (!empty($deliveryData['delivery_time'])) {
            $deliveryRemarks[] = "Delivery Time: " . $deliveryData['delivery_time'];
        }
        
        if (!empty($deliveryData['item_confirmations'])) {
            $deliveryRemarks[] = "Item Confirmations: " . $deliveryData['item_confirmations'];
        }
        
        $remarksText = implode(" | ", $deliveryRemarks);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $deliveryData['delivery_date'] ?? null,
            $deliveryData['delivery_time'] ?? null,
            $deliveryData['received_by'] ?? null,
            $deliveryData['received_by_phone'] ?? null,
            $deliveryData['delivery_address'] ?? null,
            $deliveryData['delivery_notes'] ?? null,
            $deliveryData['lr_copy_path'] ?? null,
            $deliveryData['additional_documents'] ?? null,
            $deliveryData['item_confirmations'] ?? null,
            $deliveryData['confirmed_by'] ?? null,
            $deliveryData['confirmation_date'] ?? null,
            $remarksText,
            $dispatchId
        ]);
    }
    
    public function createDeliveryNotification($data) {
        $sql = "INSERT INTO delivery_notifications 
                (request_id, dispatch_id, vendor_id, message, type, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['request_id'],
            $data['dispatch_id'],
            $data['vendor_id'],
            $data['message'],
            $data['type'],
            $data['created_by']
        ]);
    }
    
    public function getReceivedMaterialsForVendor($vendorId) {
        $sql = "SELECT id.*, mr.id as material_request_id, s.site_id as site_code
                FROM inventory_dispatches id
                LEFT JOIN material_requests mr ON id.material_request_id = mr.id
                LEFT JOIN sites s ON id.site_id = s.id
                WHERE id.vendor_id = ? 
                AND id.dispatch_status IN ('dispatched', 'delivered', 'confirmed')
                ORDER BY id.delivery_date DESC, id.dispatch_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$vendorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getDispatchItems($dispatchId) {
        $sql = "SELECT idi.*, ist.serial_number, ist.batch_number, ist.item_status,
                       bi.item_name, bi.item_code, bi.unit, bi.icon_class
                FROM inventory_dispatch_items idi
                LEFT JOIN inventory_stock ist ON idi.inventory_stock_id = ist.id
                LEFT JOIN boq_items bi ON CAST(idi.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                WHERE idi.dispatch_id = ?
                ORDER BY bi.item_name, ist.serial_number";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dispatchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getDispatchItemsSummary($dispatchId) {
        $sql = "SELECT bi.id as boq_item_id, bi.item_name, bi.item_code, bi.unit, bi.icon_class,
                       COUNT(idi.id) as quantity_dispatched,
                       AVG(idi.unit_cost) as unit_cost,
                       SUM(idi.unit_cost) as total_cost,
                       GROUP_CONCAT(DISTINCT ist.serial_number ORDER BY ist.serial_number) as serial_numbers,
                       GROUP_CONCAT(DISTINCT COALESCE(idi.batch_number, ist.batch_number) ORDER BY COALESCE(idi.batch_number, ist.batch_number)) as batch_number,
                       MAX(idi.item_condition) as item_condition,
                       MAX(idi.warranty_period) as warranty_period,
                       MAX(idi.dispatch_notes) as remarks
                FROM inventory_dispatch_items idi
                LEFT JOIN inventory_stock ist ON idi.inventory_stock_id = ist.id
                LEFT JOIN boq_items bi ON CAST(idi.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                WHERE idi.dispatch_id = ?
                GROUP BY bi.id, bi.item_name, bi.item_code, bi.unit, bi.icon_class
                ORDER BY bi.item_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dispatchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function checkStockAvailabilityForItems($requestedItems) {
        $stockAvailability = [];
        
        foreach ($requestedItems as $item) {
            if (empty($item['boq_item_id'])) {
                continue;
            }
            
            $boqItemId = $item['boq_item_id'];
            $requestedQuantity = intval($item['quantity']);
            
            // Get available stock count efficiently using COUNT query instead of fetching all records
            $sql = "SELECT COUNT(*) as available_count
                    FROM inventory_stock ist
                    WHERE ist.boq_item_id = ? 
                    AND CAST(ist.item_status AS CHAR) = CAST('available' AS CHAR)
                    AND CAST(ist.quality_status AS CHAR) = CAST('good' AS CHAR)
                    AND CAST(ist.activity_status AS CHAR) = CAST('active' AS CHAR)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$boqItemId]);
            $availableQuantity = intval($stmt->fetchColumn());
            
            // Get item details
            $sql = "SELECT item_name, item_code, unit FROM boq_items WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$boqItemId]);
            $itemDetails = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stockAvailability[$boqItemId] = [
                'item_name' => $itemDetails['item_name'] ?? 'Unknown Item',
                'item_code' => $itemDetails['item_code'] ?? 'N/A',
                'unit' => $itemDetails['unit'] ?? 'Nos',
                'requested_quantity' => $requestedQuantity,
                'available_quantity' => $availableQuantity,
                'is_sufficient' => $availableQuantity >= $requestedQuantity,
                'shortage' => max(0, $requestedQuantity - $availableQuantity)
            ];
        }
        
        return $stockAvailability;
    }
    
    public function getStockSummaryForItem($boqItemId) {
        $sql = "SELECT 
                    COUNT(CASE WHEN CAST(item_status AS CHAR) = CAST('available' AS CHAR) THEN 1 END) as available_stock,
                    COUNT(CASE WHEN CAST(item_status AS CHAR) = CAST('dispatched' AS CHAR) THEN 1 END) as dispatched_stock,
                    COUNT(CASE WHEN CAST(item_status AS CHAR) = CAST('delivered' AS CHAR) THEN 1 END) as delivered_stock,
                    COUNT(*) as total_stock,
                    AVG(unit_cost) as avg_unit_cost
                FROM inventory_stock 
                WHERE boq_item_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$boqItemId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStockDetailsByItem($boqItemId) {
        $sql = "SELECT inv.*, 
                       inv.item_status as status
                FROM inventory_stock inv
                WHERE inv.boq_item_id = ?
                ORDER BY inv.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$boqItemId]);
        return $stmt->fetchAll();
    }

    public function getStockSummaryByItem($boqItemId) {
        // $sql = "SELECT 
        //             COUNT(*) as total_stock,
        //             SUM(CASE WHEN CAST(item_status AS CHAR) = CAST('available' AS CHAR) THEN 1 ELSE 0 END) as available_stock,
        //             SUM(CASE WHEN CAST(item_status AS CHAR) = CAST('dispatched' AS CHAR) THEN 1 ELSE 0 END) as dispatched_stock,
        //             AVG(unit_cost) as avg_unit_cost,
        //             SUM(unit_cost) as total_value
        //         FROM inventory_stock 
        //         WHERE boq_item_id = ?";
        
        
            $sql ="SELECT 
                            COUNT(*) as total_stock,
                            SUM( CASE 
            WHEN item_status = 'available' 
                 AND activity_status = 'active'
            THEN 1 
            ELSE 0 
          END) as available_stock,
                            SUM(CASE WHEN CAST(item_status AS CHAR) = CAST('dispatched' AS CHAR) THEN 1 ELSE 0 END) as dispatched_stock,
                            AVG(unit_cost) as avg_unit_cost,
                            SUM(unit_cost) as total_value
                        FROM inventory_stock 
                        WHERE boq_item_id = ?";
        
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$boqItemId]);
        return $stmt->fetch();
    }

    public function getStockHistoryByItem($boqItemId, $limit = 50) {
        $sql = "SELECT 
                    'inward' as transaction_type,
                    1 as quantity,
                    inv.created_at,
                    inv.purchase_order_number as reference_number,
                    u.username as user_name,
                    inv.notes
                FROM inventory_stock inv
                LEFT JOIN users u ON inv.created_by = u.id
                WHERE inv.boq_item_id = ?
                
                UNION ALL
                
                SELECT 
                    'dispatch' as transaction_type,
                    1 as quantity,
                    inv2.updated_at as created_at,
                    '' as reference_number,
                    u2.username as user_name,
                    'Item dispatched' as notes
                FROM inventory_stock inv2
                LEFT JOIN users u2 ON inv2.updated_by = u2.id
                WHERE inv2.boq_item_id = ? AND CAST(inv2.item_status AS CHAR) = CAST('dispatched' AS CHAR)
                
                ORDER BY created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$boqItemId, $boqItemId, $limit]);
        return $stmt->fetchAll();
    }

    public function getDispatchDetails($dispatchId) {
        $sql = "SELECT id.*, s.site_id as site_code, v.name as vendor_name, v.company_name as vendor_company_name,
                       u.username as dispatched_by_name
                FROM inventory_dispatches id
                LEFT JOIN sites s ON id.site_id = s.id
                LEFT JOIN vendors v ON id.vendor_id = v.id
                LEFT JOIN users u ON id.dispatched_by = u.id
                WHERE id.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dispatchId]);
        $dispatch = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dispatch) {
            // Get dispatch items
            $dispatch['items'] = $this->getDispatchItemsSummary($dispatchId);
            
            // Ensure items is always an array
            if (!$dispatch['items']) {
                $dispatch['items'] = [];
            }
            
            // Debug: Log the structure of items (remove this in production)
            if (!empty($dispatch['items'])) {
                error_log("Dispatch items structure: " . print_r(array_keys($dispatch['items'][0]), true));
            }
        }
        
        return $dispatch;
    }

    public function updateDispatchStatus($dispatchId, $updateData) {
        $fields = [];
        $values = [];
        
        foreach ($updateData as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        
        $values[] = $dispatchId;
        
        $sql = "UPDATE inventory_dispatches SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    public function getDispatchById($dispatchId) {
        $sql = "SELECT id.*, 
                       s.site_id as site_code, s.location,
                       v.name as vendor_name, v.company_name as vendor_company
                FROM inventory_dispatches id
                LEFT JOIN sites s ON id.site_id = s.id
                LEFT JOIN vendors v ON id.vendor_id = v.id
                WHERE id.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dispatchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get formatted delivery confirmation details
     * Parses the delivery_remarks and combines with structured fields
     */
    public function getDeliveryConfirmationDetails($dispatchId) {
        $dispatch = $this->getDispatchById($dispatchId);
        
        if (!$dispatch) {
            return null;
        }
        
        // Return structured confirmation data
        return [
            'dispatch_id' => $dispatch['id'],
            'dispatch_number' => $dispatch['dispatch_number'],
            'dispatch_status' => $dispatch['dispatch_status'],
            'delivery_date' => $dispatch['delivery_date'],
            'delivery_time' => $dispatch['delivery_time'],
            'received_by' => $dispatch['received_by'],
            'received_by_phone' => $dispatch['received_by_phone'],
            'actual_delivery_address' => $dispatch['actual_delivery_address'],
            'delivery_notes' => $dispatch['delivery_notes'],
            'lr_copy_path' => $dispatch['lr_copy_path'],
            'additional_documents' => !empty($dispatch['additional_documents']) ? json_decode($dispatch['additional_documents'], true) : [],
            'item_confirmations' => !empty($dispatch['item_confirmations']) ? json_decode($dispatch['item_confirmations'], true) : [],
            'confirmed_by' => $dispatch['confirmed_by'],
            'confirmation_date' => $dispatch['confirmation_date'],
            'delivery_remarks' => $dispatch['delivery_remarks']
        ];
    }
    
    /**
     * Check if dispatch has uploaded documents
     */
    public function hasUploadedDocuments($dispatchId) {
        $sql = "SELECT lr_copy_path, additional_documents FROM inventory_dispatches WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dispatchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return false;
        }
        
        return !empty($result['lr_copy_path']) || !empty($result['additional_documents']);
    }
    
    // Add a note to a dispatch
    public function addDispatchNote($dispatchId, $note, $userId) {
        try {
            $sql = "INSERT INTO dispatch_notes (dispatch_id, note, created_by, created_at) 
                    VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$dispatchId, $note, $userId]);
        } catch (Exception $e) {
            error_log("Error adding dispatch note: " . $e->getMessage());
            return false;
        }
    }
    
    // ==================== CONTRACTOR INVENTORY METHODS ====================
    
    public function getContractorReceivedMaterials($vendorId) {
        try {
            $sql = "SELECT 
                        id.id as dispatch_id,
                        id.dispatch_number,
                        id.dispatch_date,
                        COALESCE(bi.item_name, 'No Items') as item_name,
                        COALESCE(bi.item_code, 'N/A') as item_code,
                        COALESCE(COUNT(idi.id), 0) as sent_quantity,
                        COALESCE(COUNT(idi.id), 0) as received_quantity,
                        COALESCE(bi.unit, 'Nos') as unit,
                        GROUP_CONCAT(DISTINCT ist.serial_number ORDER BY ist.serial_number) as serial_numbers,
                        COALESCE(MAX(idi.item_condition), 'new') as item_condition,
                        id.delivery_date,
                        id.confirmation_date,
                        'good' as received_condition
                    FROM inventory_dispatches id
                    LEFT JOIN inventory_dispatch_items idi ON id.id = idi.dispatch_id
                    LEFT JOIN inventory_stock ist ON idi.inventory_stock_id = ist.id
                    LEFT JOIN boq_items bi ON CAST(idi.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                    WHERE id.vendor_id = ?
                    GROUP BY id.id, id.dispatch_number, id.dispatch_date, bi.item_name, bi.item_code, bi.unit, id.delivery_date, id.confirmation_date
                    ORDER BY id.dispatch_date DESC, id.dispatch_number DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$vendorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getContractorReceivedMaterials: " . $e->getMessage());
            return [];
        }
    }
    
    public function getContractorReceivedMaterialsPaginated($vendorId, $page = 1, $limit = 20, $search = '', $status = '') {
        $offset = ($page - 1) * $limit;
        
        try {
            $whereClause = '';
            $params = [$vendorId];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(id.dispatch_number LIKE ? OR COALESCE(bi.item_name, 'No Items') LIKE ?)";
                $searchTerm = "%$search%";
                $params = array_merge($params, [$searchTerm, $searchTerm]);
            }
            
            if (!empty($status)) {
                if ($status === 'confirmed') {
                    $conditions[] = "id.confirmation_date IS NOT NULL";
                } elseif ($status === 'delivered') {
                    $conditions[] = "id.delivery_date IS NOT NULL AND id.confirmation_date IS NULL";
                } elseif ($status === 'pending') {
                    $conditions[] = "id.delivery_date IS NULL AND id.confirmation_date IS NULL";
                }
            }
            
            if (!empty($conditions)) {
                $whereClause = "AND " . implode(" AND ", $conditions);
            }
            
            // Get total count
            $countSql = "SELECT COUNT(DISTINCT id.id) 
                         FROM inventory_dispatches id
                         LEFT JOIN inventory_dispatch_items idi ON id.id = idi.dispatch_id
                         LEFT JOIN boq_items bi ON CAST(idi.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                         WHERE id.vendor_id = ? $whereClause";
            
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            
            // Get paginated results
            $sql = "SELECT 
                        id.id as dispatch_id,
                        id.dispatch_number,
                        id.dispatch_date,
                        COALESCE(bi.item_name, 'No Items') as item_name,
                        COALESCE(bi.item_code, 'N/A') as item_code,
                        COALESCE(COUNT(idi.id), 0) as sent_quantity,
                        COALESCE(COUNT(idi.id), 0) as received_quantity,
                        COALESCE(bi.unit, 'Nos') as unit,
                        GROUP_CONCAT(DISTINCT ist.serial_number ORDER BY ist.serial_number) as serial_numbers,
                        COALESCE(MAX(idi.item_condition), 'new') as item_condition,
                        id.delivery_date,
                        id.confirmation_date,
                        'good' as received_condition
                    FROM inventory_dispatches id
                    LEFT JOIN inventory_dispatch_items idi ON id.id = idi.dispatch_id
                    LEFT JOIN inventory_stock ist ON idi.inventory_stock_id = ist.id
                    LEFT JOIN boq_items bi ON CAST(idi.boq_item_id AS CHAR) = CAST(bi.id AS CHAR)
                    WHERE id.vendor_id = ? $whereClause
                    GROUP BY id.id, id.dispatch_number, id.dispatch_date, bi.item_name, bi.item_code, bi.unit, id.delivery_date, id.confirmation_date
                    ORDER BY id.dispatch_date DESC, id.dispatch_number DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'materials' => $materials,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
        } catch (Exception $e) {
            error_log("Error in getContractorReceivedMaterialsPaginated: " . $e->getMessage());
            return [
                'materials' => [],
                'total' => 0,
                'page' => 1,
                'limit' => $limit,
                'pages' => 1
            ];
        }
    }
    
    public function getContractorDispatchCount($vendorId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM inventory_dispatches WHERE vendor_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$vendorId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getContractorDispatchCount: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getContractorTotalItems($vendorId) {
        try {
            $sql = "SELECT COUNT(idi.id) as total 
                    FROM inventory_dispatches id 
                    JOIN inventory_dispatch_items idi ON id.id = idi.dispatch_id 
                    WHERE id.vendor_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$vendorId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getContractorTotalItems: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getContractorPendingConfirmations($vendorId) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM inventory_dispatches id 
                    WHERE id.vendor_id = ? 
                    AND id.dispatch_status IN ('dispatched', 'delivered') 
                    AND id.confirmation_date IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$vendorId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getContractorPendingConfirmations: " . $e->getMessage());
            return 0;
        }
    }

}