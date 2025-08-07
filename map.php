<?php
function mapearComponentes($rutaBase) {
    $elementos = scandir($rutaBase);

    foreach ($elementos as $elemento) {
        if ($elemento === '.' || $elemento === '..') continue;

        $rutaCompleta = $rutaBase . DIRECTORY_SEPARATOR . $elemento;

        if (is_dir($rutaCompleta)) {
            echo "📦 Componente: " . $elemento . "\n";
            mapearComponentes($rutaCompleta); // Recursivo
        } elseif (pathinfo($elemento, PATHINFO_EXTENSION) === 'php') {
            echo "  └── 📄 Archivo: " . $elemento . "\n";
        }
    }
}

// 🔍 Usa la carpeta donde está este archivo como raíz
$rutaRaiz = __DIR__;
mapearComponentes($rutaRaiz);
