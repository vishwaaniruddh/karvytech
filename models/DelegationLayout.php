<?php
require_once __DIR__ . '/BaseModel.php';

class DelegationLayout extends BaseModel {
    protected $table = 'delegation_layouts';
    protected $uploadDir = 'uploads/delegations/';
    protected $maxFileSize = 10485760; // 10MB in bytes
    
    protected $allowedTypes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'application/vnd.ms-excel', 
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    protected $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 
        'pdf', 
        'xls', 'xlsx', 
        'doc', 'docx'
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Upload a layout file for a delegation
     * 
     * @param int $delegationId The delegation ID
     * @param array $file The $_FILES array element
     * @param string $remarks Optional remarks
     * @param int $uploadedBy User ID of uploader
     * @return int The layout record ID
     * @throws Exception on validation or upload failure
     */
    public function uploadLayout($delegationId, $file, $remarks, $uploadedBy) {
        // Validate file
        $this->validateFile($file);
        
        // Get file extension
        $extension = $this->getFileExtension($file['name']);
        
        // Generate unique filename
        $storedFilename = $this->generateUniqueFilename($delegationId, $extension);
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }
        
        // Check if directory is writable
        if (!is_writable($this->uploadDir)) {
            throw new Exception('Upload directory is not writable');
        }
        
        // Full path for the file
        $filePath = $this->uploadDir . $storedFilename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Insert record into database
        $data = [
            'delegation_id' => $delegationId,
            'original_filename' => $file['name'],
            'stored_filename' => $storedFilename,
            'file_path' => $filePath,
            'file_type' => $extension,
            'file_size' => $file['size'],
            'mime_type' => $file['type'],
            'remarks' => $remarks,
            'uploaded_by' => $uploadedBy
        ];
        
        try {
            $layoutId = $this->create($data);
            return $layoutId;
        } catch (Exception $e) {
            // If database insert fails, delete the uploaded file
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            throw new Exception('Failed to save file information: ' . $e->getMessage());
        }
    }
    
    /**
     * Get all layout files for a delegation
     * 
     * @param int $delegationId The delegation ID
     * @return array Array of layout records with uploader info
     */
    public function getLayoutsByDelegation($delegationId) {
        // echo "SELECT dl.*, u.username as uploader_name
        //     FROM {$this->table} dl
        //     INNER JOIN users u ON dl.uploaded_by = u.id
        //     WHERE dl.delegation_id = ?
        //     ORDER BY dl.uploaded_at DESC";

        $stmt = $this->db->prepare("
            SELECT dl.*, u.username as uploader_name
            FROM {$this->table} dl
            INNER JOIN users u ON dl.uploaded_by = u.id
            WHERE dl.delegation_id = ?
            ORDER BY dl.uploaded_at DESC
        ");
        $stmt->execute([$delegationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Delete a layout file
     * 
     * @param int $layoutId The layout record ID
     * @return bool Success status
     * @throws Exception if file or record not found
     */
    public function deleteLayout($layoutId) {
        // Fetch layout record
        $layout = $this->find($layoutId);
        
        if (!$layout) {
            throw new Exception('Layout record not found');
        }
        
        // Delete physical file
        if (file_exists($layout['file_path'])) {
            if (!unlink($layout['file_path'])) {
                throw new Exception('Failed to delete file from filesystem');
            }
        }
        
        // Delete database record
        return $this->delete($layoutId);
    }
    
    /**
     * Validate uploaded file
     * 
     * @param array $file The $_FILES array element
     * @throws Exception on validation failure
     */
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            
            $message = $errorMessages[$file['error']] ?? 'Unknown upload error';
            throw new Exception($message);
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds 10MB limit');
        }
        
        if ($file['size'] === 0) {
            throw new Exception('File is empty');
        }
        
        // Validate MIME type
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new Exception('Invalid file type. Allowed: images, PDF, Excel, Word documents');
        }
        
        // Validate file extension
        $extension = $this->getFileExtension($file['name']);
        if (!in_array(strtolower($extension), $this->allowedExtensions)) {
            throw new Exception('Invalid file extension. Allowed: ' . implode(', ', $this->allowedExtensions));
        }
        
        return true;
    }
    
    /**
     * Generate unique filename for storage
     * 
     * @param int $delegationId The delegation ID
     * @param string $extension File extension
     * @return string Unique filename
     */
    private function generateUniqueFilename($delegationId, $extension) {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        return "layout_{$delegationId}_{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * Get file extension from filename
     * 
     * @param string $filename The filename
     * @return string File extension
     */
    private function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * Convert bytes to human-readable format
     * 
     * @param int $bytes File size in bytes
     * @return string Human-readable size
     */
    private function getHumanReadableSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
?>
