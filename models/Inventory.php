<?php
require_once __DIR__ . '/../config/database.php';

class Inventory {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getStockOverview($search = '', $category = '', $lowStock = false, $warehouseId = '', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $params = [];
        $whereClause = "WHERE 1=1";

        if (!empty($search)) {
            $whereClause .= " AND (item_name LIKE ? OR item_code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($category)) {
            $whereClause .= " AND category = ?";
            $params[] = $category;
        }

        if ($lowStock) {
            $whereClause .= " AND available_stock < 100";
        }

        $view = !empty($warehouseId) ? "warehouse_stock_summary" : "inventory_summary";
        if (!empty($warehouseId)) {
            $whereClause .= " AND warehouse_id = ?";
            $params[] = $warehouseId;
        }

        try {
            // Count total for pagination
            $countSql = "SELECT COUNT(*) FROM $view $whereClause";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            // Fetch items
            // Important: LIMIT and OFFSET must be integers. With emulate_prepares = false, we must bind them correctly.
            $sql = "SELECT *, 'All Warehouses' as warehouse_name FROM $view $whereClause ORDER BY item_name LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            
            // Bind search/filter params
            $paramIndex = 1;
            foreach ($params as $p) {
                $stmt->bindValue($paramIndex++, $p);
            }
            
            // Bind pagination params
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'items' => $items,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
        } catch (PDOException $e) {
            error_log("Inventory::getStockOverview error: " . $e->getMessage());
            return [
                'items' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'pages' => 0
            ];
        }
    }

    public function getInventoryStats() {
        try {
            // Basic summary stats
            $sql = "SELECT 
                        COUNT(DISTINCT boq_item_id) as total_items,
                        SUM(total_stock) as total_quantity,
                        SUM(total_value) as total_value,
                        SUM(available_stock) as available_quantity,
                        SUM(dispatched_stock) as dispatched_quantity
                    FROM inventory_summary";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Total entries in registry
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM inventory_stock");
            $stmt->execute();
            $stats['total_entries'] = $stmt->fetchColumn();

            // Pending dispatches
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM material_requests WHERE status = 'Pending'");
            $stmt->execute();
            $stats['pending_dispatches'] = $stmt->fetchColumn();

            // Recent additions (last 7 days)
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM inventory_stock WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stmt->execute();
            $stats['recent_additions'] = $stmt->fetchColumn();

            return $stats;
        } catch (PDOException $e) {
            error_log("Inventory::getInventoryStats error: " . $e->getMessage());
            return [];
        }
    }

    public function getIndividualStockEntries($boqItemId = null, $search = '', $location = '', $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        $params = [];
        $conditions = ["1=1"];

        if ($boqItemId) {
            $conditions[] = "ist.boq_item_id = ?";
            $params[] = $boqItemId;
        }

        if (!empty($search)) {
            $conditions[] = "(bi.item_name LIKE ? OR bi.item_code LIKE ? OR ist.serial_number LIKE ? OR ist.batch_number LIKE ?)";
            $term = "%$search%";
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        if (!empty($location)) {
            $conditions[] = "ist.location_type = ?";
            $params[] = $location;
        }

        $whereClause = "WHERE " . implode(" AND ", $conditions);

        try {
            // Count
            $countSql = "SELECT COUNT(*) FROM inventory_stock ist JOIN boq_items bi ON ist.boq_item_id = bi.id $whereClause";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            // Fetch
            $sql = "SELECT ist.*, bi.item_name, bi.item_code, bi.unit, bi.category
                    FROM inventory_stock ist
                    JOIN boq_items bi ON ist.boq_item_id = bi.id
                    $whereClause
                    ORDER BY ist.created_at DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind filter params
            $paramIndex = 1;
            foreach ($params as $p) {
                $stmt->bindValue($paramIndex++, $p);
            }
            
            // Bind pagination params
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'entries' => $entries,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
        } catch (PDOException $e) {
            error_log("Inventory::getIndividualStockEntries error: " . $e->getMessage());
            return ['entries' => [], 'total' => 0, 'page' => $page, 'limit' => $limit, 'pages' => 0];
        }
    }

    public function getAvailableStock($boqItemId, $requiredQuantity = null) {
        $sql = "SELECT ist.*, bi.item_name, bi.item_code, bi.unit
                FROM inventory_stock ist
                JOIN boq_items bi ON ist.boq_item_id = bi.id
                WHERE ist.boq_item_id = ? 
                AND ist.item_status = 'available'
                AND ist.quality_status = 'good'
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

    public function getDispatches($page = 1, $limit = 20, $search = '', $status = '', $siteId = null, $requestId = null) {
        $offset = ($page - 1) * $limit;
        $params = [];
        $conditions = ["1=1"];

        if (!empty($search)) {
            $search = trim($search);
            $searchReqId = $search;
            $useExactId = false;

            if (stripos($search, 'REQ-') === 0) {
                $searchReqId = ltrim(substr($search, 4), '0');
                $useExactId = true;
            }
            
            if ($useExactId && is_numeric($searchReqId)) {
                $conditions[] = "(disp.dispatch_number LIKE :search_1 OR disp.tracking_number LIKE :search_2 OR s.site_id LIKE :search_3 OR v.company_name LIKE :search_4 OR disp.material_request_id = :req_id_exact)";
                $params[':search_1'] = "%$search%";
                $params[':search_2'] = "%$search%";
                $params[':search_3'] = "%$search%";
                $params[':search_4'] = "%$search%";
                $params[':req_id_exact'] = $searchReqId;
            } else {
                $conditions[] = "(disp.dispatch_number LIKE :search_1 OR disp.tracking_number LIKE :search_2 OR s.site_id LIKE :search_3 OR v.company_name LIKE :search_4 OR CAST(disp.material_request_id AS CHAR) LIKE :search_5)";
                $params[':search_1'] = "%$search%";
                $params[':search_2'] = "%$search%";
                $params[':search_3'] = "%$search%";
                $params[':search_4'] = "%$search%";
                $params[':search_5'] = "%$search%";
            }
        }

        if (!empty($status)) {
            $conditions[] = "disp.dispatch_status = :status";
            $params[':status'] = $status;
        }

        if (!empty($siteId)) {
            $conditions[] = "disp.site_id = :site_id";
            $params[':site_id'] = $siteId;
        }

        if (!empty($requestId)) {
            if (is_string($requestId) && stripos($requestId, 'REQ-') === 0) {
                $requestId = ltrim(substr($requestId, 4), '0');
            }
            $conditions[] = "disp.material_request_id = :request_id";
            $params[':request_id'] = $requestId;
        }

        $whereClause = "WHERE " . implode(" AND ", $conditions);

        try {
            // Get total count
            $countSql = "SELECT COUNT(*) FROM inventory_dispatches disp 
                        LEFT JOIN sites s ON disp.site_id = s.id 
                        LEFT JOIN vendors v ON disp.vendor_id = v.id 
                        $whereClause";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            // Get dispatches
            $sql = "SELECT disp.*, COALESCE(s.site_id, 'N/A') as site_code, COALESCE(s.location, 'Unknown Location') as site_name, 
                    v.company_name as vendor_company_name,
                    (SELECT COUNT(*) FROM inventory_dispatch_items WHERE dispatch_id = disp.id) as total_items,
                    (SELECT SUM(unit_cost) FROM inventory_dispatch_items WHERE dispatch_id = disp.id) as total_value
                    FROM inventory_dispatches disp
                    LEFT JOIN sites s ON disp.site_id = s.id
                    LEFT JOIN vendors v ON disp.vendor_id = v.id
                    $whereClause
                    ORDER BY disp.dispatch_date DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $dispatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'dispatches' => $dispatches,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
        } catch (PDOException $e) {
            error_log("Inventory::getDispatches error: " . $e->getMessage());
            return ['dispatches' => [], 'total' => 0, 'page' => $page, 'limit' => $limit, 'pages' => 0];
        }
    }

    public function getDispatchDetails($dispatchId) {
        try {
            $sql = "SELECT disp.*, COALESCE(s.site_id, 'N/A') as site_code, COALESCE(s.location, 'Unknown Location') as site_name, 
                    v.company_name as vendor_company_name, v.name as vendor_name, u.username as dispatched_by_name
                    FROM inventory_dispatches disp
                    LEFT JOIN sites s ON disp.site_id = s.id
                    LEFT JOIN vendors v ON disp.vendor_id = v.id
                    LEFT JOIN users u ON disp.dispatched_by = u.id
                    WHERE disp.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dispatchId]);
            $dispatch = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dispatch) {
                $requestId = $dispatch['material_request_id'] ?? null;
                
                // 1. Fetch items ONLY for the specific dispatch ID
                $sqlItems = "SELECT idi.boq_item_id, idi.item_condition, idi.batch_number, idi.unit_cost,
                             COUNT(*) as quantity_dispatched,
                             SUM(idi.unit_cost) as total_cost,
                             GROUP_CONCAT(ist.serial_number SEPARATOR ', ') as serial_numbers,
                             bi.item_name, bi.item_code, bi.unit, idi.dispatch_notes as remarks
                             FROM inventory_dispatch_items idi
                             JOIN boq_items bi ON idi.boq_item_id = bi.id
                             LEFT JOIN inventory_stock ist ON idi.inventory_stock_id = ist.id
                             WHERE idi.dispatch_id = ?
                             GROUP BY idi.boq_item_id, idi.item_condition, idi.batch_number, idi.unit_cost";
                
                $stmtItems = $this->db->prepare($sqlItems);
                $stmtItems->execute([$dispatchId]);
                $dispatch['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                // 2. If the manifest is empty, fallback to the parent Material Request's manifest
                if (count($dispatch['items']) === 0 && $requestId) {
                    $sqlMr = "SELECT items FROM material_requests WHERE id = ?";
                    $stmtMr = $this->db->prepare($sqlMr);
                    $stmtMr->execute([$requestId]);
                    $mrItemsJson = $stmtMr->fetchColumn();
                    
                    if ($mrItemsJson) {
                        $mrItems = json_decode($mrItemsJson, true);
                        if (is_array($mrItems)) {
                            $reqTotalCount = 0;
                            foreach ($mrItems as $mItem) {
                                // Map Material Request item format to Manifest format
                                $dispatch['items'][] = [
                                    'item_name' => $mItem['material_name'] ?? 'Unknown Material',
                                    'item_code' => $mItem['item_code'] ?? 'N/A',
                                    'item_condition' => 'standard', 
                                    'quantity_dispatched' => $mItem['quantity'] ?? 0,
                                    'unit' => $mItem['unit'] ?? 'units',
                                    'unit_cost' => 0, 
                                    'total_cost' => 0,
                                    'remarks' => $mItem['notes'] ?? $mItem['reason'] ?? '--',
                                    'is_request_fallback' => true
                                ];
                                $reqTotalCount += (int)($mItem['quantity'] ?? 0);
                            }
                            
                            // Update header totals for request manifest fallback
                            if ($dispatch['total_items'] == 0 || $dispatch['total_items'] == "0") {
                                $dispatch['total_items'] = $reqTotalCount;
                                $dispatch['is_request_manifest'] = true;
                            }
                        }
                    }
                }
            }

            return $dispatch;
        } catch (PDOException $e) {
            error_log("Inventory::getDispatchDetails error: " . $e->getMessage());
            return null;
        }
    }

    public function getDispatchById($dispatchId) {
        try {
            $sql = "SELECT * FROM inventory_dispatches WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dispatchId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Inventory::getDispatchById error: " . $e->getMessage());
            return null;
        }
    }

    public function updateDispatchStatus($dispatchId, $data) {
        try {
            $fields = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
            
            if (empty($fields)) return false;
            
            $sql = "UPDATE inventory_dispatches SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = ?";
            $params[] = $dispatchId;
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Inventory::updateDispatchStatus error: " . $e->getMessage());
            return false;
        }
    }

    public function generateDispatchNumber() {
        try {
            $year = date('Y');
            $sql = "SELECT dispatch_number FROM inventory_dispatches 
                    WHERE dispatch_number LIKE 'DS-$year-%' 
                    ORDER BY id DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $last = $stmt->fetchColumn();

            if (!$last) {
                return "DS-$year-0001";
            }

            $parts = explode('-', $last);
            $num = (int)end($parts);
            return "DS-$year-" . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            return "DS-" . date('YmdHis');
        }
    }

    /**
     * Generate a custom formatted DC number: [SiteCode]-[FY]-[DC-Sequence]-[Random]
     * Example: KTEX-26-27-DC097-9591
     */
    public function generateCustomDispatchNumber($siteId) {
        try {
            // 1. Get Site Code
            $stmt = $this->db->prepare("SELECT site_id FROM sites WHERE id = ?");
            $stmt->execute([$siteId]);
            $siteCode = $stmt->fetchColumn() ?: 'KTV';

            // 2. Financial Year (April to March)
            $month = (int)date('n');
            $year = (int)date('y');
            if ($month >= 4) {
                $fy = $year . '-' . ($year + 1);
            } else {
                $fy = ($year - 1) . '-' . $year;
            }

            // 3. DC Sequence for this site
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM inventory_dispatches WHERE site_id = ?");
            $stmt->execute([$siteId]);
            $count = (int)$stmt->fetchColumn() + 1;
            $sequence = "DC" . str_pad($count, 3, '0', STR_PAD_LEFT);

            // 4. Random number
            $random = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

            return "{$siteCode}-{$fy}-{$sequence}-{$random}";
        } catch (Exception $e) {
            return "DC-" . date('Ymd') . "-" . rand(1000, 9999);
        }
    }

    public function getInwardReceipts($page = 1, $limit = 20, $search = '', $status = '') {
        $offset = ($page - 1) * $limit;
        $params = [];
        $conditions = ["1=1"];

        if (!empty($search)) {
            $conditions[] = "(ii.receipt_number LIKE ? OR ii.invoice_number LIKE ? OR ii.supplier_name LIKE ?)";
            $term = "%$search%";
            $params = array_merge($params, [$term, $term, $term]);
        }

        if (!empty($status)) {
            $conditions[] = "ii.status = ?";
            $params[] = $status;
        }

        $whereClause = "WHERE " . implode(" AND ", $conditions);

        try {
            // Count
            $countSql = "SELECT COUNT(*) FROM inventory_inwards ii $whereClause";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            // Fetch
            $sql = "SELECT ii.*, u.username as received_by_name,
                    (SELECT COUNT(*) FROM inventory_inward_items WHERE inward_id = ii.id) as item_count
                    FROM inventory_inwards ii
                    LEFT JOIN users u ON ii.received_by = u.id
                    $whereClause
                    ORDER BY ii.receipt_date DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $idx => $val) {
                $stmt->bindValue($idx + 1, $val);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'receipts' => $receipts,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
        } catch (PDOException $e) {
            error_log("Inventory::getInwardReceipts error: " . $e->getMessage());
            return ['receipts' => [], 'total' => 0, 'page' => $page, 'limit' => $limit, 'pages' => 0];
        }
    }

    public function generateReceiptNumber() {
        try {
            $year = date('Y');
            $sql = "SELECT receipt_number FROM inventory_inwards 
                    WHERE receipt_number LIKE 'REC-$year-%' 
                    ORDER BY id DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $last = $stmt->fetchColumn();

            if (!$last) {
                return "REC-$year-0001";
            }

            $parts = explode('-', $last);
            $num = (int)end($parts);
            return "REC-$year-" . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            return "REC-" . date('YmdHis');
        }
    }

    /**
     * Check stock availability for multiple items
     * @param array $requestedItems Array of items with boq_item_id and quantity
     * @return array Stock availability information for each item
     */
    public function checkStockAvailabilityForItems($requestedItems) {
        $availability = [];
        
        try {
            foreach ($requestedItems as $item) {
                // Normalize IDs (some use material_id, some use boq_item_id)
                $boqItemId = $item['boq_item_id'] ?? $item['material_id'] ?? null;
                $requestedQty = floatval($item['quantity'] ?? 0);
                $itemName = $item['item_name'] ?? $item['material_name'] ?? 'Unknown Item';
                
                if (empty($boqItemId)) {
                    $key = 'unmapped_' . md5($itemName);
                    $availability[$key] = [
                        'boq_item_id' => null,
                        'item_name' => $itemName,
                        'requested_qty' => $requestedQty,
                        'available_qty' => 0,
                        'is_sufficient' => false,
                        'not_found' => true,
                        'shortage' => $requestedQty
                    ];
                    continue;
                }
                
                // Get available stock for this item
                $sql = "SELECT 
                            COALESCE(SUM(CASE WHEN item_status = 'available' AND quality_status = 'good' THEN 1 ELSE 0 END), 0) as available_qty,
                            bi.item_name as db_item_name
                        FROM inventory_stock ist
                        RIGHT JOIN boq_items bi ON ist.boq_item_id = bi.id
                        WHERE bi.id = ?
                        GROUP BY bi.id";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$boqItemId]);
                $stock = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$stock) {
                    // Item exists as ID but maybe not in boq_items table?
                    $availability[$boqItemId] = [
                        'boq_item_id' => $boqItemId,
                        'item_name' => $itemName,
                        'requested_qty' => $requestedQty,
                        'available_qty' => 0,
                        'is_sufficient' => false,
                        'not_found' => true,
                        'shortage' => $requestedQty
                    ];
                    continue;
                }

                $availableQty = floatval($stock['available_qty'] ?? 0);
                
                $availability[$boqItemId] = [
                    'boq_item_id' => $boqItemId,
                    'item_name' => $stock['db_item_name'] ?? $itemName,
                    'requested_qty' => $requestedQty,
                    'available_qty' => $availableQty,
                    'is_sufficient' => $availableQty >= $requestedQty,
                    'not_found' => false,
                    'shortage' => max(0, $requestedQty - $availableQty)
                ];
            }
            
            return $availability;
        } catch (PDOException $e) {
            error_log("Inventory::checkStockAvailabilityForItems error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new dispatch record
     */
    public function createDispatch($data) {
        try {
            $sql = "INSERT INTO inventory_dispatches (
                        dispatch_number, dispatch_date, material_request_id,
                        site_id, vendor_id, contact_person_name,
                        contact_person_phone, delivery_address, courier_name,
                        tracking_number, expected_delivery_date, dispatch_status,
                        dispatched_by, delivery_remarks
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
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
                $data['dispatch_status'],
                $data['dispatched_by'],
                $data['delivery_remarks']
            ]);
            
            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Inventory::createDispatch error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add multiple items to a dispatch and update stock levels
     */
    /**
     * Add multiple items to a dispatch and update stock levels
     * Maps each dispatched unit to a specific row in inventory_stock
     */
    public function addDispatchItems($dispatchId, $items) {
        try {
            $this->db->beginTransaction();
            
            $totalValue = 0;
            $totalItemsDispatched = 0;
            
            // Prepare insert for dispatch items
            $insertSql = "INSERT INTO inventory_dispatch_items (
                        dispatch_id, inventory_stock_id, boq_item_id, 
                        unit_cost, item_condition, batch_number, dispatch_notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $this->db->prepare($insertSql);
            
            // Prepare update for stock status
            $updateStockSql = "UPDATE inventory_stock SET item_status = 'dispatched', dispatch_id = ?, dispatched_at = NOW() WHERE id = ?";
            $updateStmt = $this->db->prepare($updateStockSql);
            
            foreach ($items as $item) {
                $boqItemId = $item['boq_item_id'];
                if (empty($boqItemId)) continue;
                
                $qty = (int)$item['quantity_dispatched'];
                $unitCost = floatval($item['unit_cost']);
                $batchNumber = $item['batch_number'] ?? null;
                $dispatchNotes = $item['remarks'] ?? null;
                $itemCondition = $item['item_condition'] ?? 'new';
                $serialNumbers = !empty($item['individual_records']) ? json_decode($item['individual_records'], true) : [];

                // 1. Identify specific IDs to dispatch
                $stockIdsToDispatch = [];
                
                if (!empty($serialNumbers)) {
                    // Dispatch specific serial numbers
                    foreach ($serialNumbers as $record) {
                        $sn = $record['serial_number'] ?? null;
                        if (!$sn) continue;
                        
                        $checkSql = "SELECT id FROM inventory_stock 
                                    WHERE boq_item_id = ? AND serial_number = ? AND item_status = 'available' 
                                    LIMIT 1";
                        $checkStmt = $this->db->prepare($checkSql);
                        $checkStmt->execute([$boqItemId, $sn]);
                        $stockId = $checkStmt->fetchColumn();
                        
                        if ($stockId) {
                            $stockIdsToDispatch[] = $stockId;
                        } else {
                            throw new Exception("Serial number '$sn' not available for dispatch for BOQ item $boqItemId");
                        }
                    }
                } else {
                    // FIFO Selection for bulk items
                    $pickSql = "SELECT id FROM inventory_stock 
                                WHERE boq_item_id = ? AND item_status = 'available' AND quality_status = 'good' 
                                ORDER BY id ASC LIMIT ?";
                    $pickStmt = $this->db->prepare($pickSql);
                    $pickStmt->execute([$boqItemId, $qty]);
                    $stockIdsToDispatch = $pickStmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (count($stockIdsToDispatch) < $qty) {
                        throw new Exception("Insufficient available stock for BOQ item $boqItemId. Requested: $qty, Found: " . count($stockIdsToDispatch));
                    }
                }

                // 2. Process each unit individually (as per schema design)
                foreach ($stockIdsToDispatch as $stockId) {
                    // Insert into dispatch items mapping
                    $insertStmt->execute([
                        $dispatchId,
                        $stockId,
                        $boqItemId,
                        $unitCost,
                        $itemCondition,
                        $batchNumber,
                        $dispatchNotes
                    ]);
                    
                    // Update stock status
                    $updateStmt->execute([$dispatchId, $stockId]);
                    
                    $totalValue += $unitCost;
                    $totalItemsDispatched++;
                }
            }
            
            // 3. Update dispatch totals
            $updateDispatch = "UPDATE inventory_dispatches 
                               SET total_items = ?, total_value = ? 
                               WHERE id = ?";
            $dispatchStmt = $this->db->prepare($updateDispatch);
            $dispatchStmt->execute([$totalItemsDispatched, $totalValue, $dispatchId]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Inventory::addDispatchItems error: " . $e->getMessage());
            throw $e; // Re-throw to caller for handling
        }
    }

    /**
     * Create a tracking entry for material movement
     */
    public function createTrackingEntry($data) {
        try {
            $sql = "INSERT INTO inventory_tracking (
                        boq_item_id, batch_number, serial_number,
                        current_location_type, current_location_name,
                        site_id, vendor_id, dispatch_id,
                        quantity, status, movement_remarks
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['boq_item_id'],
                $data['batch_number'] ?? null,
                $data['serial_number'] ?? null,
                $data['current_location_type'],
                $data['current_location_name'],
                $data['site_id'] ?? null,
                $data['vendor_id'] ?? null,
                $data['dispatch_id'] ?? null,
                $data['quantity'],
                $data['status'],
                $data['movement_remarks'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Inventory::createTrackingEntry error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get stock summary for a specific BOQ item
     */
    public function getStockSummaryByItem($itemId) {
        try {
            $sql = "SELECT * FROM inventory_summary WHERE boq_item_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$itemId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'total_stock' => 0,
                'available_stock' => 0,
                'dispatched_stock' => 0,
                'total_value' => 0
            ];
        } catch (PDOException $e) {
            error_log("Inventory::getStockSummaryByItem error: " . $e->getMessage());
            return [
                'total_stock' => 0,
                'available_stock' => 0,
                'dispatched_stock' => 0,
                'total_value' => 0
            ];
        }
    }

    /**
     * Get individual stock details for a specific BOQ item
     */
    public function getStockDetailsByItem($itemId) {
        try {
            $sql = "SELECT *, item_status as status FROM inventory_stock 
                    WHERE boq_item_id = ? 
                    ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$itemId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Inventory::getStockDetailsByItem error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update an individual stock entry
     */
    public function updateIndividualStockEntry($id, $data) {
        try {
            $fields = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
            
            if (empty($fields)) return false;
            
            $sql = "UPDATE inventory_stock SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Inventory::updateIndividualStockEntry error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get delivery confirmation details for a dispatch
     */
    public function getDeliveryConfirmationDetails($dispatchId) {
        try {
            $sql = "SELECT delivery_date, delivery_time, received_by, received_by_phone, 
                           actual_delivery_address, delivery_notes, lr_copy_path, 
                           additional_documents, item_confirmations, confirmed_by, confirmation_date
                    FROM inventory_dispatches
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dispatchId]);
            $details = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($details) {
                // Parse JSON fields
                if (!empty($details['additional_documents'])) {
                    $details['additional_documents'] = json_decode($details['additional_documents'], true) ?: [];
                } else {
                    $details['additional_documents'] = [];
                }
                
                if (!empty($details['item_confirmations'])) {
                    $details['item_confirmations'] = json_decode($details['item_confirmations'], true) ?: [];
                } else {
                    $details['item_confirmations'] = [];
                }
            }
            
            return $details;
        } catch (PDOException $e) {
            error_log("Inventory::getDeliveryConfirmationDetails error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a dispatch has any uploaded documents
     */
    public function hasUploadedDocuments($dispatchId) {
        try {
            $sql = "SELECT lr_copy_path, additional_documents FROM inventory_dispatches WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dispatchId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) return false;
            
            if (!empty($row['lr_copy_path'])) return true;
            
            if (!empty($row['additional_documents'])) {
                $docs = json_decode($row['additional_documents'], true);
                return !empty($docs);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Inventory::hasUploadedDocuments error: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Get paginated received materials (dispatches) for a contractor
     */
    public function getContractorReceivedMaterialsPaginated($vendorId, $page = 1, $limit = 20, $search = '', $status = '', $dateFrom = '', $dateTo = '') {
        $offset = ($page - 1) * $limit;
        $params = [':vendor_id' => $vendorId];
        $whereClause = "WHERE vendor_id = :vendor_id";

        if (!empty($search)) {
            $whereClause .= " AND (dispatch_number LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if (!empty($status)) {
            $whereClause .= " AND dispatch_status = :status";
            $params[':status'] = $status;
        }

        if (!empty($dateFrom)) {
            $whereClause .= " AND dispatch_date >= :date_from";
            $params[':date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $whereClause .= " AND dispatch_date <= :date_to";
            $params[':date_to'] = $dateTo;
        }

        try {
            // Count total
            $countSql = "SELECT COUNT(*) FROM inventory_dispatches $whereClause";
            $stmt = $this->db->prepare($countSql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->execute();
            $total = (int)$stmt->fetchColumn();

            // Fetch materials
            $sql = "SELECT d.*, s.site_id as site_code, s.location as site_location 
                    FROM inventory_dispatches d
                    LEFT JOIN sites s ON d.site_id = s.id
                    $whereClause 
                    ORDER BY d.dispatch_date DESC, d.id DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind value params
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'materials' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total' => $total,
                'pages' => ceil($total / $limit)
            ];
        } catch (PDOException $e) {
            error_log("Inventory::getContractorReceivedMaterialsPaginated error: " . $e->getMessage());
            return ['materials' => [], 'total' => 0, 'pages' => 0];
        }
    }

    /**
     * Get total dispatch count for a contractor
     */
    public function getContractorDispatchCount($vendorId) {
        try {
            $sql = "SELECT COUNT(*) FROM inventory_dispatches WHERE vendor_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$vendorId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Inventory::getContractorDispatchCount error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get total distinct items received by a contractor
     */
    public function getContractorTotalItems($vendorId) {
        try {
            $sql = "SELECT COUNT(DISTINCT idi.boq_item_id) 
                    FROM inventory_dispatch_items idi
                    JOIN inventory_dispatches id ON idi.dispatch_id = id.id
                    WHERE id.vendor_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$vendorId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Inventory::getContractorTotalItems error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get pending confirmations count for a contractor
     */
    public function getContractorPendingConfirmations($vendorId) {
        try {
            $sql = "SELECT COUNT(*) FROM inventory_dispatches 
                    WHERE vendor_id = ? AND dispatch_status != 'confirmed'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$vendorId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Inventory::getContractorPendingConfirmations error: " . $e->getMessage());
            return 0;
        }
    }
    /**
     * Get all received materials (dispatches) for a specific vendor
     */
    public function getReceivedMaterialsForVendor($vendorId, $page = 1, $limit = 10, $search = '', $statusFilter = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause
            $whereConditions = ["id.vendor_id = :vendor_id"];
            $params = [':vendor_id' => $vendorId];
            
            // Add search filter
            if (!empty($search)) {
                $whereConditions[] = "(s.site_id LIKE :search OR id.dispatch_number LIKE :search OR id.courier_name LIKE :search OR id.tracking_number LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }
            
            // Add status filter
            if (!empty($statusFilter)) {
                $whereConditions[] = "id.dispatch_status = :status";
                $params[':status'] = $statusFilter;
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                        FROM inventory_dispatches id
                        LEFT JOIN sites s ON id.site_id = s.id
                        WHERE $whereClause";
            $countStmt = $this->db->prepare($countSql);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get paginated data
            $sql = "SELECT id.*, s.site_id as site_code, s.location as site_location,
                    (SELECT COUNT(DISTINCT boq_item_id) FROM inventory_dispatch_items WHERE dispatch_id = id.id) as actual_item_count
                    FROM inventory_dispatches id
                    LEFT JOIN sites s ON id.site_id = s.id
                    WHERE $whereClause
                    ORDER BY id.dispatch_date DESC, id.id DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total' => $totalRecords,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => ceil($totalRecords / $limit)
            ];
        } catch (PDOException $e) {
            error_log("Inventory::getReceivedMaterialsForVendor error: " . $e->getMessage());
            return [
                'data' => [],
                'total' => 0,
                'page' => 1,
                'limit' => $limit,
                'totalPages' => 0
            ];
        }
    }

    /**
     * Get items summary for a specific dispatch
     */
    public function getDispatchItemsSummary($dispatchId) {
        try {
            $sql = "SELECT idi.boq_item_id, bi.item_name, bi.item_code, bi.unit, bi.category,
                           COUNT(*) as quantity_dispatched,
                           idi.item_condition, idi.batch_number, idi.dispatch_notes, idi.warranty_period
                    FROM inventory_dispatch_items idi
                    JOIN boq_items bi ON idi.boq_item_id = bi.id
                    WHERE idi.dispatch_id = :dispatch_id
                    GROUP BY idi.boq_item_id, idi.item_condition, idi.batch_number";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':dispatch_id', $dispatchId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Inventory::getDispatchItemsSummary error: " . $e->getMessage());
            return [];
        }
    }
}
