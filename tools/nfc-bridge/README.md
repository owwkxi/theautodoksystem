# ACR122 PC/SC bridge

The ACR122 is a PC/SC/CCID reader; it does not type a UID into the browser. Run this helper on the same computer as the reader and attendance kiosk.

## Install on another computer

Clone or download the repository. Do **not** upload or copy `.venv`: it contains machine- and operating-system-specific installed files and is intentionally ignored by Git. The pinned dependencies and setup scripts below recreate it locally.

Use Python 3.9 or newer.

### macOS

Open Terminal in this folder and run:

```sh
bash setup-macos.command
.venv/bin/python acr122_bridge.py
```

The macOS setup rebuilds `pyscard` from source because some universal macOS wheels fail to load on newer macOS/Python combinations. Install Apple's Command Line Tools first if the build reports that no compiler is available:

```sh
xcode-select --install
```

### Windows

Open PowerShell in this folder and run:

```powershell
.\setup-windows.ps1
.\.venv\Scripts\python.exe acr122_bridge.py
```

If Windows does not detect the ACR122 through PC/SC, install the ACS ACR122U driver for that computer, reconnect the reader, and run the bridge setup/check again.

### Linux

Install PC/SC Lite and Python build prerequisites before creating the environment. On Debian/Ubuntu:

```sh
sudo apt install python3 python3-venv python3-dev build-essential swig libpcsclite-dev pcscd pcsc-tools
sudo systemctl enable --now pcscd
bash setup-linux.sh
.venv/bin/python acr122_bridge.py
```

On other Linux distributions, install the equivalent PC/SC Lite service, headers, Python venv and compiler packages.

## Use

After setup, start the bridge and leave its terminal running. Open NFC Attendance and choose **Connect ACR122 PC/SC**. In Staff Management, open a staff member's card-registration dialog and choose **Connect ACR122** to scan and then save that card UID.

The bridge binds only to `127.0.0.1:8765`. Its default allowed browser origins are `http://localhost`, `http://127.0.0.1`, and `https://tautodokattendance.theautodok.com`. For another kiosk origin, set `NFC_BRIDGE_ALLOWED_ORIGINS` to a comma-separated list of exact origins before starting it. For example:

```sh
NFC_BRIDGE_ALLOWED_ORIGINS="http://localhost,https://attendance.example.com" .venv/bin/python acr122_bridge.py
```

If the PC/SC reader name does not contain `ACR122`, set `NFC_BRIDGE_READER_NAME` to a distinctive part of its name. The helper logs reader errors to its terminal but does not log card UIDs.

The attendance page must be served from an origin listed in `NFC_BRIDGE_ALLOWED_ORIGINS`. The helper reads cards locally on the kiosk computer and does not expose the reader to the application server. It polls for card events in short intervals so switching between Attendance and Staff Management does not leave a long-running scan waiting in another tab. If the browser asks for local-network access, allow it for the kiosk site.
