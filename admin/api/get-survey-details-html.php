<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/SiteSurvey.php';

// Auth check
if (!Auth::isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$siteId = $_GET['site_id'] ?? null;
$type = $_GET['type'] ?? 'legacy';

if (!$siteId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

$db = Database::getInstance()->getConnection();

function getRepeatCount($section, $sections, $formData) {
    if (!$section['is_repeatable'] && stripos($section['title'] ?? '', 'floor wise') === false) return 1;
    
    $sectionTitle = strtolower(trim($section['title'] ?? ''));
    if ($sectionTitle === 'floor wise camera details') {
        foreach ($sections as $s) {
            if (strtolower(trim($s['title'] ?? '')) === 'general information') {
                foreach ($s['fields'] as $field) {
                    if (strtolower(trim($field['label'] ?? '')) === 'no of floors') {
                        return max(0, intval($formData[$field['id']] ?? 0));
                    }
                }
            }
        }
    }
    
    if ($section['is_repeatable'] && $section['repeat_source_field_id']) {
        return max(0, intval($formData[$section['repeat_source_field_id']] ?? 0));
    }
    return 1;
}

function getFieldKey($fieldId, $repeatIndex, $section) {
    if (!$section['is_repeatable'] && stripos($section['title'] ?? '', 'floor wise') === false) return $fieldId;
    return $fieldId . '_' . $repeatIndex;
}

ob_start();

if ($type === 'dynamic') {
    $stmt = $db->prepare("
        SELECT dsr.*, ds.title as survey_title, ds.description as survey_description
        FROM dynamic_survey_responses dsr
        LEFT JOIN dynamic_surveys ds ON dsr.survey_form_id = ds.id
        WHERE dsr.site_id = ? AND dsr.survey_status = 'approved'
        ORDER BY dsr.submitted_date DESC LIMIT 1
    ");
    $stmt->execute([$siteId]);
    $response = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($response) {
        $formData = json_decode($response['form_data'], true) ?? [];
        
        $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE survey_id = ? AND parent_section_id IS NULL ORDER BY sort_order ASC");
        $stmt->execute([$response['survey_form_id']]);
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($sections as &$s) {
            $fStmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
            $fStmt->execute([$s['id']]);
            $s['fields'] = $fStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $subStmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE parent_section_id = ? ORDER BY sort_order ASC");
            $subStmt->execute([$s['id']]);
            $s['subsections'] = $subStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($s['subsections'] as &$sub) {
                $sfStmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
                $sfStmt->execute([$sub['id']]);
                $sub['fields'] = $sfStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        foreach ($sections as $section) {
            $repeatCount = getRepeatCount($section, $sections, $formData);
            
            for ($r = 1; $r <= $repeatCount; $r++) {
                $hasContent = false;
                foreach($section['fields'] as $f) if(isset($formData[getFieldKey($f['id'], $r, $section)]) && $formData[getFieldKey($f['id'], $r, $section)] !== '') $hasContent = true;
                if (!$hasContent) {
                    foreach($section['subsections'] as $sub) {
                        foreach($sub['fields'] as $f) if(isset($formData[getFieldKey($f['id'], $r, $section)]) && $formData[getFieldKey($f['id'], $r, $section)] !== '') $hasContent = true;
                    }
                }
                if (!$hasContent) continue;

                echo '<div class="mb-12 last:mb-0">';
                $titleSuffix = ($repeatCount > 1) ? " — Instance #$r" : "";
                echo '<h4 class="text-[10px] font-extrabold text-blue-500 uppercase tracking-[0.2em] mb-6 flex items-center gap-4">';
                echo htmlspecialchars($section['title']) . $titleSuffix;
                echo '<span class="flex-1 h-px bg-gray-100/50"></span>';
                echo '</h4>';
                
                if (!empty($section['fields'])) {
                    echo '<div class="grid grid-cols-2 md:grid-cols-4 gap-x-8 gap-y-6 bg-white p-7 rounded-3xl border border-gray-100/50 shadow-sm mb-6">';
                    foreach ($section['fields'] as $field) {
                        if ($field['field_type'] === 'file') continue;
                        $key = getFieldKey($field['id'], $r, $section);
                        $value = $formData[$key] ?? null;
                        if ($value === null || $value === '') continue;
                        
                        echo '<div>';
                        echo '<label class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-2">' . htmlspecialchars($field['label']) . '</label>';
                        echo '<p class="text-[13px] font-bold text-gray-800">' . htmlspecialchars(is_array($value) ? implode(', ', $value) : $value) . '</p>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                
                if (!empty($section['subsections'])) {
                    foreach ($section['subsections'] as $sub) {
                        $subFieldsCount = 0;
                        foreach($sub['fields'] as $f) if(isset($formData[getFieldKey($f['id'], $r, $section)]) && $formData[getFieldKey($f['id'], $r, $section)] !== '') $subFieldsCount++;
                        if ($subFieldsCount === 0) continue;

                        echo '<div class="bg-gray-50/30 p-7 rounded-3xl border border-gray-100/50 mb-4">';
                        echo '<h5 class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-3">';
                        echo '<span class="w-1.5 h-px bg-gray-300"></span>' . htmlspecialchars($sub['title']) . '</h5>';
                        echo '<div class="grid grid-cols-2 md:grid-cols-4 gap-8">';
                        foreach ($sub['fields'] as $field) {
                            if ($field['field_type'] === 'file') continue;
                            $key = getFieldKey($field['id'], $r, $section);
                            $value = $formData[$key] ?? null;
                            if ($value === null || $value === '') continue;
                            echo '<div>';
                            echo '<label class="block text-[8px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">' . htmlspecialchars($field['label']) . '</label>';
                            echo '<p class="text-[12px] font-bold text-gray-700">' . htmlspecialchars(is_array($value) ? implode(', ', $value) : $value) . '</p>';
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                }
                echo '</div>';
            }
        }
    } else {
        echo '<div class="text-center py-12 opacity-30 italic text-[10px] uppercase tracking-widest font-bold">No technical manifest hydrated</div>';
    }
} else {
    // Legacy Survey - Grouped Vertical Layout
    $stmt = $db->prepare("
        SELECT ss.*, v.name as vendor_name, u.username as surveyor_name
        FROM site_surveys ss
        LEFT JOIN vendors v ON ss.vendor_id = v.id
        LEFT JOIN users u ON ss.created_by = u.id
        WHERE ss.site_id = ?
        ORDER BY ss.submitted_date DESC LIMIT 1
    ");
    $stmt->execute([$siteId]);
    $survey = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($survey) {
        $groups = [
            'Physical Assessment' => [
                'Store Model' => 'store_model',
                'Floor Height' => 'floor_height',
                'Ceiling Type' => 'ceiling_type'
            ],
            'Camera Audit' => [
                'Total Cameras' => 'total_cameras',
                'SLP Cameras' => 'slp_cameras',
                'Analytic Cameras' => 'analytic_cameras',
                'Zones Recommended' => 'zones_recommended'
            ],
            'Technical & Logistics' => [
                'Power Status' => 'power_availability',
                'Connectivity' => 'network_connectivity',
                'Ladder Specs' => 'ladder_size',
                'Accessibility' => 'site_accessibility'
            ]
        ];
        
        foreach ($groups as $groupName => $fields) {
            echo '<div class="mb-10">';
            echo '<h4 class="text-[10px] font-extrabold text-blue-500 uppercase tracking-[0.2em] mb-6 flex items-center gap-4">' . $groupName . '<span class="flex-1 h-px bg-gray-100"></span></h4>';
            echo '<div class="grid grid-cols-2 md:grid-cols-4 gap-8 bg-white p-7 rounded-3xl border border-gray-100/50 shadow-sm">';
            foreach ($fields as $label => $col) {
                $value = $survey[$col] ?? 'N/A';
                echo '<div>';
                echo '<label class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-2">' . $label . '</label>';
                echo '<p class="text-[13px] font-bold text-gray-800">' . htmlspecialchars($value) . '</p>';
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }
    }
}

$html = ob_get_clean();
echo $html;
