from http.server import ThreadingHTTPServer
from threading import Thread
import unittest
from urllib.error import HTTPError
from urllib.request import Request, urlopen
from unittest.mock import patch

from acr122_bridge import (
    BridgeHandler,
    decode_uid,
    is_target_reader,
    origin_is_allowed,
)


class BridgeTests(unittest.TestCase):
    def test_decodes_supported_uid_lengths(self):
        self.assertEqual(decode_uid([0x04, 0xA1, 0xB2, 0xC3], 0x90, 0x00), "04A1B2C3")
        self.assertEqual(
            decode_uid([0x04, 0xA1, 0xB2, 0xC3, 0xD4, 0xE5, 0xF6], 0x90, 0x00),
            "04A1B2C3D4E5F6",
        )

    def test_rejects_unexpected_status_and_uid_length(self):
        with self.assertRaises(ValueError):
            decode_uid([0x01, 0x02, 0x03, 0x04], 0x63, 0x00)
        with self.assertRaises(ValueError):
            decode_uid([0x01, 0x02, 0x03], 0x90, 0x00)

    def test_matches_acr122_reader_name_case_insensitively(self):
        self.assertTrue(is_target_reader("ACS ACR122U PICC Interface"))
        self.assertFalse(is_target_reader("Generic USB Smart Card Reader"))

    def test_origin_restriction(self):
        self.assertTrue(origin_is_allowed("http://localhost"))
        self.assertTrue(origin_is_allowed("https://tautodokattendance.theautodok.com"))
        self.assertFalse(origin_is_allowed("https://untrusted.example"))
        self.assertTrue(origin_is_allowed(""))

    def test_health_endpoint_only_allows_configured_origin(self):
        server = ThreadingHTTPServer(("127.0.0.1", 0), BridgeHandler)
        thread = Thread(target=server.serve_forever, daemon=True)
        thread.start()
        try:
            url = "http://127.0.0.1:{}/health".format(server.server_port)
            request = Request(url, headers={"Origin": "http://localhost"})
            with patch(
                "acr122_bridge.connected_readers",
                return_value=["ACS ACR122U PICC Interface"],
            ):
                with urlopen(request) as response:
                    self.assertEqual(response.status, 200)
                    self.assertEqual(
                        response.headers["Access-Control-Allow-Origin"],
                        "http://localhost",
                    )

            request = Request(url, headers={"Origin": "https://untrusted.example"})
            with self.assertRaises(HTTPError) as error:
                urlopen(request)
            self.assertEqual(error.exception.code, 403)
        finally:
            server.shutdown()
            server.server_close()
            thread.join(timeout=2)

    def test_private_network_preflight_is_supported(self):
        server = ThreadingHTTPServer(("127.0.0.1", 0), BridgeHandler)
        thread = Thread(target=server.serve_forever, daemon=True)
        thread.start()
        try:
            request = Request(
                "http://127.0.0.1:{}/scan".format(server.server_port),
                method="OPTIONS",
                headers={
                    "Origin": "http://localhost",
                    "Access-Control-Request-Private-Network": "true",
                },
            )
            with urlopen(request) as response:
                self.assertEqual(response.status, 204)
                self.assertEqual(
                    response.headers["Access-Control-Allow-Private-Network"],
                    "true",
                )
        finally:
            server.shutdown()
            server.server_close()
            thread.join(timeout=2)


if __name__ == "__main__":
    unittest.main()
