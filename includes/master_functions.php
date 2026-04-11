<?php
/**
 * Master Data Utility Functions
 * Common functions for master data operations
 */

class MasterFunctions {
    
    /**
     * Get all active records for a master type
     */
    public static function getActiveRecords($masterType) {
        $model = self::getModelByType($masterType);
        if (!$model) {
            return [];
        }
        
        return $model->getActive();
    }
    
    /**
     * Get model instance by master type
     */
    public static function getModelByType($type) {
        switch ($type) {
            case 'zones':
                require_once __DIR__ . '/../models/Zone.php';
                return new Zone();
            case 'countries':
                require_once __DIR__ . '/../models/Country.php';
                return new Country();
            case 'states':
                require_once __DIR__ . '/../models/State.php';
                return new State();
            case 'cities':
                require_once __DIR__ . '/../models/City.php';
                return new City();
            case 'banks':
                require_once __DIR__ . '/../models/Bank.php';
                return new Bank();
            case 'customers':
                require_once __DIR__ . '/../models/Customer.php';
                return new Customer();
            case 'boq':
                require_once __DIR__ . '/../models/BoqMaster.php';
                return new BoqMaster();
            default:
                return null;
        }
    }
    
    /**
     * Get dropdown options for a master type
     */
    public static function getDropdownOptions($masterType, $selectedValue = null) {
        $records = self::getActiveRecords($masterType);
        $options = '<option value="">Select ' . ucfirst(rtrim($masterType, 's')) . '</option>';
        
        foreach ($records as $record) {
            $selected = ($selectedValue && $selectedValue == $record['id']) ? 'selected' : '';
            $options .= '<option value="' . $record['id'] . '" ' . $selected . '>' . htmlspecialchars($record['name']) . '</option>';
        }
        
        return $options;
    }
    
    /**
     * Get states by country for dropdown
     */
    public static function getStatesByCountry($countryId, $selectedValue = null) {
        require_once __DIR__ . '/../models/State.php';
        $stateModel = new State();
        $states = $stateModel->getByCountry($countryId);
        
        $options = '<option value="">Select State</option>';
        foreach ($states as $state) {
            $selected = ($selectedValue && $selectedValue == $state['id']) ? 'selected' : '';
            $options .= '<option value="' . $state['id'] . '" ' . $selected . '>' . htmlspecialchars($state['name']) . '</option>';
        }
        
        return $options;
    }
    
    /**
     * Get cities by state for dropdown
     */
    public static function getCitiesByState($stateId, $selectedValue = null) {
        require_once __DIR__ . '/../models/City.php';
        $cityModel = new City();
        $cities = $cityModel->getByState($stateId);
        
        $options = '<option value="">Select City</option>';
        foreach ($cities as $city) {
            $selected = ($selectedValue && $selectedValue == $city['id']) ? 'selected' : '';
            $options .= '<option value="' . $city['id'] . '" ' . $selected . '>' . htmlspecialchars($city['name']) . '</option>';
        }
        
        return $options;
    }
    
    /**
     * Validate master data
     */
    public static function validateMasterData($type, $data, $isUpdate = false, $recordId = null) {
        $model = self::getModelByType($type);
        if (!$model) {
            return ['general' => 'Invalid master type'];
        }
        
        // Use model's validation if available
        if (method_exists($model, 'validateMasterData')) {
            return $model->validateMasterData($data, $isUpdate, $recordId);
        }
        
        // Basic validation
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }
        
        return $errors;
    }
    
    /**
     * Format master record for display
     */
    public static function formatMasterRecord($type, $record) {
        $formatted = [
            'id' => $record['id'],
            'name' => $record['name'],
            'status' => $record['status'],
            'created_at' => $record['created_at'],
            'updated_at' => $record['updated_at'] ?? null
        ];
        
        // Add type-specific fields
        switch ($type) {
            case 'states':
                $formatted['country_name'] = $record['country_name'] ?? null;
                $formatted['zone_name'] = $record['zone_name'] ?? null;
                break;
            case 'cities':
                $formatted['state_name'] = $record['state_name'] ?? null;
                $formatted['country_name'] = $record['country_name'] ?? null;
                break;
        }
        
        return $formatted;
    }
    
    /**
     * Get master statistics
     */
    public static function getMasterStats($type) {
        $model = self::getModelByType($type);
        if (!$model) {
            return null;
        }
        
        if (method_exists($model, 'getMasterStats')) {
            return $model->getMasterStats();
        }
        
        // Basic stats
        return [
            'total' => $model->count(),
            'active' => $model->count(['status' => 'active']),
            'inactive' => $model->count(['status' => 'inactive'])
        ];
    }
}
?>