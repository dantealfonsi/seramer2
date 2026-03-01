<?php
// views/reports/editor.php - Vista para el editor de plantillas de reportes
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>
    <title>Editor de Plantillas de Reportes</title>
    <style>        
        .editor-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .form-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        select.form-select {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
            font-size: 1rem;
        }
        textarea.report-editor {
            width: 100%;
            height: 500px;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #ced4da;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 14px;
            line-height: 1.5;
            resize: vertical;
            background-color: #f8f9fa;
        }
        textarea.report-editor:focus {
            border-color: #837aff;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(131, 122, 255, 0.25);
        }
        .fields-container {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .fields-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #495057;
            display: flex;
            align-items: center;
        }
        .fields-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .field-tag {
            background: #e9ecef;
            color: #495057;
            padding: 6px 12px;
            border-radius: 20px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #dee2e6;
            user-select: none;
        }
        .field-tag:hover {
            background: #837aff;
            color: white;
            border-color: #837aff;
            transform: translateY(-1px);
        }
        .btn-action {
            padding: 10px 24px;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.3s;
        }
        .alert-custom {
            border-radius: 8px;
            border: 0;
            margin-bottom: 20px;
        }
    </style>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0 editor-container">
                        <div class="card-header bg-transparent border-bottom-0 pb-0 pt-4 px-4">
                            <h5 class="card-title d-flex align-items-center mb-0" style="font-size: 1.4rem;font-weight: 600;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-file-edit-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                Editor de Plantillas de Reportes
                            </h5>
                        </div>

                        <div class="card-body p-4">
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-success alert-custom d-flex align-items-center" role="alert">
                                    <i class="ri-checkbox-circle-line me-2 fs-5"></i>
                                    <div><?php echo htmlspecialchars($message); ?></div>
                                </div>
                            <?php endif; ?>

                            <!-- Selector de Plantilla -->
                            <div class="form-section">
                                <form method="POST" action="index.php?action=edit" class="row align-items-end g-3">
                                    <div class="col-md-8">
                                        <label for="report_file_select" class="form-label">Seleccionar Plantilla:</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="ri-file-text-line"></i></span>
                                            <select name="report_file" id="report_file_select" class="form-select">
                                                <?php foreach ($reportFiles as $file): ?>
                                                    <option value="<?php echo htmlspecialchars($file); ?>" <?php echo ($file === $currentReport) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($file); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary w-100 btn-action">
                                            <i class="ri-download-cloud-2-line me-2"></i>Cargar Plantilla
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Editor -->
                            <form method="POST" action="index.php?action=edit">
                                <input type="hidden" name="report_file" value="<?php echo htmlspecialchars($currentReport); ?>">

                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="fields-container">
                                            <div class="fields-title">
                                                <i class="ri-code-s-slash-line me-2 text-primary"></i>Campos Disponibles
                                                <small class="text-muted ms-2 fw-normal">(Haga clic para insertar en la posición del cursor)</small>
                                            </div>
                                            <div class="fields-wrapper">
                                                <?php foreach ($availableFields as $field): ?>
                                                    <span class="field-tag" data-field="{{<?php echo $field; ?>}}" title="Insertar {{<?php echo $field; ?>}}">
                                                        {{<?php echo $field; ?>}}
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group mb-4">
                                            <label for="report_content" class="form-label d-flex justify-content-between">
                                                <span>Contenido de: <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($currentReport); ?></span></span>
                                            </label>
                                            <textarea name="report_content" id="report_content" class="report-editor" spellcheck="false"><?php echo htmlspecialchars($reportContent); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                                    <button type="submit" class="btn btn-success btn-action">
                                        <i class="ri-save-3-line me-2"></i>Guardar Cambios
                                    </button>
                                </div>
                            </form>

                            <div class="mt-4 pt-3 text-center border-top">
                                <p class="text-muted mb-0">
                                    <i class="ri-lightbulb-line text-warning me-1"></i>
                                    <strong>Tip:</strong> Después de guardar, puedes 
                                    <a href="index.php?report=<?php echo urlencode($currentReport) ?>&action=view&id=4" target="_blank" class="text-decoration-none fw-bold">
                                         ver una vista previa <i class="ri-external-link-line"></i>
                                    </a> 
                                    usando datos de prueba.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const textarea = document.getElementById('report_content');
            const fieldTags = document.querySelectorAll('.field-tag');

            fieldTags.forEach(tag => {
                tag.addEventListener('click', () => {
                    const field = tag.getAttribute('data-field');
                    // Insertar el texto en la posición actual del cursor
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const text = textarea.value;
                    textarea.value = text.substring(0, start) + field + text.substring(end);
                    // Mover el cursor después del texto insertado
                    textarea.selectionStart = textarea.selectionEnd = start + field.length;
                    textarea.focus();
                });
            });
        });
    </script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
