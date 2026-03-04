# Start PHP dev server on port 8000 (works even when php is not in PATH)
$phpPaths = @(
    (Get-Command php -ErrorAction SilentlyContinue)?.Source,
    "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe",
    "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe",
    "C:\Program Files\PHP\php.exe"
)
$php = $phpPaths | Where-Object { $_ -and (Test-Path $_) } | Select-Object -First 1
if (-not $php) {
    Write-Host "PHP not found. Install with: winget install PHP.PHP.8.3" -ForegroundColor Red
    exit 1
}
Set-Location $PSScriptRoot
& $php -S localhost:8000 router.php
