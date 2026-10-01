#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")"
PYTHON_BIN="${PYTHON_BIN:-python3}"

if ! command -v "$PYTHON_BIN" >/dev/null 2>&1; then
    echo "Python 3 is required. Install Python 3.9 or newer, then run this script again."
    exit 1
fi

"$PYTHON_BIN" -m venv .venv
.venv/bin/python -m pip install --upgrade pip setuptools wheel
.venv/bin/python -m pip install --no-binary=pyscard -r requirements.txt
.venv/bin/python -c 'from smartcard.System import readers; print("pyscard is ready. PC/SC readers:", [str(reader) for reader in readers()])'

echo
echo "Setup complete. Start the bridge with:"
echo "  .venv/bin/python acr122_bridge.py"
