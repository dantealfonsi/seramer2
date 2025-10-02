<?php
// views/reports/view.php - Vista para mostrar el reporte final generado
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>
    <style>
        .report-container { 
            max-width: 800px; 
            margin: auto; 
            padding: 30px; 
        }
        pre { 
            font-family: "Courier New", Courier, monospace; 
            line-height: 1.6; 
            color: #333; 
            background-color: #fff; 
            white-space: pre-wrap;
            word-wrap: break-word; 
            font-family: inherit; 
            font-size: 16px; 
            margin: 0; 
        }
    </style>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="report-container">
                            <pre><?php echo $finalReport; ?></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
