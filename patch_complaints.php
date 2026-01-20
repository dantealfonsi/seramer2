<?php
$indexFile = 'c:/xampp/htdocs/seramer2/views/complaints/index.php';
$viewFile = 'c:/xampp/htdocs/seramer2/views/complaints/view.php';

// Patch index.php
if (file_exists($indexFile)) {
    $content = file_get_contents($indexFile);
    // Find the btn-group in the table
    $pattern = '/<div class="btn-group" role="group">.*?<\/div>/s';
    
    // We only want to replace the one inside the tbody
    // Let's be more specific
    $replacement = '<div class="btn-group" role="group">
                                                    <a href="view.php?id=<?php echo $complaint[\'complaint_id\']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="openHistoryModal(<?php echo $complaint[\'complaint_id\']; ?>)" title="Agregar Seguimiento">
                                                        <i class="ri-history-line"></i>
                                                    </button>
                                                    <?php if ($_SESSION[\'selected_department\'] === \'Recursos Humanos\'): ?>
                                                        <a href="edit.php?id=<?php echo $complaint[\'complaint_id\']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $complaint[\'complaint_id\']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                    <?php endif; ?>
                                                </div>';
    
    // Actually, there's only one btn-group with role="group" inside the loop.
    // Let's use a more unique target if possible.
    $targetIndented = '<div class="btn-group" role="group">
                                                    <?php // if ($rol->hasPermission(\'COMPLAINTS\', \'r\')): ?>
                                                    <a href="view.php?id=<?php echo $complaint[\'complaint_id\']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                    <?php // endif; ?>
                                                    <?php // if ($rol->hasPermission(\'COMPLAINTS\', \'w\')): ?>
                                                    <a href="edit.php?id=<?php echo $complaint[\'complaint_id\']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $complaint[\'complaint_id\']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                    <?php // endif; ?>
                                                </div>';
                                                
    // Normalize and replace
    $content = str_replace("\r\n", "\n", $content);
    $targetIndented = str_replace("\r\n", "\n", $targetIndented);
    
    // If literal fails, use regex
    if (strpos($content, $targetIndented) === false) {
        // Regex to match the block with flexible whitespace
        $pattern = '/<div class="btn-group" role="group">\s*<\?php \/\/ if \(\$rol->hasPermission\(\'COMPLAINTS\', \'r\'\)\): \?>\s*<a href="view\.php\?id=<\?php echo \$complaint\[\'complaint_id\'\]; \?>"\s*class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"><\/i><\/a>\s*<\?php \/\/ endif; \?>\s*<\?php \/\/ if \(\$rol->hasPermission\(\'COMPLAINTS\', \'w\'\)\): \?>\s*<a href="edit\.php\?id=<\?php echo \$complaint\[\'complaint_id\'\]; \?>"\s*class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"><\/i><\/a>\s*<button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete\(<\?php echo \$complaint\[\'complaint_id\'\]; \?>\)" title="Eliminar"><i class="ri-delete-bin-line"><\/i><\/button>\s*<\?php \/\/ endif; \?>\s*<\/div>/s';
        $content = preg_replace($pattern, $replacement, $content);
    } else {
        $content = str_replace($targetIndented, $replacement, $content);
    }
    file_put_contents($indexFile, $content);
    echo "index.php patched\n";
}

// Patch view.php
if (file_exists($viewFile)) {
    $content = file_get_contents($viewFile);
    $content = str_replace("\r\n", "\n", $content);
    
    // History buttons in timeline
    $historyReplacement = '<?php if ($_SESSION[\'selected_department\'] === \'Recursos Humanos\'): ?>
                                                    <div class="btn-group">
                                                        <a href="../complaint_tracking/edit.php?id=<?php echo htmlspecialchars($record[\'tracking_id\']); ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="ri-pencil-line"></i></a>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteTrackingRecord(<?php echo htmlspecialchars($record[\'tracking_id\']); ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                    </div>
                                                    <?php endif; ?>';
                                                    
    $historyTargetPattern = '/<div class="btn-group">\s*<a href="\.\.\/complaint_tracking\/edit\.php\?id=<\?php echo htmlspecialchars\(\$record\[\'tracking_id\'\]\); \?>"\s*class="btn btn-sm btn-outline-primary" title="Editar"><i class="ri-pencil-line"><\/i><\/a>\s*<button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteTrackingRecord\(<\?php echo htmlspecialchars\(\$record\[\'tracking_id\'\]\); \?>\)" title="Eliminar"><i class="ri-delete-bin-line"><\/i><\/button>\s*<\/div>/s';
    
    $content = preg_replace($historyTargetPattern, $historyReplacement, $content);
    file_put_contents($viewFile, $content);
    echo "view.php patched\n";
}
?>
