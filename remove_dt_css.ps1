# remove_dt_css.ps1
# Elimina los <link> de DataTables CSS duplicados de las vistas,
# ya que ahora están centralizados en header.php

$viewsPath = "c:\xampp\htdocs\seramer2\views"
$files = Get-ChildItem -Path $viewsPath -Recurse -Filter "*.php"
$count = 0

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $original = $content

    # Eliminar líneas con datatables.min.css, buttons.bootstrap5.min.css y dani-styles.css
    $patterns = @(
        '<link[^>]*datatables\.min\.css[^>]*/>\s*(\r?\n)?',
        '<link[^>]*buttons\.bootstrap5\.min\.css[^>]*/>\s*(\r?\n)?',
        '<link[^>]*dani-styles\.css[^>]*/>\s*(\r?\n)?'
    )

    foreach ($pattern in $patterns) {
        $content = [regex]::Replace($content, $pattern, '')
    }

    if ($content -ne $original) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        Write-Host "Limpiado: $($file.FullName)"
        $count++
    }
}

Write-Host "`n✅ Total archivos limpiados: $count"
