<?php
// views/reports/view.php - Vista para mostrar el reporte final generado
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

?>
    <style>
        /* Estilos de PANTALLA (Screen Styles) */
        .report-container { 
            max-width: 800px; 
            margin: auto; 
            padding: 30px; 
            border: 1px solid #ddd; /* Solo para visualización en pantalla */
            box-shadow: 0 0 10px rgba(0,0,0,0.1); /* Solo para visualización en pantalla */
            background-color: #fff;
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
        /* Estilos del Botón (Ajustar según tu framework, ej. Bootstrap) */
        .print-button {
            margin-bottom: 20px;
            padding: 10px 20px;
            font-size: 18px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
        }
        
        /* ------------------------------------------------------------------ */
        /* Estilos de IMPRESIÓN (Print Styles) - ¡Esto es clave! */
        /* ------------------------------------------------------------------ */
        @media print {
            /* 1. Ocultar elementos que no deben imprimirse (navegación, botones, etc.) */
            header, nav, .navigation, .navigation-top, .footer, .print-button {
                display: none !important;
            }
            
            /* 2. Optimizar el diseño para el papel */
            body, .main-content, .container-fluid, .row, .col-12, .card {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                /* Eliminar bordes, sombras, etc. */
                box-shadow: none !important;
                border: none !important;
            }

            /* 3. Asegurar que el reporte ocupe el espacio sin restricciones de pantalla */
            .report-container {
                max-width: none !important; 
                margin: 0 !important;
                padding: 15px !important; /* Ajusta el relleno si es necesario */
            }
        }
    </style>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="card-title d-flex align-items-center mb-1" style="font-size: 1.4rem;font-weight: 600;">
                                    <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-file-search-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                    Vista de Impresión: <?php echo htmlspecialchars($_GET['report'] ?? 'Reporte'); ?>
                                </h5>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="../dashboard/dashboard.php">Inicio</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Generar Reporte</li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="btn-group" role="group">
                                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                                    <i class="ri-arrow-left-line"></i> Volver a detalles
                                </a>
                                <button onclick="window.print()" class="btn btn-primary">
                                    <i class="ri-printer-line"></i> Imprimir Reporte
                                </button>
                            </div>
                        </div>
                        <div class="card-body bg-light p-4">
                            <div class="report-container shadow-sm p-5 bg-white mx-auto rounded">
                                <pre><?php echo htmlspecialchars($finalReport); ?></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>