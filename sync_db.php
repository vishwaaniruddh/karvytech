<?php
// Connection details for local and server DB
$localConn = new mysqli('localhost', 'reporting', 'reporting', 'u444388293_karvy_project');
$serverConn = new mysqli('193.203.184.112', 'u444388293_karvytech_test', 'AVav@@2025', 'u444388293_karvytech_test');

// Handle Sync Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sync_table'])) {
    $tableToSync = $_POST['sync_table'];
    
    // 1. Get Create Statement from Local
    $res = $localConn->query("SHOW CREATE TABLE `$tableToSync`");
    if ($res && $row = $res->fetch_assoc()) {
        $createSQL = $row['Create Table'];
        
        // 2. Execute on Server
        if ($serverConn->query($createSQL)) {
            echo "<script>alert('Table `$tableToSync` created successfully on server.'); window.location.href='sync_db.php';</script>";
        } else {
            echo "<script>alert('Error creating table: " . $serverConn->error . "');</script>";
        }
    } else {
         echo "<script>alert('Error fetching local table structure.');</script>";
    }
}

// Helper to sync single column
function syncColumnDefinition($table, $col, $localConn, $serverConn) {
    // Get Create Statement
    $res = $localConn->query("SHOW CREATE TABLE `$table`");
    if ($res && $row = $res->fetch_assoc()) {
        $createSQL = $row['Create Table'];
        $colEsc = preg_quote($col, '/');
        $pattern = "/^\s*`$colEsc`\s+(.*?),?$/m";
        
        if (preg_match($pattern, $createSQL, $matches)) {
            $fullDef = trim($matches[0]);
            if (substr($fullDef, -1) === ',') {
                $fullDef = substr($fullDef, 0, -1);
            }
            $alterSQL = "ALTER TABLE `$table` ADD $fullDef";
            return $serverConn->query($alterSQL) ? true : $serverConn->error;
        } else {
            return "Definition not found";
        }
    }
    return "Local table not found";
}

// Handle Sync Column Action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sync_column'])) {
        $table = $_POST['table'];
        $col = $_POST['column'];
        $res = syncColumnDefinition($table, $col, $localConn, $serverConn);
        if ($res === true) {
             echo "<script>alert('Column `$col` synced successfully.'); window.location.href='sync_db.php';</script>";
        } else {
             echo "<script>alert('Error syncing `$col`: $res');</script>";
        }
    }
    
    if (isset($_POST['sync_all_columns'])) {
        $table = $_POST['table'];
        $colDiff = compareColumns($table, $localConn, $serverConn);
        $missing = array_keys($colDiff['onlyLocal']);
        $successCount = 0;
        $errors = [];
        
        foreach($missing as $col) {
            $res = syncColumnDefinition($table, $col, $localConn, $serverConn);
            if($res === true) {
                $successCount++;
            } else {
                $errors[] = "$col: $res";
            }
        }
        
        if(count($errors) === 0) {
            echo "<script>alert('All $successCount columns synced successfully.'); window.location.href='sync_db.php';</script>";
        } else {
            $errMsg = implode("\\n", $errors);
            echo "<script>alert('Synced $successCount columns. Errors:\\n$errMsg'); window.location.href='sync_db.php';</script>";
        }
    }
}
// (existing sync_table logic was simpler, keeping it conceptually separate or merging? I'll replace the block covering sync_column)

// Fetch table lists
$localTables = getTables($localConn);
$serverTables = getTables($serverConn);

$matchingTables = array_intersect($localTables, $serverTables);
$uniqueToLocal = array_diff($localTables, $serverTables);
$uniqueToServer = array_diff($serverTables, $localTables);

function getTables($conn) {
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    return $tables;
}

function getColumns($conn, $table) {
    if(!$conn) return [];
    $columns = [];
    $result = $conn->query("SHOW FULL COLUMNS FROM `$table`");
    if($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = [
                'Type' => $row['Type'],
                'Collation' => $row['Collation'] ?? 'NULL'
            ];
        }
    }
    return $columns;
}

function compareColumns($table, $localConn, $serverConn) {
    $cols1 = getColumns($localConn, $table);
    $cols2 = getColumns($serverConn, $table);

    $typeMismatch = [];
    $collationMismatch = [];

    foreach ($cols1 as $col => $attr1) {
        if (isset($cols2[$col])) {
            if ($attr1['Type'] !== $cols2[$col]['Type']) {
                $typeMismatch[$col] = [
                    'Local' => $attr1['Type'], 
                    'Server' => $cols2[$col]['Type']
                ];
            }
            if ($attr1['Collation'] !== $cols2[$col]['Collation']) {
                $collationMismatch[$col] = [
                    'Local' => $attr1['Collation'], 
                    'Server' => $cols2[$col]['Collation']
                ];
            }
        }
    }

    return [
        'onlyLocal' => array_diff_key($cols1, $cols2),
        'onlyServer' => array_diff_key($cols2, $cols1),
        'typeMismatch' => $typeMismatch,
        'collationMismatch' => $collationMismatch,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DB Difference Viewer</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    h1, h2 { color: #333; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 30px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    th, td { padding: 10px 15px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #343a40; color: white; }
    .highlight { background: #ffeeba; }
    .section-title { background: #007bff; color: #fff; padding: 8px 10px; font-weight: bold; }
    .cell-danger { background-color: #f8d7da; }
    .cell-warning { background-color: #fff3cd; }
    .cell-success { background-color: #d4edda; }
    .btn-sync { background-color: #28a745; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 4px; }
    .btn-sync:hover { background-color: #218838; }
    .btn-sm { font-size: 10px; padding: 2px 5px; margin-left: 5px; }
    .btn-blue { background-color: #17a2b8; }
    .btn-blue:hover { background-color: #138496; }
</style>
</head>
<body>

<h1>Database Schema Comparison</h1>

<div class="section-title">Tables Only in Local DB</div>
<table>
    <tr>
        <th>Table Name</th>
        <th style="width: 150px; text-align: right;">Action</th>
    </tr>
    <?php foreach ($uniqueToLocal as $table): ?>
        <tr>
            <td><?php echo $table; ?></td>
            <td style="text-align: right;">
                <form method="POST" onsubmit="return confirm('Are you sure you want to create table `<?php echo $table; ?>` on the server?');">
                    <input type="hidden" name="sync_table" value="<?php echo $table; ?>">
                    <button type="submit" class="btn-sync">Sync to Server</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<div class="section-title">Tables Only in Server DB</div>
<table>
    <tr><th>Table Name</th></tr>
    <?php foreach ($uniqueToServer as $table): ?>
        <tr><td><?php echo $table; ?></td></tr>
    <?php endforeach; ?>
</table>

<div class="section-title">Differences in Matching Tables</div>
<table>
    <tr>
        <th>Table Name</th>
        <th>Columns Only in Local</th>
        <th>Columns Only in Server</th>
        <th>Column Type Mismatches</th>
        <th>Collation Mismatches</th>
    </tr>
    <?php foreach ($matchingTables as $table): ?>
        <?php
        $colDiff = compareColumns($table, $localConn, $serverConn);
        if (empty($colDiff['onlyLocal']) && empty($colDiff['onlyServer']) && empty($colDiff['typeMismatch']) && empty($colDiff['collationMismatch'])) {
            continue;
        }
        ?>
        <tr class="highlight">
            <td><?php echo $table; ?></td>
            <td class="cell-warning">
                <?php if (!empty($colDiff['onlyLocal'])): ?>
                    <?php if(count($colDiff['onlyLocal']) > 1): ?>
                        <form method="POST" onsubmit="return confirm('Sync ALL <?php echo count($colDiff['onlyLocal']); ?> missing columns for `<?php echo $table; ?>`?');" style="margin-bottom: 10px; border-bottom: 1px dashed #ccc; padding-bottom: 5px;">
                            <input type="hidden" name="sync_all_columns" value="1">
                            <input type="hidden" name="table" value="<?php echo $table; ?>">
                            <button type="submit" class="btn-sync btn-blue btn-sm" style="margin:0; width:100%;">Sync All <?php echo count($colDiff['onlyLocal']); ?> Columns</button>
                        </form>
                    <?php endif; ?>
                    
                    <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($colDiff['onlyLocal'] as $col => $details): ?>
                        <li style="margin-bottom: 5px;">
                            <?php echo $col; ?> (<?php echo $details['Type']; ?>)
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Sync column `<?php echo $col; ?>` to server?');">
                                <input type="hidden" name="sync_column" value="1">
                                <input type="hidden" name="table" value="<?php echo $table; ?>">
                                <input type="hidden" name="column" value="<?php echo $col; ?>">
                                <button type="submit" class="btn-sync btn-sm">Sync</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    None
                <?php endif; ?>
            </td>
            <td class="cell-danger">
                <?php echo implode('<br>', array_keys($colDiff['onlyServer'])) ?: 'None'; ?>
            </td>
            <td class="cell-warning">
                <?php 
                if (!empty($colDiff['typeMismatch'])) {
                    foreach ($colDiff['typeMismatch'] as $col => $types) {
                        echo "$col (Local: {$types['Local']}, Server: {$types['Server']})<br>";
                    }
                } else {
                    echo "None";
                }
                ?>
            </td>
            <td class="cell-warning">
                <?php 
                if (!empty($colDiff['collationMismatch'])) {
                    foreach ($colDiff['collationMismatch'] as $col => $collations) {
                        echo "$col (Local: {$collations['Local']}, Server: {$collations['Server']})<br>";
                    }
                } else {
                    echo "None";
                }
                ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
