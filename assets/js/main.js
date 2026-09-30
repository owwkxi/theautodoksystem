/**
 * Main JavaScript File
 * The Autodok - Automotive Care Services
 */

// Bootstrap can leave a backdrop behind when a modal is opened while the
// page is navigating or another modal is closing. Remove only orphaned
// backdrops so they cannot cover the application indefinitely.
(function () {
  function removeOrphanedModalState() {
    const visibleModal = Array.from(document.querySelectorAll(".modal")).some(
      (modal) =>
        modal.classList.contains("show") ||
        modal.getAttribute("aria-hidden") === "false",
    );

    if (visibleModal) return;

    document.querySelectorAll(".modal-backdrop").forEach((backdrop) => {
      backdrop.remove();
    });
    document.body.classList.remove("modal-open");
    document.body.style.removeProperty("padding-right");
  }

  document.addEventListener("hidden.bs.modal", removeOrphanedModalState);
  window.addEventListener("pageshow", removeOrphanedModalState);
  window.setTimeout(removeOrphanedModalState, 0);
})();

// Auto-dismiss alerts after 5 seconds
document.addEventListener("DOMContentLoaded", function () {
  const alerts = document.querySelectorAll(".alert:not(.alert-permanent)");
  alerts.forEach(function (alert) {
    setTimeout(function () {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });
});

// Form validation
(function () {
  "use strict";
  const forms = document.querySelectorAll(".needs-validation");
  Array.from(forms).forEach(function (form) {
    form.addEventListener(
      "submit",
      function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add("was-validated");
      },
      false,
    );
  });
})();

// Confirm before delete
function confirmDelete(message = "Are you sure you want to delete this item?") {
  return appConfirm(message, {
    title: "Confirm Delete",
    confirmText: "Delete",
    variant: "danger",
  });
}

// Reusable app confirmation modal (promise-based)
function appConfirm(message, options = {}) {
  const {
    title = "Confirm Action",
    confirmText = "OK",
    cancelText = "Cancel",
    variant = "primary",
  } = options;

  const variantClass =
    variant === "danger"
      ? "btn-danger"
      : variant === "warning"
        ? "btn-warning"
        : "btn-primary";

  return new Promise((resolve) => {
    let modalEl = document.getElementById("appConfirmModal");

    if (!modalEl) {
      modalEl = document.createElement("div");
      modalEl.id = "appConfirmModal";
      modalEl.className = "modal fade";
      modalEl.tabIndex = -1;
      modalEl.setAttribute("aria-hidden", "true");
      modalEl.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header">
                            <h5 class="modal-title" id="appConfirmTitle">Confirm Action</h5>
                            <button type="button" class="app-confirm-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0" id="appConfirmMessage"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" id="appConfirmCancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="appConfirmOk">OK</button>
                        </div>
                    </div>
                </div>
            `;
      document.body.appendChild(modalEl);
    }

    const titleEl = document.getElementById("appConfirmTitle");
    const messageEl = document.getElementById("appConfirmMessage");
    const cancelBtn = document.getElementById("appConfirmCancel");
    const okBtn = document.getElementById("appConfirmOk");

    titleEl.textContent = title;
    messageEl.textContent = message;
    cancelBtn.textContent = cancelText;
    okBtn.textContent = confirmText;
    okBtn.className = `btn ${variantClass}`;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    let resolved = false;
    const cleanup = () => {
      okBtn.removeEventListener("click", onConfirm);
      modalEl.removeEventListener("hidden.bs.modal", onHidden);
    };

    const onConfirm = () => {
      if (resolved) return;
      resolved = true;
      cleanup();
      modal.hide();
      resolve(true);
    };

    const onHidden = () => {
      if (resolved) return;
      resolved = true;
      cleanup();
      resolve(false);
    };

    okBtn.addEventListener("click", onConfirm);
    modalEl.addEventListener("hidden.bs.modal", onHidden);
    modal.show();
  });
}

// Show loading spinner
function showLoading(button) {
  const originalText = button.innerHTML;
  button.disabled = true;
  button.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
  return originalText;
}

// Hide loading spinner
function hideLoading(button, originalText) {
  button.disabled = false;
  button.innerHTML = originalText;
}

// Format currency
function formatCurrency(amount) {
  return (
    "₱" +
    parseFloat(amount).toLocaleString("en-PH", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
  );
}

// Calculate total amount
function calculateTotal() {
  const subtotal = parseFloat(document.getElementById("subtotal")?.value || 0);
  const partsBlocks = parseFloat(
    document.getElementById("parts_blocks")?.value || 0,
  );
  const discountType =
    document.getElementById("discount_type")?.value || "none";
  const discountAmount = parseFloat(
    document.getElementById("discount_amount")?.value || 0,
  );

  let total = subtotal + partsBlocks;

  if (discountType === "percentage") {
    total = total - total * (discountAmount / 100);
  } else if (discountType === "fixed") {
    total = total - discountAmount;
  }

  if (document.getElementById("total_amount")) {
    document.getElementById("total_amount").value = total.toFixed(2);
  }

  return total;
}

// AJAX helper function
async function apiRequest(url, method = "GET", data = null, token = null) {
  const options = {
    method: method,
    headers: {
      "Content-Type": "application/json",
    },
  };

  if (token) {
    options.headers["Authorization"] = "Bearer " + token;
  }

  if (data && (method === "POST" || method === "PUT")) {
    options.body = JSON.stringify(data);
  }

  try {
    const response = await fetch(url, options);
    const result = await response.json();
    return result;
  } catch (error) {
    console.error("API Request Error:", error);
    return {
      success: false,
      message: "Network error occurred",
    };
  }
}

// Show toast notification
function showToast(message, type = "info") {
  let toastContainer = document.getElementById("toastContainer");
  if (!toastContainer) {
    const container = document.createElement("div");
    container.id = "toastContainer";
    container.className = "toast-container position-fixed top-0 end-0 p-3";
    container.style.zIndex = "1080";
    document.body.appendChild(container);
    toastContainer = container;
  }

  const toastId = "toast-" + Date.now();
  const bgClass =
    type === "success"
      ? "bg-success"
      : type === "error"
        ? "bg-danger"
        : type === "danger"
          ? "bg-danger"
          : type === "warning"
            ? "bg-warning"
            : "bg-info";

  const toastHTML = `
        <div id="${toastId}" class="toast ${bgClass} text-white border-0 shadow" role="alert" data-bs-delay="3500" aria-live="assertive" aria-atomic="true" style="min-width:280px;max-width:380px;">
            <div class="toast-header ${bgClass} text-white">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="toast-close" data-bs-dismiss="toast" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

  toastContainer.insertAdjacentHTML("beforeend", toastHTML);
  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement);
  toast.show();

  // Guard: remove stray modal backdrops or modal-open body state when no modal is actually visible.
  const cleanupBackdrops = () => {
    const anyModalVisible = Array.from(document.querySelectorAll('.modal')).some(m => {
      return m.classList.contains('show') || (m.style.display && m.style.display !== 'none') || m.offsetParent !== null;
    });
    if (!anyModalVisible) {
      const backdrops = document.querySelectorAll('.modal-backdrop');
      if (backdrops.length > 0) {
        console.warn("cleanupBackdrops: removing", backdrops.length, "stray modal-backdrop(s)");
      }
      backdrops.forEach(el => el.remove());
      document.body.classList.remove('modal-open');
    }
  };

  // Retry the cleanup a few times to handle race conditions where other code briefly re-adds the backdrop.
  setTimeout(cleanupBackdrops, 20);
  (function retryCleanup(retries = 8, delay = 50) {
    if (retries <= 0) return;
    const id = setTimeout(() => {
      cleanupBackdrops();
      // If there are still backdrops and retries remain, schedule another attempt
      const hasBackdrop = document.querySelectorAll('.modal-backdrop').length > 0;
      if (hasBackdrop) {
        retryCleanup(retries - 1, delay);
      }
    }, delay);
  })();

  toastElement.addEventListener('hidden.bs.toast', function () {
    // Ensure cleanup when toast hides as well
    cleanupBackdrops();
    toastElement.remove();
  });
}

// Show server-side flash messages with the same toast style
document.addEventListener("DOMContentLoaded", function () {
  const flash = document.getElementById("flashToastMessage");
  if (!flash) return;

  const message = flash.dataset.message || "";
  const rawType = (flash.dataset.type || "info").toLowerCase();
  const type = rawType === "danger" ? "error" : rawType;
  if (message) {
    showToast(message, type);
  }
});

// Normalize browser alert popups into app toasts for a consistent UX
window.nativeAlert = window.alert;
window.alert = function (message) {
  const text = String(message || "Notification");
  const lower = text.toLowerCase();
  const isError =
    lower.includes("error") ||
    lower.includes("failed") ||
    lower.includes("network") ||
    lower.includes("required") ||
    lower.includes("invalid");
  showToast(text, isError ? "error" : "info");
};

// Print function
function printPage() {
  window.print();
}

// Export to CSV
function exportToCSV(tableId, filename = "export.csv") {
  const table = document.getElementById(tableId);
  if (!table) return;

  let csv = [];
  const rows = table.querySelectorAll("tr");

  for (let i = 0; i < rows.length; i++) {
    const row = [];
    const cols = rows[i].querySelectorAll("td, th");

    for (let j = 0; j < cols.length; j++) {
      let data = cols[j].innerText
        .replace(/(\r\n|\n|\r)/gm, "")
        .replace(/(\s\s)/gm, " ");
      data = data.replace(/"/g, '""');
      row.push('"' + data + '"');
    }

    csv.push(row.join(","));
  }

  const csvString = csv.join("\n");
  const blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");

  if (link.download !== undefined) {
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", filename);
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
}

// Debounce function for search
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Initialize tooltips
document.addEventListener("DOMContentLoaded", function () {
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]'),
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

// Initialize popovers
document.addEventListener("DOMContentLoaded", function () {
  const popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]'),
  );
  popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });
});

// Hamburger menu toggle
document.addEventListener("DOMContentLoaded", function () {
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebar = document.querySelector(".sidebar");
  const sidebarOverlay = document.getElementById("sidebarOverlay");

  if (sidebarToggle && sidebar && sidebarOverlay) {
    // Toggle sidebar on button click
    sidebarToggle.addEventListener("click", function () {
      sidebar.classList.toggle("open");
      sidebarOverlay.classList.toggle("active");
    });

    // Close sidebar when clicking overlay
    sidebarOverlay.addEventListener("click", function () {
      sidebar.classList.remove("open");
      sidebarOverlay.classList.remove("active");
    });

    // Close sidebar when clicking a nav link (mobile)
    const navLinks = sidebar.querySelectorAll(".nav-item");
    navLinks.forEach(function (link) {
      link.addEventListener("click", function () {
        if (window.innerWidth <= 768) {
          sidebar.classList.remove("open");
          sidebarOverlay.classList.remove("active");
        }
      });
    });

    // Close sidebar on window resize if screen becomes larger
    window.addEventListener("resize", function () {
      if (window.innerWidth > 768) {
        sidebar.classList.remove("open");
        sidebarOverlay.classList.remove("active");
      }
    });
  }
});
