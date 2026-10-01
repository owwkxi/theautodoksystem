(() => {
  const localHost = ["localhost", "127.0.0.1", "::1"].includes(
    window.location.hostname,
  );
  const bridgeUrl =
    (localHost && window.NFC_BRIDGE_PROXY_URL) || "http://127.0.0.1:8765/";
  const sleep = (milliseconds) =>
    new Promise((resolve) => window.setTimeout(resolve, milliseconds));
  const requestJson = async (path, signal) => {
    const controller = new AbortController();
    const timeoutId = window.setTimeout(() => controller.abort(), 6000);
    const abortRequest = () => controller.abort();
    signal?.addEventListener("abort", abortRequest, { once: true });
    try {
      const response = await fetch(`${bridgeUrl}${path}`, {
        cache: "no-store",
        signal: controller.signal,
      });
      const responseText = await response.text();
      let data;
      try {
        data = JSON.parse(responseText);
      } catch (error) {
        const detail = responseText.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
        throw new Error(
          `Bridge route "${path}" returned non-JSON HTTP ${response.status}: ${detail.slice(0, 180) || error.message}`,
        );
      }
      if (!response.ok) throw new Error(data.error || "Local reader bridge request failed");
      return data;
    } finally {
      window.clearTimeout(timeoutId);
      signal?.removeEventListener("abort", abortRequest);
    }
  };

  window.NfcPcscBridge = {
    start({ onUid, onStatus }) {
      let active = true;
      let scanController = null;
      let resumeVisible;

      const waitUntilVisible = () => {
        if (!document.hidden) return Promise.resolve();
        onStatus("Reader paused while this browser tab is hidden.");
        return new Promise((resolve) => {
          resumeVisible = () => {
            resumeVisible = null;
            resolve();
          };
          document.addEventListener("visibilitychange", resumeVisible, { once: true });
        });
      };

      const pauseWhenHidden = () => {
        if (document.hidden) scanController?.abort();
      };
      document.addEventListener("visibilitychange", pauseWhenHidden);

      const poll = async () => {
        while (active) {
          try {
            await waitUntilVisible();
            if (!active) return;
            scanController = new AbortController();
            const health = await requestJson("health", scanController.signal);
            if (document.hidden) continue;
            if (!health.ready) {
              scanController = null;
              onStatus(health.message || "No ACR122 reader detected.");
              await sleep(1500);
              continue;
            }

            onStatus(`${health.message} Tap a card.`);
            const scan = await requestJson(
              localHost ? "scan&timeout=0.5" : "scan?timeout=0.5",
              scanController.signal,
            );
            scanController = null;
            if (scan.status === "uid") onUid(scan.uid);
            else if (scan.status === "error") onStatus(scan.message);
          } catch (error) {
            scanController = null;
            if (!active) return;
            if (document.hidden) continue;
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
        scanController?.abort();
        document.removeEventListener("visibilitychange", pauseWhenHidden);
        if (resumeVisible) {
          document.removeEventListener("visibilitychange", resumeVisible);
          resumeVisible();
        }
      };
    },
  };
})();
