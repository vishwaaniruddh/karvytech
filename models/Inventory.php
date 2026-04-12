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

    public function getDispatches($page = 1, $limit = 20, $search = '', $status = '', $siteId = null) {
        $offset = ($page - 1) * $limit;
        $params = [];
        $conditions = ["1=1"];

        if (!empty($search)) {
            $conditions[] = "(id.dispatch_number LIKE ? OR id.tracking_number LIKE ? OR s.site_id LIKE ? OR v.company_name LIKE ?)";
            $term = "%$search%";
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        if (!empty($status)) {
            $conditions[] = "id.dispatch_status = ?";
            $params[] = $status;
        }

        if ($siteId) {
            $conditions[] = "id.site_id = ?";
            $params[] = $siteId;
        }

        $whereClause = "WHERE " . implode(" AND ", $conditions);

        try {
            // Get total count
            $countSql = "SELECT COUNT(*) FROM inventory_dispatches id 
                        LEFT JOIN sites s ON id.site_id = s.id 
                        LEFT JOIN vendors v ON id.vendor_id = v.id 
                        $whereClause";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            // Get dispatches with aggregated item info
            $sql = "SELECT id.*, COALESCE(s.site_id, 'N/A') as site_code, COALESCE(s.location, 'Unknown Location') as site_name, 
                    v.company_name as vendor_company_name,
                    (SELECT COUNT(*) FROM inventory_dispatch_items WHERE dispatch_id = id.id) as total_items,
                    (SELECT SUM(unit_cost) FROM inventory_dispatch_items WHERE dispatch_id = id.id) as total_value
                    FROM inventory_dispatches id
                    LEFT JOIN sites s ON id.site_id = s.id
                    LEFT JOIN vendors v ON id.vendor_id = v.id
                    $whereClause
                    ORDER BY id.dispatch_date DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $idx => $val) {
                $stmt->bindValue($idx + 1, $val);
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
            $sql = "SELECT id.*, COALESCE(s.site_id, 'N/A') as site_code, COALESCE(s.location, 'Unknown Location') as site_name, 
                    v.company_name as vendor_company_name
                    FROM inventory_dispatches id
                    LEFT JOIN sites s ON id.site_id = s.id
                    LEFT JOIN vendors v ON id.vendor_id = v.id
                    WHERE id.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dispatchId]);
            $dispatch = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dispatch) {
                $sqlItems = "SELECT idi.*, bi.item_name, bi.item_code, bi.unit
                             FROM inventory_dispatch_items idi
                             JOIN boq_items bi ON idi.boq_item_id = bi.id
                             WHERE idi.dispatch_id = ?";
                $stmtItems = $this->db->prepare($sqlItems);
                $stmtItems->execute([$dispatchId]);
                $dispatch['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
            }

            return $dispatch;
        } catch (PDOException $e) {
            error_log("Inventory::getDispatchDetails error: " . $e->getMessage());
            return null;
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
}