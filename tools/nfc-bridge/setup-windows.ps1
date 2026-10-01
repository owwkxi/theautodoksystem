$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

$PythonLauncher = Get-Command py -ErrorAction SilentlyContinue
if ($PythonLauncher) {
    & $PythonLauncher.Source -3 -m venv .venv
    if ($LASTEXITCODE -ne 0) { throw "Could not create the Python virtual environment." }
} else {
    $Python = Get-Command python -ErrorAction SilentlyContinue
    if (-not $Python) {
        throw "Python 3.9 or newer is required. Install Python, then run this script again."
    }
    & $Python.Source -m venv .venv
    if ($LASTEXITCODE -ne 0) { throw "Could not create the Python virtual environment." }
}

$VenvPython = Join-Path $PSScriptRoot ".venv\Scripts\python.exe"
& $VenvPython -m pip install --upgrade pip setuptools wheel
if ($LASTEXITCODE -ne 0) { throw "Could not update pip build tools." }
& $VenvPython -m pip install -r requirements.txt
if ($LASTEXITCODE -ne 0) { throw "Could not install the bridge requirements." }
& $VenvPython -c "from smartcard.System import readers; print('pyscard is ready. PC/SC readers:', [str(reader) for reader in readers()])"
if ($LASTEXITCODE -ne 0) { throw "pyscard could not connect to the Windows smart-card service." }

Write-Host ""
Write-Host "Setup complete. Start the bridge with:"
Write-Host "  .\.venv\Scripts\python.exe acr122_bridge.py"
