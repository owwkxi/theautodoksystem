/**
 * Notifications JavaScript
 * Handles notification UI and interactions
 */

const NotificationManager = {
  apiUrl: `${window.APP_URL || ""}/api/notifications.php`,
  pollInterval: 30000, // Poll every 30 seconds
  pollTimer: null,

  /**
   * Initialize notification system
   */
  init() {
    this.autoCleanOldNotifications(); // Auto-clear old notifications on init
    this.loadUnreadCount();
    this.setupEventListeners();
    this.startPolling();
  },

  /**
   * Auto-clean notifications older than 1 month
   */
  async autoCleanOldNotifications() {
    try {
      await fetch(this.apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify({ action: "auto_clear_old" }),
      });
    } catch (error) {
      console.error("Error auto-cleaning old notifications:", error);
    }
  },

  /**
   * Setup event listeners
   */
  setupEventListeners() {
    // Bell icon click - toggle dropdown
    const bellWrap = document.querySelector(".bell-wrap");
    if (bellWrap) {
      bellWrap.addEventListener("click", (e) => {
        e.stopPropagation();
        this.toggleNotificationDropdown();
      });
    }

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      const dropdown = document.getElementById("notificationDropdown");
      if (dropdown && !dropdown.contains(e.target)) {
        dropdown.classList.remove("show");
      }
    });
  },

  /**
   * Load unread notification count
   */
  async loadUnreadCount() {
    try {
      const response = await fetch(`${this.apiUrl}?action=unread_count`, {
        credentials: "same-origin",
      });
      if (!response.ok) {
        return;
      }
      const data = await response.json();

      if (data && data.success) {
        this.updateBadge(data.count);
      }
    } catch (error) {
      console.error("Error loading unread count:", error);
    }
  },

  /**
   * Update notification badge
   */
  updateBadge(count) {
    const bellDot = document.querySelector(".bell-dot");
    if (bellDot) {
      if (count > 0) {
        bellDot.style.display = "flex";
        bellDot.setAttribute("data-count", String(count));
        bellDot.textContent = count > 99 ? "99+" : String(count);
      } else {
        bellDot.style.display = "none";
        bellDot.removeAttribute("data-count");
        bellDot.textContent = "";
      }
    }
  },

  /**
   * Show latest action result in toast when available
   */
  notifyResult(message, type = "info") {
    if (typeof showToast === "function") {
      showToast(message, type);
    }
  },

  /**
   * Immediately reflect a cleared/marked state in the current UI
   */
  applyAllClearedUI() {
    this.updateBadge(0);
    const listContainer = document.getElementById("notificationList");
    if (listContainer) {
      listContainer.innerHTML =
        '<div class="notification-empty"><i class="bi bi-bell-slash"></i> No new notifications</div>';
    }
  },

  /**
   * Toggle notification dropdown
   */
  async toggleNotificationDropdown() {
    let dropdown = document.getElementById("notificationDropdown");

    if (!dropdown) {
      dropdown = this.createDropdown();
      document.body.appendChild(dropdown);
    }

    if (dropdown.classList.contains("show")) {
      dropdown.classList.remove("show");
    } else {
      await this.loadNotifications();
      dropdown.classList.add("show");
      this.positionDropdown(dropdown);
    }
  },

  /**
   * Create notification dropdown
   */
  createDropdown() {
    const dropdown = document.createElement("div");
    dropdown.id = "notificationDropdown";
    dropdown.className = "notification-dropdown";
    dropdown.innerHTML = `
            <div class="notification-header">
                <h6>Notifications</h6>
                <div class="notification-header-actions">
                    <button class="btn-clear-all" onclick="NotificationManager.clearAllNotifications()" title="Clear all">
                        <i class="bi bi-trash"></i> Clear
                    </button>
                    <button class="btn-mark-all-read" onclick="NotificationManager.markAllAsRead()" title="Mark all as read">
                        <i class="bi bi-check-all"></i> Mark all read
                    </button>
                </div>
            </div>
            <div class="notification-list" id="notificationList">
                <div class="notification-loading">
                    <i class="bi bi-hourglass-split"></i> Loading...
                </div>
            </div>
            <div class="notification-footer">
                <button class="btn-view-more" onclick="NotificationManager.expandDropdown()">
                    <i class="bi bi-chevron-down"></i> View More
                </button>
            </div>
        `;
    return dropdown;
  },

  /**
   * Position dropdown near bell icon
   */
  positionDropdown(dropdown) {
    const bellWrap = document.querySelector(".bell-wrap");
    if (bellWrap) {
      const rect = bellWrap.getBoundingClientRect();
      const dropdownWidth = 360;

      // Position below the bell, aligned to its right edge
      let top = rect.bottom + 8;
      let left = rect.right - dropdownWidth;

      // Don't go off left edge
      if (left < 12) left = 12;
      // Don't go off right edge
      if (left + dropdownWidth > window.innerWidth - 12) {
        left = window.innerWidth - dropdownWidth - 12;
      }
      // Don't go off bottom
      const maxH = window.innerHeight - top - 20;
      dropdown.style.maxHeight = Math.min(480, maxH) + "px";

      dropdown.style.top = top + "px";
      dropdown.style.left = left + "px";
      dropdown.style.right = "auto";
    }
  },

  /**
   * Load notifications
   */
  async loadNotifications() {
    const listContainer = document.getElementById("notificationList");
    if (!listContainer) return;

    try {
      const response = await fetch(`${this.apiUrl}?action=all&limit=10`, {
        credentials: "same-origin",
      });
      const raw = await response.text();
      let data = null;
      try {
        data = JSON.parse(raw);
      } catch (_e) {
        data = null;
      }

      if (response.ok && data && data.success) {
        this.renderNotifications(data.notifications);
      } else {
        listContainer.innerHTML =
          '<div class="notification-empty">Failed to load notifications</div>';
      }
    } catch (error) {
      console.error("Error loading notifications:", error);
      listContainer.innerHTML =
        '<div class="notification-empty">Error loading notifications</div>';
    }
  },

  /**
   * Render notifications
   */
  renderNotifications(notifications) {
    const listContainer = document.getElementById("notificationList");
    if (!listContainer) return;

    if (notifications.length === 0) {
      listContainer.innerHTML =
        '<div class="notification-empty"><i class="bi bi-bell-slash"></i> No new notifications</div>';
      return;
    }

    listContainer.innerHTML = notifications
      .map((notif) => {
        const clickHandler = notif.is_dynamic
          ? ""
          : `onclick="NotificationManager.markAsRead('${notif.id}')"`;
        return `
                <div class="notification-item ${notif.is_read ? "read" : "unread"} ${notif.is_dynamic ? "dynamic" : ""}" data-id="${notif.id}" ${clickHandler} style="cursor:${notif.is_dynamic ? "default" : "pointer"}">
                    <div class="notification-icon ${notif.type}">
                        ${this.getNotificationIcon(notif.type)}
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${this.escapeHtml(notif.title)}</div>
                        <div class="notification-message">${this.escapeHtml(notif.message)}</div>
                        <div class="notification-time">${this.formatTime(notif.created_at)}</div>
                    </div>
                </div>
            `;
      })
      .join("");
  },

  /**
   * Get icon for notification type
   */
  getNotificationIcon(type) {
    const icons = {
      account_update: '<i class="bi bi-person-circle"></i>',
      cash_advance: '<i class="bi bi-cash-coin"></i>',
      job_assigned: '<i class="bi bi-clipboard-check"></i>',
      job_status: '<i class="bi bi-arrow-repeat"></i>',
      payment: '<i class="bi bi-credit-card"></i>',
      staff_update: '<i class="bi bi-people"></i>',
      system: '<i class="bi bi-info-circle"></i>',
    };
    return icons[type] || '<i class="bi bi-bell"></i>';
  },

  /**
   * Mark notification as read — removes it from the list immediately
   */
  async markAsRead(notificationId) {
    try {
      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify({
          action: "mark_read",
          notification_id: notificationId,
        }),
      });
      const data = await response.json();
      if (data.success) {
        this.loadUnreadCount();
        this.loadNotifications();
      }
    } catch (error) {
      console.error("Error marking notification as read:", error);
    }
  },

  /**
   * Check if list is empty and show empty state
   */
  checkEmpty() {
    const list = document.getElementById("notificationList");
    if (list && list.querySelectorAll(".notification-item").length === 0) {
      list.innerHTML =
        '<div class="notification-empty"><i class="bi bi-bell-slash"></i> No new notifications</div>';
    }
  },

  /**
   * Mark all notifications as read
   */
  async markAllAsRead() {
    try {
      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify({ action: "mark_all_read" }),
      });

      let data = null;
      try {
        data = await response.json();
      } catch (_error) {
        data = null;
      }

      if (data && data.success) {
        this.updateBadge(0);
        await this.loadNotifications();
      } else {
        this.notifyResult(data?.message || "Failed to mark all notifications as read", "error");
        this.loadUnreadCount();
        this.loadNotifications();
      }
    } catch (error) {
      console.error("Error marking all as read:", error);
      this.notifyResult("Error marking all notifications as read", "error");
      this.loadUnreadCount();
      this.loadNotifications();
    }
  },

  /**
   * Clear all notifications
   */
  async clearAllNotifications() {
    const dropdown = document.getElementById("notificationDropdown");
    if (dropdown) {
      dropdown.classList.remove("show");
    }

    const confirmed = await appConfirm(
      "Are you sure you want to clear all notifications?",
      {
        title: "Clear Notifications",
        confirmText: "Clear All",
        variant: "danger",
      },
    );

    if (!confirmed) {
      return;
    }

    try {
      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify({ action: "delete_all" }),
      });

      let data = null;
      try {
        data = await response.json();
      } catch (_error) {
        data = null;
      }

      if (data && data.success) {
        this.applyAllClearedUI();
        this.updateBadge(0);
      } else {
        this.notifyResult(data?.message || "Failed to clear notifications", "error");
        this.loadUnreadCount();
        this.loadNotifications();
      }
    } catch (error) {
      console.error("Error clearing notifications:", error);
      this.notifyResult("Error clearing notifications", "error");
      this.loadUnreadCount();
      this.loadNotifications();
    }
  },

  /**
   * Expand dropdown to show more notifications
   */
  expandDropdown() {
    const dropdown = document.getElementById("notificationDropdown");
    if (!dropdown) return;

    // Toggle expanded state
    if (dropdown.classList.contains("expanded")) {
      dropdown.classList.remove("expanded");
      dropdown.style.maxHeight = "480px";
      // Keep width the same - only vertical resize

      // Change button text
      const btn = dropdown.querySelector(".btn-view-more");
      if (btn) {
        btn.innerHTML = '<i class="bi bi-chevron-down"></i> View More';
      }

      // Load limited notifications
      this.loadNotifications();
    } else {
      dropdown.classList.add("expanded");
      dropdown.style.maxHeight = "700px"; // Expand vertically only
      // Width stays at 360px

      // Change button text
      const btn = dropdown.querySelector(".btn-view-more");
      if (btn) {
        btn.innerHTML = '<i class="bi bi-chevron-up"></i> View Less';
      }

      // Load more notifications
      this.loadAllNotifications();
    }

    // Reposition after resize
    this.positionDropdown(dropdown);
  },

  /**
   * Load all notifications (expanded view)
   */
  async loadAllNotifications() {
    const listContainer = document.getElementById("notificationList");
    if (!listContainer) return;

    try {
      const response = await fetch(`${this.apiUrl}?action=all&limit=50`, {
        credentials: "same-origin",
      });
      const raw = await response.text();
      let data = null;
      try {
        data = JSON.parse(raw);
      } catch (_e) {
        data = null;
      }

      if (response.ok && data && data.success) {
        this.renderNotifications(data.notifications);
      } else {
        listContainer.innerHTML =
          '<div class="notification-empty">Failed to load notifications</div>';
      }
    } catch (error) {
      console.error("Error loading all notifications:", error);
      listContainer.innerHTML =
        '<div class="notification-empty">Error loading notifications</div>';
    }
  },

  /**
   * Start polling for new notifications
   */
  startPolling() {
    this.pollTimer = setInterval(() => {
      this.loadUnreadCount();
    }, this.pollInterval);
  },

  /**
   * Stop polling
   */
  stopPolling() {
    if (this.pollTimer) {
      clearInterval(this.pollTimer);
      this.pollTimer = null;
    }
  },

  /**
   * Format time ago
   */
  formatTime(timestamp) {
    const now = new Date();
    const time = new Date(timestamp);
    const diff = Math.floor((now - time) / 1000); // seconds

    if (diff < 60) return "Just now";
    if (diff < 3600) return Math.floor(diff / 60) + " minutes ago";
    if (diff < 86400) return Math.floor(diff / 3600) + " hours ago";
    if (diff < 604800) return Math.floor(diff / 86400) + " days ago";

    return time.toLocaleDateString();
  },

  /**
   * Escape HTML
   */
  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  },
};

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  NotificationManager.init();
});

// Stop polling when page is hidden
document.addEventListener("visibilitychange", () => {
  if (document.hidden) {
    NotificationManager.stopPolling();
  } else {
    NotificationManager.startPolling();
    NotificationManager.loadUnreadCount();
  }
});
