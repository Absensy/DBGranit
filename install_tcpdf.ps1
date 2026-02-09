# TCPDF Installation Script
Write-Host "Downloading TCPDF..." -ForegroundColor Green

# Create directory
if (-not (Test-Path "includes\tcpdf")) {
    New-Item -ItemType Directory -Path "includes\tcpdf" -Force | Out-Null
}

# Download TCPDF release (latest stable)
$url = "https://github.com/tecnickcom/TCPDF/archive/6.10.1.zip"
$zipFile = "tcpdf.zip"

Write-Host "Downloading archive..." -ForegroundColor Yellow
try {
    Invoke-WebRequest -Uri $url -OutFile $zipFile -UseBasicParsing
    Write-Host "Extracting..." -ForegroundColor Yellow
    
    Expand-Archive -Path $zipFile -DestinationPath "temp" -Force
    
    # Check structure and copy files
    $sourcePath = "temp\TCPDF-6.10.1\tcpdf"
    if (-not (Test-Path $sourcePath)) {
        # Try alternative path
        $sourcePath = "temp\TCPDF-6.10.1"
    }
    
    if (Test-Path $sourcePath) {
        Copy-Item -Path "$sourcePath\*" -Destination "includes\tcpdf" -Recurse -Force
        Write-Host "TCPDF installed successfully in includes\tcpdf" -ForegroundColor Green
        Write-Host "PDF export feature is now ready!" -ForegroundColor Green
    } else {
        Write-Host "Error: Could not find TCPDF files in archive" -ForegroundColor Red
        Write-Host "Archive structure:" -ForegroundColor Yellow
        Get-ChildItem -Path "temp" -Recurse -Directory | Select-Object -First 10 FullName
    }
    
    # Cleanup
    Remove-Item $zipFile -Force -ErrorAction SilentlyContinue
    Remove-Item "temp" -Recurse -Force -ErrorAction SilentlyContinue
    
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
    Write-Host "Please download TCPDF manually from https://github.com/tecnickcom/TCPDF/releases" -ForegroundColor Yellow
    Write-Host "Extract tcpdf folder to includes\tcpdf" -ForegroundColor Yellow
}
