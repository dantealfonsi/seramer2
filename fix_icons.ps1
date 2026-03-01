# Script to standardize card-header title icons across all views
# Replace old solid purple icon style with new box style (matching awardees/create.php)
# Old: <i class="ri-xxx-xxx me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
# New: <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-xxx-xxx" style="color: #696cff; font-size: 1.5rem;"></i></div>

$viewsPath = 'c:\xampp\htdocs\seramer2\views'

# Get all PHP files
$phpFiles = Get-ChildItem -Path $viewsPath -Recurse -Filter '*.php' | Where-Object { $_.FullName -notlike '*\original\*' }

$oldStyle = 'style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"'

$modifiedCount = 0

foreach ($file in $phpFiles) {
    $content = Get-Content -Path $file.FullName -Raw -Encoding UTF8
    
    if ($content -notmatch 'background: #837aff') {
        continue
    }
    
    $originalContent = $content
    
    # Pattern 1: Simple icon same line - <i class="ri-xxx-yyy me-1" style="..."></i>
    # Replace with boxed version
    $pattern1 = '<i (class="ri-[\w-]+ me-1[^"]*)" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: \.24rem;border-radius: \.7rem;"></i>'
    $replacement1 = '<div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="${1}" style="color: #696cff; font-size: 1.5rem; font-weight: normal !important;"></i></div>'
    # Extract just the icon class part (remove me-1 and other spacing classes from the icon itself)
    
    # Use regex to find and replace the icon patterns
    # Pattern: <i class="ri-SOMETHING me-1 [optional other classes]" style="...old style..."></i>
    $content = [regex]::Replace($content, 
        '<i (class="(ri-[\w-]+)(?:\s+me-1)?(?:\s+[^"]*)?") style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: \.24rem;border-radius: \.7rem;"></i>',
        {
            param($m)
            $iconClass = $m.Groups[2].Value  # just the ri-icon-name
            return '<div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="' + $iconClass + '" style="color: #696cff; font-size: 1.5rem;"></i></div>'
        },
        [System.Text.RegularExpressions.RegexOptions]::Singleline
    )
    
    # Pattern 2: Multi-line variant where the style is on the next line
    # <i class="ri-xxx-yyy me-1"
    #     style="font-size: 2rem;background: #837aff;..."></i>
    $content = [regex]::Replace($content, 
        '<i\s+(class="(ri-[\w-]+)(?:\s+me-1)?(?:\s+[^"]*)?"\s*)\r?\n\s+style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: \.24rem;border-radius: \.7rem;">\s*</i>',
        {
            param($m)
            $iconClass = $m.Groups[2].Value  # just the ri-icon-name
            return '<div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="' + $iconClass + '" style="color: #696cff; font-size: 1.5rem;"></i></div>'
        },
        [System.Text.RegularExpressions.RegexOptions]::Singleline
    )
    
    # Also fix the h5/h4/h6 title styles - replace old title styles with new standard
    # Old: <h5 class="card-title mb-0" style="font-size: 2rem;font-weight: 600;">
    # New: <h5 class="card-title mb-0 d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
    $content = [regex]::Replace($content,
        '(<h[456]\s+class="card-title[^"]*")(\s+style="font-size: 2rem;font-weight: 600;")',
        {
            param($m)
            $tag = $m.Groups[1].Value
            # Add d-flex align-items-center if not already there
            if ($tag -notmatch 'd-flex') {
                $tag = $tag -replace 'class="card-title', 'class="card-title d-flex align-items-center'
            }
            return $tag + ' style="font-size: 1.4rem;font-weight: 600;"'
        }
    )
    
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        $modifiedCount++
        Write-Host "Modified: $($file.FullName)"
    }
}

Write-Host ""
Write-Host "Total files modified: $modifiedCount"
