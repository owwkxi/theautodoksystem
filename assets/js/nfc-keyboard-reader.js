(() => {
  const normalizeUid = (value) => {
    const candidate = String(value)
      .replace(/^(?:CARD\s*)?UID(?:\s*\([^)]*\))?\s*[:=#-]\s*/i, "")
      .replace(/^0x/i, "")
      .replace(/[\s:-]/g, "")
      .toUpperCase();
    return /^(?:[A-F0-9]{8}(?:[A-F0-9]{6}|[A-F0-9]{12})?|\d{10})$/.test(candidate)
      ? candidate
      : "";
  };

  const findIdentifier = (rawValue, allowStaffId) => {
    const lines = String(rawValue).split(/\r\n|\n|\r|\t/);
    for (const line of lines) {
      const uid = normalizeUid(line.trim());
      if (uid) return uid;
      const candidate = line.trim();
      if (allowStaffId && /^\d{5}$/.test(candidate)) return candidate;
    }
    return "";
  };

  window.NfcKeyboardReader = {
    attach({
      getInput,
      onScan,
      onInput = () => {},
      onInvalid = () => {},
      allowStaffId = false,
    }) {
      let buffer = "";
      let lastKeyAt = 0;
      let resetTimer;
      let inputTimer;
      let bufferInput = null;
      let suppressUntil = 0;
      let scanCompleted = false;
      let lastScannedIdentifier = "";

      const reset = () => {
        buffer = "";
        bufferInput = null;
        scanCompleted = false;
        window.clearTimeout(resetTimer);
      };

      const submitIdentifier = (input, rawValue, event = null) => {
        if (scanCompleted) return false;
        const identifier = findIdentifier(rawValue, allowStaffId);
        if (identifier) {
          scanCompleted = true;
          lastScannedIdentifier = identifier;
          buffer = "";
          window.clearTimeout(resetTimer);
          window.clearTimeout(inputTimer);
          suppressUntil = Date.now() + 2000;
          input.value = identifier;
          input.dispatchEvent(new Event("input", { bubbles: true }));
          if (event) {
            event.preventDefault();
            event.stopImmediatePropagation();
          }
          onScan(identifier, input);
          resetTimer = window.setTimeout(reset, 2000);
          return true;
        }
        if (rawValue) onInvalid(rawValue);
        return false;
      };

      document.addEventListener(
        "keydown",
        (event) => {
          const input = getInput();
          if (!input) {
            reset();
            return;
          }

          if (Date.now() < suppressUntil) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
          }

          if (event.key === "Enter" || event.key === "Tab") {
            if (buffer && bufferInput === input) {
              const wasScannedQuickly = Date.now() - lastKeyAt <= 150;
              if (!submitIdentifier(input, buffer, event) && wasScannedQuickly) {
                event.preventDefault();
                event.stopImmediatePropagation();
              }
              if (!scanCompleted && wasScannedQuickly) {
                buffer += "\n";
                window.clearTimeout(resetTimer);
                resetTimer = window.setTimeout(reset, 3000);
              }
            } else {
              reset();
            }
            return;
          }

          if (
            event.key.length !== 1 ||
            event.ctrlKey ||
            event.metaKey ||
            event.altKey
          ) {
            return;
          }

          const now = Date.now();
          if (bufferInput !== input || now - lastKeyAt > 3000) {
            reset();
            bufferInput = input;
          }
          if (buffer.length >= 2048) reset();
          bufferInput = input;
          buffer += event.key;
          lastKeyAt = now;
          onInput(buffer);

          window.clearTimeout(resetTimer);
          resetTimer = window.setTimeout(() => {
            if (bufferInput === input) submitIdentifier(input, buffer);
            if (!scanCompleted) reset();
          }, 1500);
        },
        true,
      );

      document.addEventListener(
        "input",
        (event) => {
          const input = getInput();
          if (!input || event.target !== input || Date.now() < suppressUntil) return;
          if (event.inputType !== "insertFromPaste") return;

          const rawValue = input.value;
          if (!rawValue || rawValue === lastScannedIdentifier) return;
          onInput(rawValue);
          window.clearTimeout(inputTimer);
          inputTimer = window.setTimeout(() => {
            if (getInput() === input) submitIdentifier(input, input.value);
          }, 1500);
        },
        true,
      );
    },
  };
})();
