#!/usr/bin/env python3
"""Expose ACR122 card insertions to the local attendance kiosk over HTTP."""

import json
import logging
import math
import os
import queue
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from urllib.parse import parse_qs, urlsplit


HOST = "127.0.0.1"
PORT = 8765
UID_APDU = [0xFF, 0xCA, 0x00, 0x00, 0x00]
READER_NAME = os.environ.get("NFC_BRIDGE_READER_NAME", "ACR122").strip().lower()
DEFAULT_ORIGINS = {
    "http://localhost",
    "http://127.0.0.1",
    "https://tautodokattendance.theautodok.com",
}
configured_origins = os.environ.get("NFC_BRIDGE_ALLOWED_ORIGINS")
ALLOWED_ORIGINS = {
    origin.strip().rstrip("/")
    for origin in configured_origins.split(",")
    if origin.strip()
} if configured_origins is not None else DEFAULT_ORIGINS
CARD_EVENTS = queue.Queue(maxsize=32)

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s %(levelname)s %(message)s",
)


def is_target_reader(reader_name):
    return READER_NAME in str(reader_name).lower()


def decode_uid(response, sw1, sw2):
    if (sw1, sw2) != (0x90, 0x00):
        raise ValueError("The reader did not return a card UID")
    if len(response) not in (4, 7, 10):
        raise ValueError("The reader returned an unsupported UID length")
    return "".join("{:02X}".format(byte) for byte in response)


def origin_is_allowed(origin):
    return not origin or origin.rstrip("/") in ALLOWED_ORIGINS


def publish_event(event):
    try:
        CARD_EVENTS.put_nowait(event)
    except queue.Full:
        try:
            CARD_EVENTS.get_nowait()
        except queue.Empty:
            pass
        CARD_EVENTS.put_nowait(event)


def create_card_observer():
    try:
        from smartcard.CardMonitoring import CardObserver
    except ImportError as error:
        raise RuntimeError(
            "pyscard is missing. Install the bridge requirements first."
        ) from error

    class ACR122CardObserver(CardObserver):
        def update(self, observable, actions):
            added_cards, _removed_cards = actions
            for card in added_cards:
                reader_name = str(card.reader)
                if not is_target_reader(reader_name):
                    continue

                connection = None
                connected = False
                try:
                    connection = card.createConnection()
                    connection.connect()
                    connected = True
                    response, sw1, sw2 = connection.transmit(UID_APDU)
                    uid = decode_uid(response, sw1, sw2)
                    publish_event({"status": "uid", "uid": uid})
                    logging.info("Read a card UID from %s", reader_name)
                except Exception:
                    logging.exception("Unable to read a UID from %s", reader_name)
                    publish_event({
                        "status": "error",
                        "message": (
                            "The ACR122 detected a card but could not read its UID. "
                            "Remove the card and try again."
                        ),
                    })
                finally:
                    if connection is not None and connected:
                        try:
                            connection.disconnect()
                        except Exception:
                            logging.exception("Unable to close the card connection")

    return ACR122CardObserver()


def connected_readers():
    try:
        from smartcard.System import readers
    except ImportError as error:
        raise RuntimeError(
            "pyscard is missing. Install the bridge requirements first."
        ) from error

    return [str(reader) for reader in readers()]


class BridgeHandler(BaseHTTPRequestHandler):
    server_version = "AutodokNfcBridge/1.0"

    def _send_json(self, status_code, payload):
        body = json.dumps(payload).encode("utf-8")
        self.send_response(status_code)
        origin = self.headers.get("Origin", "")
        if origin and origin_is_allowed(origin):
            self.send_header("Access-Control-Allow-Origin", origin)
            self.send_header("Vary", "Origin")
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Cache-Control", "no-store")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def do_OPTIONS(self):
        origin = self.headers.get("Origin", "")
        if not origin_is_allowed(origin):
            self._send_json(403, {"error": "Origin is not allowed"})
            return
        self.send_response(204)
        self.send_header("Access-Control-Allow-Origin", origin)
        self.send_header("Access-Control-Allow-Methods", "GET, OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type")
        if self.headers.get("Access-Control-Request-Private-Network", "").lower() == "true":
            self.send_header("Access-Control-Allow-Private-Network", "true")
        self.send_header("Vary", "Origin")
        self.end_headers()

    def do_GET(self):
        origin = self.headers.get("Origin", "")
        if not origin_is_allowed(origin):
            self._send_json(403, {"error": "Origin is not allowed"})
            return

        route = urlsplit(self.path)
        if route.path == "/health":
            self._send_health()
        elif route.path == "/scan":
            self._send_scan(parse_qs(route.query))
        else:
            self._send_json(404, {"error": "Not found"})

    def _send_health(self):
        try:
            found_readers = connected_readers()
        except Exception:
            logging.exception("Unable to query PC/SC readers")
            self._send_json(200, {
                "ready": False,
                "readers": [],
                "message": "The PC/SC service is unavailable on this computer.",
            })
            return

        matching_readers = [
            reader for reader in found_readers if is_target_reader(reader)
        ]
        self._send_json(200, {
            "ready": bool(matching_readers),
            "readers": matching_readers,
            "message": (
                "ACR122 reader ready."
                if matching_readers
                else "No ACR122 reader detected by PC/SC."
            ),
        })

    def _send_scan(self, query):
        try:
            timeout = float(query.get("timeout", ["2"])[0])
        except (TypeError, ValueError):
            timeout = 2.0
        if not math.isfinite(timeout):
            timeout = 2.0
        timeout = min(max(timeout, 0.0), 15.0)

        try:
            event = CARD_EVENTS.get(timeout=timeout)
        except queue.Empty:
            self._send_json(200, {"status": "waiting"})
            return
        self._send_json(200, event)

    def log_message(self, format_string, *args):
        logging.info("%s - %s", self.address_string(), format_string % args)


def main():
    try:
        from smartcard.CardMonitoring import CardMonitor
    except ImportError as error:
        raise SystemExit(
            "pyscard is missing. Run the installation steps in README.md."
        ) from error

    server = ThreadingHTTPServer((HOST, PORT), BridgeHandler)
    server.daemon_threads = True
    observer = create_card_observer()
    monitor = CardMonitor()
    monitor.addObserver(observer)
    logging.info("NFC bridge listening on http://%s:%s", HOST, PORT)
    logging.info("Allowed web origins: %s", ", ".join(sorted(ALLOWED_ORIGINS)))
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        logging.info("Stopping NFC bridge")
    finally:
        server.server_close()
        monitor.deleteObserver(observer)


if __name__ == "__main__":
    main()
