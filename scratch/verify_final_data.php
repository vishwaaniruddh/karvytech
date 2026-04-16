<?php
require_once __DIR__ . '/../models/MaterialRequest.php';
$model = new MaterialRequest();
$data = $model->findWithDetails(90);

echo "--- Final Data for Request #90 ---\n";
echo "Site Name: " . $data['site_name'] . "\n";
echo "Vendor Company: " . $data['vendor_company_name'] . "\n";
echo "Delegated Vendor: " . $data['delegated_vendor_name'] . "\n";
echo "Unified Survey Status: " . $data['unified_survey_status'] . "\n";
echo "Unified Survey Date: " . $data['unified_survey_date'] . "\n";
?>
