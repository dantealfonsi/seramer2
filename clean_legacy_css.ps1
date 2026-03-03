$files = @(
    'c:\xampp\htdocs\seramer2\views\billing\fines.php',
    'c:\xampp\htdocs\seramer2\views\billing\fine_details.php',
    'c:\xampp\htdocs\seramer2\views\billing\payments.php',
    'c:\xampp\htdocs\seramer2\views\billing\receivable.php',
    'c:\xampp\htdocs\seramer2\views\billing\delinquency.php',
    'c:\xampp\htdocs\seramer2\views\reports\editor.php'
)

foreach ($f in $files) {
    if (Test-Path $f) {
        $content = Get-Content $f -Raw -Encoding UTF8
        # Remove .card-title-premium CSS block
        $content = $content -replace '(?s)\s*\.card-title-premium \{[^}]+\}', ''
        # Remove .icon-premium CSS block
        $content = $content -replace '(?s)\s*\.icon-premium \{[^}]+\}', ''
        [System.IO.File]::WriteAllText($f, $content, [System.Text.Encoding]::UTF8)
        Write-Host "Cleaned: $f"
    } else {
        Write-Host "Not found: $f"
    }
}
Write-Host "Done!"
