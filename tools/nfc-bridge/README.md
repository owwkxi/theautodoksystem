# ACR122 PC/SC bridge

The ACR122 is a PC/SC/CCID reader; it does not type a UID into the browser. Run this helper on the same computer as the reader and attendance kiosk.

## Install and start

From this directory, create a virtual environment, install the PC/SC binding, and start the bridge:

```sh
python3 -m venv .venv
source .venv/bin/activate
python -m pip install -r requirements.txt
python acr122_bridge.py
```

On macOS and Windows, use the operating system's PC/SC smart-card service. On Linux, install and start PC/SC Lite before starting this helper. If `pyscard` needs to build from source, install the platform's PC/SC development headers and a C/C++ compiler.

If macOS reports `dynamic module does not define module export function (PyInit__scard)` when starting the bridge, rebuild the native binding for the active Python:

```sh
python -m pip install --force-reinstall --no-binary=pyscard -r requirements.txt
```

The bridge binds only to `127.0.0.1:8765`. Its default allowed browser origins are `http://localhost`, `http://127.0.0.1`, and `https://tautodokattendance.theautodok.com`. For another kiosk origin, set `NFC_BRIDGE_ALLOWED_ORIGINS` to a comma-separated list of exact origins before starting it. For example:

```sh
NFC_BRIDGE_ALLOWED_ORIGINS="http://localhost,https://attendance.example.com" python acr122_bridge.py
```

If the PC/SC reader name does not contain `ACR122`, set `NFC_BRIDGE_READER_NAME` to a distinctive part of its name. The helper logs reader errors to its terminal but does not log card UIDs.

## Use

Start the helper, open NFC Attendance, and choose **Connect ACR122 PC/SC**. In Staff Management, open a staff member's card-registration dialog and choose **Connect ACR122** to scan and then save that card UID. Keep the helper running while the kiosk is in use.

The attendance page must be served from an origin listed in `NFC_BRIDGE_ALLOWED_ORIGINS`. The helper does not expose the reader to the application server; it reads the card locally on the kiosk computer.
If the browser asks for local-network access, allow it for the kiosk site so the page can reach the loopback helper.
