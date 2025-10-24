<?php
// views/reports/editor.php - Vista para el editor de plantillas de reportes
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>
    <title>Editor de Plantillas de Reportes</title>
    <style>        
        .container { max-width: 1000px; 
            margin: auto; 
            background: #fff; 
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        h1 { color: #2c3e50; }
        form { display: flex; flex-direction: column; gap: 15px; }
        .form-group { display: flex; flex-direction: column; }
        label { font-weight: bold; margin-bottom: 5px; color: #555; }
        select, textarea {
            padding: 10px; border-radius: 5px; border: 1px solid #ccc;
            font-family: inherit; font-size: 16px;
        }
        textarea {
            height: 400px; resize: vertical;
            font-family: "Courier New", Courier, monospace;
        }
        .buttons-container { display: flex; justify-content: flex-end; gap: 10px; }
        button {
            padding: 10px 20px; border: none; border-radius: 5px;
            font-size: 16px; font-weight: bold; color: white;
            cursor: pointer; transition: background-color 0.3s;
        }
        .btn-load { background-color: #3498db; }
        .btn-load:hover { background-color: #2980b9; }
        .btn-save { background-color: #2ecc71; }
        .btn-save:hover { background-color: #27ae60; }
        .message {
            padding: 15px; border-radius: 5px; margin-bottom: 20px;
            font-weight: bold;
            background-color: #e8f5e9; color: #2e7d32;
        }
        .fields-container {
            padding: 15px; background: #ecf0f1; border-radius: 5px;
            border: 1px solid #bdc3c7;
        }
        .fields-container h3 { margin-top: 0; }
        .field-tag {
            display: inline-block; background: #95a5a6; color: white;
            padding: 5px 10px; margin: 3px; border-radius: 4px;
            cursor: pointer; transition: background-color 0.2s;
            font-family: "Courier New", Courier, monospace; font-size: 14px;
        }
        .field-tag:hover { background: #7f8c8d; }
    </style>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <h1>Editor de Plantillas de Reportes</h1>

                        <?php if (!empty($message)): ?>
                            <div class="message"><?php echo htmlspecialchars($message); ?></div>
                        <?php endif; ?>

                        <!-- Formulario para seleccionar y cargar el reporte -->
                        <form method="POST" action="index.php?action=edit">
                            <div class="form-group">
                                <label for="report_file_select">Seleccionar Plantilla:</label>
                                <select name="report_file" id="report_file_select">
                                    <?php foreach ($reportFiles as $file): ?>
                                        <option value="<?php echo htmlspecialchars($file); ?>" <?php echo ($file === $currentReport) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($file); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="buttons-container">
                                <button type="submit" class="btn-load">Cargar Plantilla</button>
                            </div>
                        </form>
                        
                        <hr style="margin: 20px 0;">

                        <!-- Formulario para editar y guardar el contenido -->
                        <form method="POST" action="index.php?action=edit">
                            <input type="hidden" name="report_file" value="<?php echo htmlspecialchars($currentReport); ?>">

                            <div class="fields-container">
                                <h3>Campos Disponibles (clic para insertar)</h3>
                                <?php foreach ($availableFields as $field): ?>
                                    <span class="field-tag" data-field="{{<?php echo $field; ?>}}">{{<?php echo $field; ?>}}</span>
                                <?php endforeach; ?>
                            </div>

                            <div class="form-group">
                                <label for="report_content">Contenido de "<?php echo htmlspecialchars($currentReport); ?>"</label>
                                <textarea name="report_content" id="report_content"><?php echo htmlspecialchars($reportContent); ?></textarea>
                            </div>
                            
                            <div class="buttons-container">
                                <button type="submit" class="btn-save">Guardar Cambios</button>
                            </div>
                        </form>

                        <div style="margin-top: 20px;">
                            <p><strong>Para probar:</strong> Guarda tu plantilla y luego visita <a href="index.php?report=<?php echo $currentReport ?>&action=view&id=4" >este enlace para ver el reporte con ID 1</a>.</p>
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
