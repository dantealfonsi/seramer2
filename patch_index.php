<?php
$file = 'c:/xampp/htdocs/seramer2/views/complaints/index.php';
$content = file_get_contents($file);

$target = '<div class="btn-group" role="group">
                                                    <?php // if ($rol->hasPermission(\'COMPLAINTS\', \'r\')): ?>
                                                    <a href="view.php?id=<?php echo $complaint[\'complaint_id\']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                    <?php // endif; ?>
                                                    <?php // if ($rol->hasPermission(\'COMPLAINTS\', \'w\')): ?>
                                                    <a href="edit.php?id=<?php echo $complaint[\'complaint_id\']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $complaint[\'complaint_id\']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                    <?php // endif; ?>
                                                </div>';

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

// Remove line numbers if they were accidentally included and normalize whitespace
$normalizedContent = preg_replace(\'/\r\n|\r|\n/\', "\n", $content);
$normalizedTarget = preg_replace(\'/\r\n|\r|\n/\', "\n", $target);
$normalizedReplacement = preg_replace(\'/\r\n|\r|\n/\', "\n", $replacement);

// Try literal match first
if (strpos($normalizedContent, $normalizedTarget) !== false) {
    $newContent = str_replace($normalizedTarget, $normalizedReplacement, $normalizedContent);
    file_put_contents($file, $newContent);
    echo "Success: Literal match found and replaced.\n";
} else {
    // Try matching without specific indentation
    echo "Error: Literal match failed.\n";
    
    // More flexible match for the block
    $pattern = \'/ <div class="btn-group" role="group">.*?<\/div>/s\';
    // This is risky, let\'s be careful.
}
?>
