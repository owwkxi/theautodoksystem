(() => {
  const localHost = ["localhost", "127.0.0.1", "::1"].includes(
    window.location.hostname,
  );
  const bridgeUrl =
    (localHost && window.NFC_BRIDGE_PROXY_URL) || "http://127.0.0.1:8765/";
  const sleep = (milliseconds) =>
    new Promise((resolve) => window.setTimeout(resolve, milliseconds));
  const requestJson = async (path) => {
    const controller = new AbortController();
    const timeoutId = window.setTimeout(() => controller.abort(), 3000);
    try {
      const response = await fetch(`${bridgeUrl}${path}`, {
        cache: "no-store",
        signal: controller.signal,
      });
      const data = await response.json();
      if (!response.ok) throw new Error(data.error || "Local reader bridge request failed");
      return data;
    } finally {
      window.clearTimeout(timeoutId);
    }
  };

  window.NfcPcscBridge = {
    start({ onUid, onStatus }) {
      let active = true;

      const poll = async () => {
        while (active) {
          try {
            const health = await requestJson("health");
            if (!health.ready) {
              onStatus(health.message || "No ACR122 reader detected.");
              await sleep(1500);
              continue;
            }

            onStatus(`${health.message} Tap a card.`);
            const scan = await requestJson(
              localHost ? "scan&timeout=2" : "scan?timeout=2",
            );
            if (scan.status === "uid") onUid(scan.uid);
            else if (scan.status === "error") onStatus(scan.message);
          } catch (error) {
            if (!active) return;
            const detail = error.name === "AbortError"
              ? "The local reader connection timed out. Allow local-network access for this kiosk page."
              : error.message;
            onStatus(
              `Cannot connect to the local ACR122 bridge. Start tools/nfc-bridge/acr122_bridge.py on this computer. ${detail}`,
            );
            return;
          }
        }
      };

      void poll();
      return () => {
        active = false;
      };
    },
  };
})();
