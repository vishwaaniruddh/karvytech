<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/DynamicSurvey.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Survey ID required");

$surveyModel = new DynamicSurvey();
$survey = $surveyModel->find($id);
if (!$survey) die("Survey not found");

$fields = $surveyModel->getFields($id);

ob_start();
?>

<div class="max-w-3xl mx-auto py-10">
    <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200">
        <!-- Header -->
        <div class="bg-primary p-6 text-white bg-blue-600">
            <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($survey['title']); ?></h1>
            <?php if ($survey['description']): ?>
                <p class="mt-2 text-blue-100"><?php echo htmlspecialchars($survey['description']); ?></p>
            <?php endif; ?>
        </div>

        <!-- Form Body -->
        <div class="p-8">
            <form id="dynamicSurveyForm" enctype="multipart/form-data">
                <input type="hidden" name="survey_id" value="<?php echo $id; ?>">
                
                <div class="space-y-6">
                    <?php 
                    $inSection = false;
                    foreach ($fields as $index => $field): 
                        if ($field['field_type'] === 'section'):
                            if ($inSection) echo '</div></div>'; // Close previous section contents and card
                            $inSection = true;
                            ?>
                            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm mb-8">
                                <div class="bg-gray-50 p-5 border-b border-gray-200">
                                    <h2 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($field['label']); ?></h2>
                                    <?php if ($field['options']): ?>
                                        <p class="text-xs text-gray-500 mt-1 font-medium italic"><?php echo htmlspecialchars($field['options']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="p-6 space-y-6">
                        <?php else: ?>
                            <div class="form-group">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <?php echo htmlspecialchars($field['label']); ?>
                                    <?php if ($field['is_required']): ?>
                                        <span class="text-red-500">*</span>
                                    <?php endif; ?>
                                </label>

                                <?php 
                                $name = "values[" . $field['id'] . "]";
                                $required = $field['is_required'] ? 'required' : '';
                                
                                switch ($field['field_type']) {
                                    case 'text':
                                        echo '<input type="text" name="'.$name.'" class="form-input" '.$required.'>';
                                        break;
                                    case 'number':
                                        echo '<input type="number" name="'.$name.'" class="form-input" '.$required.'>';
                                        break;
                                    case 'textarea':
                                        echo '<textarea name="'.$name.'" class="form-textarea" rows="3" '.$required.'></textarea>';
                                        break;
                                    case 'date':
                                        echo '<input type="date" name="'.$name.'" class="form-input" '.$required.'>';
                                        break;
                                    case 'datetime':
                                    case 'datetime-local':
                                        echo '<input type="datetime-local" name="'.$name.'" class="form-input" '.$required.'>';
                                        break;
                                    case 'select':
                                        $options = explode(',', $field['options']);
                                        echo '<select name="'.$name.'" class="form-select" '.$required.'>';
                                        echo '<option value="">Select an option...</option>';
                                        foreach ($options as $opt) {
                                            $opt = trim($opt);
                                            echo '<option value="'.htmlspecialchars($opt).'">'.htmlspecialchars($opt).'</option>';
                                        }
                                        echo '</select>';
                                        break;
                                    case 'radio':
                                        $options = explode(',', $field['options']);
                                        echo '<div class="space-y-2 mt-2">';
                                        foreach ($options as $opt) {
                                            $opt = trim($opt);
                                            echo '<label class="flex items-center">';
                                            echo '<input type="radio" name="'.$name.'" value="'.htmlspecialchars($opt).'" class="form-radio" '.$required.'>';
                                            echo '<span class="ml-2 text-sm text-gray-700">'.htmlspecialchars($opt).'</span>';
                                            echo '</label>';
                                        }
                                        echo '</div>';
                                        break;
                                    case 'checkbox':
                                        $options = explode(',', $field['options']);
                                        echo '<div class="space-y-2 mt-2">';
                                        foreach ($options as $opt) {
                                            $opt = trim($opt);
                                            echo '<label class="flex items-center">';
                                            echo '<input type="checkbox" name="'.$name.'[]" value="'.htmlspecialchars($opt).'" class="form-checkbox">';
                                            echo '<span class="ml-2 text-sm text-gray-700">'.htmlspecialchars($opt).'</span>';
                                            echo '</label>';
                                        }
                                        echo '</div>';
                                        break;
                                    case 'file':
                                        $config = json_decode($field['file_config'], true);
                                        $multiple = ($config['multiple'] ?? false) ? 'multiple' : '';
                                        $accept = ($config['accept'] ?? '') ?: '*';
                                        echo '<input type="file" name="files['.$field['id'].']'.($multiple ? '[]' : '').'" class="form-input" '.$required.' '.$multiple.' accept="'.htmlspecialchars($accept).'">';
                                        if ($accept !== '*') {
                                            echo '<p class="mt-1 text-xs text-gray-500">Accepted: '.$accept.'</p>';
                                        }
                                        break;
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($index === count($fields) - 1 && $inSection) echo '</div></div>'; ?>
                    <?php endforeach; ?>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                    <button type="submit" id="submitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-md transition duration-200">
                        Submit Response
                    </button>
                </div>
            </form>
            
            <div id="successMessage" class="hidden mt-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-center">
                <svg class="w-12 h-12 mx-auto mb-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <h3 class="text-lg font-bold">Thank You!</h3>
                <p>Your response has been submitted successfully.</p>
                <button onclick="location.reload()" class="mt-4 text-blue-600 font-medium hover:underline">Submit another response</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('dynamicSurveyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Submitting...';
        
        const formData = new FormData(this);
        
        fetch('../../api/surveys_submit.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('dynamicSurveyForm').classList.add('hidden');
                document.getElementById('successMessage').classList.remove('hidden');
            } else {
                alert(data.message || 'Error submitting response');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(err => {
            alert('An error occurred during submission');
            btn.disabled = false;
            btn.textContent = originalText;
        });
    });
</script>

<style>
    .form-input, .form-textarea, .form-select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        margin-top: 0.25rem;
    }
    .form-checkbox, .form-radio {
        width: 1.125rem;
        height: 1.125rem;
        color: #2563eb;
        border-color: #d1d5db;
        border-radius: 0.25rem;
    }
</style>

<?php
$content = ob_get_clean();
// We use a clean layout for the preview
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($survey['title']); ?> - Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php echo $content; ?>
</body>
</html>
