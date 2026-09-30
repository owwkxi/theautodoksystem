/**
 * Staff Management JavaScript
 * Handles all client-side operations for staff management
 */

function renderImagePreview(container, src, alt = 'Preview') {
  if (!container) return;
  if (!src) {
    container.innerHTML = "";
    return;
  }

  container.innerHTML = `
    <div class="mt-2">
      <img src="${src}" alt="${alt}" class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">
    </div>
  `;
}

function viewAttendanceNotes(button) {
  const morningNote = button.dataset.morningNote || "";
  const afternoonNote = button.dataset.afternoonNote || "";
  document.getElementById("viewMorningAttendanceNote").textContent = morningNote || "—";
  document.getElementById("viewAfternoonAttendanceNote").textContent = afternoonNote || "—";
  document.getElementById("viewMorningAttendanceNoteGroup").style.display =
    morningNote ? "" : "none";
  document.getElementById("viewAfternoonAttendanceNoteGroup").style.display =
    afternoonNote ? "" : "none";
  bootstrap.Modal.getOrCreateInstance(
    document.getElementById("viewAttendanceNotesModal"),
  ).show();
}

function openAttendanceOtherDetails(staffId, period) {
  const periodLabel = period === "morning" ? "Morning" : "Afternoon";
  const morningLabel = document.querySelector('label[for="attendanceOtherMorningNotes"]');
  const morningNotes = document.getElementById("attendanceOtherMorningNotes");
  const afternoonLabel = document.querySelector('label[for="attendanceOtherAfternoonNotes"]');
  const afternoonNotes = document.getElementById("attendanceOtherAfternoonNotes");
  const selectedLabel = period === "morning" ? morningLabel : afternoonLabel;
  const selectedNotes = period === "morning" ? morningNotes : afternoonNotes;
  const otherLabel = period === "morning" ? afternoonLabel : morningLabel;
  const otherNotes = period === "morning" ? afternoonNotes : morningNotes;

  document.getElementById("attendanceOtherStaffId").value = staffId;
  document.getElementById("attendanceOtherPeriod").value = period;
  selectedLabel.textContent = `${periodLabel} details`;
  selectedNotes.value =
    document.querySelector(`.attendance-notes[name="notes[${period}][${staffId}]"]`)?.value || "";
  selectedLabel.style.display = "";
  selectedNotes.style.display = "";
  otherLabel.style.display = "none";
  otherNotes.style.display = "none";
  bootstrap.Modal.getOrCreateInstance(
    document.getElementById("attendanceOtherModal"),
  ).show();
}

function clearAttendancePeriod(button, markCleared = false) {
  const staffId = button.dataset.staffId;
  const period = button.dataset.period;
  const clearButton =
    button.classList?.contains("attendance-clear-button")
      ? button
      : document.querySelector(
          `.attendance-clear-button[data-period="${period}"][data-staff-id="${staffId}"]`,
        );
  const status = document.querySelector(
    `.attendance-status[name="status[${period}][${staffId}]"]`,
  );
  const time = document.querySelector(
    `.attendance-time[name="time[${period}][${staffId}]"]`,
  );
  const timeOut = document.querySelector(
    `.attendance-time-out[name="time_out[${period}][${staffId}]"]`,
  );
  const notes = document.querySelector(
    `.attendance-notes[name="notes[${period}][${staffId}]"]`,
  );
  const otherButton = document.querySelector(
    `.attendance-other-button[data-period="${period}"][data-staff-id="${staffId}"]`,
  );
  if (status) status.value = "";
  if (time) time.value = "";
  if (timeOut) timeOut.value = "";
  if (notes) notes.value = "";
  if (otherButton) otherButton.style.display = "none";
  if (clearButton) {
    clearButton.classList.toggle("is-cleared", markCleared);
  }
  if (status) updateAttendanceTimeVisibility(status);
}

function updateAttendanceTimeVisibility(status) {
  const time = document.querySelector(
    `.attendance-time[name="time[${status.dataset.period}][${status.dataset.staffId}]"]`,
  );
  const timeOut = document.querySelector(
    `.attendance-time-out[name="time_out[${status.dataset.period}][${status.dataset.staffId}]"]`,
  );
  if (!time && !timeOut) return;
  const hideTime = ["absent", "on_leave", "other"].includes(status.value);
  const timeGroups = [time, timeOut]
    .map((field) => field?.closest(".attendance-time-group"))
    .filter(Boolean);
  if (time) {
    time.style.display = hideTime ? "none" : "";
    if (hideTime) time.value = "";
  }
  if (timeOut) {
    timeOut.style.display = hideTime ? "none" : "";
    if (hideTime) timeOut.value = "";
  }
  [time, timeOut].forEach((field) => {
    const picker = field?.closest(".attendance-time-group")?.querySelector(".attendance-time-picker");
    if (picker) picker.style.display = hideTime ? "none" : "";
  });
  timeGroups.forEach((group) => {
    group.style.display = hideTime ? "none" : "";
  });
}

document.addEventListener("click", (event) => {
  const clearButton = event.target.closest(".attendance-clear-button");
  if (clearButton) {
    event.preventDefault();
    event.stopPropagation();
    document
      .querySelectorAll(".attendance-clear-button")
      .forEach((button) => button.classList.remove("is-cleared"));
    clearAttendancePeriod(clearButton);
    clearButton.blur();
    return;
  }

  const otherButton = event.target.closest(".attendance-other-button");
  if (otherButton) {
    event.preventDefault();
    event.stopPropagation();
    openAttendanceOtherDetails(otherButton.dataset.staffId, otherButton.dataset.period);
  }
});

async function loadAttendanceDate(date) {
  const response = await fetch(
    `${APP_URL}/api/staff.php?attendance_date=${encodeURIComponent(date)}`,
  );
  const data = await response.json();
  if (!data.success) throw new Error(data.message || "Unable to load attendance");

  document.querySelectorAll(".attendance-status").forEach((select) => {
    clearAttendancePeriod({
      dataset: { staffId: select.dataset.staffId, period: select.dataset.period },
    }, false);
  });

  (data.data || []).forEach((record) => {
    const staffId = String(record.staff_id);
    const hour = Number.parseInt(String(record.time_in || "").slice(0, 2), 10);
    const period = hour < 12 ? "morning" : "afternoon";
    const status = document.querySelector(
      `.attendance-status[name="status[${period}][${staffId}]"]`,
    );
    const time = document.querySelector(
      `.attendance-time[name="time[${period}][${staffId}]"]`,
    );
    const timeOut = document.querySelector(
      `.attendance-time-out[name="time_out[${period}][${staffId}]"]`,
    );
    const notes = document.querySelector(
      `.attendance-notes[name="notes[${period}][${staffId}]"]`,
    );
    const otherButton = document.querySelector(
      `.attendance-other-button[data-period="${period}"][data-staff-id="${staffId}"]`,
    );
    if (status) status.value = record.status || "";
    if (time) time.value = String(record.time_in || "").slice(0, 5);
    if (timeOut) timeOut.value = String(record.time_out || "").slice(0, 5);
    if (notes) notes.value = record.notes || "";
    if (otherButton) otherButton.style.display = record.status === "other" ? "" : "none";
    if (status) updateAttendanceTimeVisibility(status);
  });
}

document.getElementById("attendanceDate")?.addEventListener("change", async (event) => {
  try {
    await loadAttendanceDate(event.target.value);
  } catch (error) {
    console.error("Attendance date error:", error);
    showToast(error.message || "Unable to load attendance for this date.", "error");
  }
});

document.getElementById("attendanceModal")?.addEventListener("shown.bs.modal", async () => {
  const dateField = document.getElementById("attendanceDate");
  if (!dateField?.value) return;

  try {
    await loadAttendanceDate(dateField.value);
  } catch (error) {
    console.error("Attendance refresh error:", error);
    showToast(error.message || "Unable to refresh attendance records.", "error");
  }
});

// Enable the details field only when an operator selects Other.
document.querySelectorAll(".attendance-status").forEach((select) => {
  select.addEventListener("change", function () {
    const notes = document.querySelector(
      `.attendance-notes[name="notes[${this.dataset.period}][${this.dataset.staffId}]"]`,
    );
    const detailsButton = document.querySelector(
      `.attendance-other-button[data-period="${this.dataset.period}"][data-staff-id="${this.dataset.staffId}"]`,
    );
    if (!notes) return;
    const clearButton = document.querySelector(
      `.attendance-clear-button[data-period="${this.dataset.period}"][data-staff-id="${this.dataset.staffId}"]`,
    );
    if (clearButton) clearButton.classList.remove("is-cleared");
    updateAttendanceTimeVisibility(this);
    if (detailsButton) detailsButton.style.display = this.value === "other" ? "" : "none";
    if (this.value === "other") {
      openAttendanceOtherDetails(this.dataset.staffId, this.dataset.period);
    } else {
      notes.value = "";
    }
  });
});

document.querySelectorAll(".attendance-status").forEach(updateAttendanceTimeVisibility);

document.addEventListener("click", (event) => {
  const button = event.target.closest(".attendance-time-picker");
  if (!button) return;
  event.preventDefault();
  event.stopPropagation();
  const input = document.getElementById(button.dataset.target);
  if (!input) return;
  if (typeof input.showPicker === "function") {
    input.showPicker();
  } else {
    input.focus();
  }
});

document.getElementById("saveAttendanceOtherNotes")?.addEventListener("click", () => {
  const staffId = document.getElementById("attendanceOtherStaffId").value;
  const period = document.getElementById("attendanceOtherPeriod").value;
  const notes = document.querySelector(
    `.attendance-notes[name="notes[${period}][${staffId}]"]`,
  );
  const field = document.getElementById(
    `attendanceOther${period === "morning" ? "Morning" : "Afternoon"}Notes`,
  );
  if (notes && field) notes.value = field.value.trim();
  bootstrap.Modal.getOrCreateInstance(document.getElementById("attendanceOtherModal")).hide();
});

document
  .getElementById("attendanceForm")
  ?.addEventListener("submit", async function (e) {
      e.preventDefault();
      if (this.dataset.submitting === "true") return;
      const missingOtherDetails = [...this.querySelectorAll(".attendance-status")].some(
        (select) => {
          if (select.value !== "other") return false;
          const notes = this.querySelector(
            `.attendance-notes[name="notes[${select.dataset.period}][${select.dataset.staffId}]"]`,
          );
          return !notes || !notes.value.trim();
        },
      );
      if (missingOtherDetails) {
          showToast("Please enter details for every Other status.", "error");
          return;
      }

      const submitBtn = this.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.innerHTML;
      this.dataset.submitting = "true";
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
      const formData = new FormData(this);
      formData.append("action", "save_attendance");

      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 15000);
      try {
        const response = await fetch(`${APP_URL}/api/staff.php`, {
          method: "POST",
          body: formData,
          credentials: "same-origin",
          signal: controller.signal,
        });
        const responseText = await response.text();
        let data;
        try {
          data = JSON.parse(responseText);
        } catch (_parseError) {
          throw new Error(`Attendance request failed (${response.status}).`);
        }
        if (!response.ok) throw new Error(data.message || "Unable to save attendance");
        if (!data.success) throw new Error(data.message || "Unable to save attendance");
        showToast(data.message, "success");
        bootstrap.Modal.getOrCreateInstance(
          document.getElementById("attendanceModal"),
        ).hide();
        setTimeout(() => window.location.reload(), 700);
      } catch (error) {
        console.error("Attendance error:", error);
        showToast(
          error.name === "AbortError"
            ? "Saving attendance timed out. Please try again."
            : error.message || "Unable to save attendance.",
          "error",
        );
      } finally {
        clearTimeout(timeout);
        delete this.dataset.submitting;
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
      }
  });

document.getElementById("attendanceModal")?.addEventListener("hidden.bs.modal", () => {
  const form = document.getElementById("attendanceForm");
  const submitBtn = form?.querySelector('button[type="submit"]');
  if (!form || !submitBtn) return;
  delete form.dataset.submitting;
  submitBtn.disabled = false;
  submitBtn.innerHTML = '<i class="bi bi-save"></i> Save Attendance';
});

// Image preview for add form
document
  .getElementById("add_profile_image")
  ?.addEventListener("change", function (e) {
    const file = e.target.files[0];
    const preview = document.getElementById("add_image_preview");

    if (file) {
      // Validate file size (5MB)
      if (file.size > 5242880) {
        showToast("File size must not exceed 5MB", "error");
        e.target.value = "";
        preview.innerHTML = "";
        return;
      }

      // Validate file type
      const allowedTypes = ["image/jpeg", "image/jpg", "image/png"];
      if (!allowedTypes.includes(file.type)) {
        showToast("Only JPG, JPEG, and PNG files are allowed", "error");
        e.target.value = "";
        preview.innerHTML = "";
        return;
      }

      const reader = new FileReader();
      reader.onload = function (e) {
        renderImagePreview(preview, e.target.result, 'New Preview');
      };
      reader.readAsDataURL(file);
    } else {
      renderImagePreview(preview, '', 'New Preview');
    }
  });

// Image preview for edit form
document
  .getElementById("edit_profile_image")
  ?.addEventListener("change", function (e) {
    const file = e.target.files[0];
    const preview = document.getElementById("edit_image_preview");

    if (file) {
      // Validate file size (5MB)
      if (file.size > 5242880) {
        showToast("File size must not exceed 5MB", "error");
        e.target.value = "";
        preview.innerHTML = "";
        return;
      }

      // Validate file type
      const allowedTypes = ["image/jpeg", "image/jpg", "image/png"];
      if (!allowedTypes.includes(file.type)) {
        showToast("Only JPG, JPEG, and PNG files are allowed", "error");
        e.target.value = "";
        preview.innerHTML = "";
        return;
      }

      const reader = new FileReader();
      reader.onload = function (e) {
        renderImagePreview(preview, e.target.result, 'New Preview');
      };
      reader.readAsDataURL(file);
    } else {
      renderImagePreview(preview, '', 'New Preview');
    }
  });

// Add Staff Form Submission
document
  .getElementById("addStaffForm")
  ?.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Validate password match
    const password = document.getElementById("add_password").value;
    const confirmPassword = document.getElementById(
      "add_confirm_password",
    ).value;

    if (password !== confirmPassword) {
      showToast("Passwords do not match", "error");
      return;
    }

    // Validate password strength
    if (password.length < 6) {
      showToast("Password must be at least 6 characters", "error");
      return;
    }

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    try {
      const response = await fetch(`${APP_URL}/api/staff.php`, {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        showToast(data.message, "success");

        // Close modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addStaffModal"),
        );
        modal.hide();

        // Reset form
        this.reset();
        document.getElementById("add_image_preview").innerHTML = "";

        // Reload page after short delay
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        showToast(data.message, "error");
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
      }
    } catch (error) {
      console.error("Error:", error);
      showToast("An error occurred. Please try again.", "error");
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnText;
    }
  });

// Edit Staff Function
async function editStaff(id) {
  try {
    const response = await fetch(`${APP_URL}/api/staff.php?id=${id}`);
    const data = await response.json();

    if (data.success) {
      const staff = data.data;

      // Populate form fields
      document.getElementById("edit_staff_id").value = staff.id;
      document.getElementById("edit_full_name").value = staff.full_name;
      document.getElementById("edit_staff_login_id").value = staff.staff_id || "";
      document.getElementById("edit_email").value = staff.email;
      document.getElementById("edit_contact_number").value =
        staff.contact_number;
      document.getElementById("edit_address").value = staff.address || "";
      document.getElementById("edit_role").value = staff.role;
      document.getElementById("edit_status").value = staff.status;
      document.getElementById("editStaffForm").dataset.expectedUpdatedAt =
        staff.updated_at || "";

      // Clear password fields
      document.getElementById("edit_password").value = "";
      document.getElementById("edit_confirm_password").value = "";

      // Show current profile image if exists
      const preview = document.getElementById("edit_image_preview");
      if (staff.profile_image) {
        renderImagePreview(preview, `${APP_URL}/uploads/${staff.profile_image}`, 'Current Profile');
      } else {
        renderImagePreview(preview, '', 'Current Profile');
      }

      // Show modal
      const modal = new bootstrap.Modal(
        document.getElementById("editStaffModal"),
      );
      modal.show();
    } else {
      showToast(data.message, "error");
    }
  } catch (error) {
    console.error("Error:", error);
    showToast("Failed to load staff data", "error");
  }
}

// Edit Staff Form Submission
document
  .getElementById("editStaffForm")
  ?.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Validate password match if password is provided
    const password = document.getElementById("edit_password").value;
    const confirmPassword = document.getElementById(
      "edit_confirm_password",
    ).value;

    if (password || confirmPassword) {
      if (password !== confirmPassword) {
        showToast("Passwords do not match", "error");
        return;
      }

      if (password.length < 6) {
        showToast("Password must be at least 6 characters", "error");
        return;
      }
    }

    const formData = new FormData(this);
    const expectedUpdatedAt = this.dataset.expectedUpdatedAt || "";
    if (expectedUpdatedAt) {
      formData.append("expected_updated_at", expectedUpdatedAt);
    }
    formData.append("_method", "PUT");
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

    try {
      const response = await fetch(`${APP_URL}/api/staff.php`, {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        showToast(data.message, "success");

        // Close modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("editStaffModal"),
        );
        modal.hide();

        // Reload page after short delay
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        showToast(data.message, "error");
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
      }
    } catch (error) {
      console.error("Error:", error);
      showToast("An error occurred. Please try again.", "error");
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnText;
    }
  });

// View Staff Function
async function viewStaff(id) {
  try {
    const response = await fetch(`${APP_URL}/api/staff.php?id=${id}`);
    const data = await response.json();

    if (data.success) {
      const staff = data.data;

      // Build profile image HTML
      let profileImageHtml = "";
      if (staff.profile_image) {
        profileImageHtml = `
                    <img src="${APP_URL}/uploads/${staff.profile_image}" alt="Profile" 
                         class="img-thumbnail mb-3" style="max-width: 200px; max-height: 200px;">
                `;
      } else {
        profileImageHtml = `
                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mb-3" 
                         style="width: 100px; height: 100px; font-size: 36px; font-weight: 600;">
                        ${staff.full_name.substring(0, 2).toUpperCase()}
                    </div>
                `;
      }

      // Build status badge
      const statusBadge =
        staff.status === "active"
          ? '<span class="badge bg-success">Active</span>'
          : '<span class="badge bg-danger">Inactive</span>';

      // Build content
        const assignedJOs = Array.isArray(staff.assigned_job_orders)
          ? staff.assigned_job_orders
          : [];
        const attendanceRecords = Array.isArray(staff.attendance)
          ? staff.attendance
          : [];
        const escapeHtml = (value) =>
          String(value ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
        const attendanceStatusClass = {
          present: "success",
          late: "warning",
          absent: "danger",
          on_leave: "info",
          other: "secondary",
        };
        const formatAttendanceTime = (value) => {
          if (!value) return "—";
          const [hours, minutes] = String(value).split(":").map(Number);
          if (!Number.isFinite(hours) || !Number.isFinite(minutes)) return "—";
          const period = hours >= 12 ? "PM" : "AM";
          const displayHour = hours % 12 || 12;
          return `${displayHour}:${String(minutes).padStart(2, "0")} ${period}`;
        };
        const formatRecordTime = (record, field) => {
          if (["absent", "on_leave", "other"].includes(record?.status)) return "—";
          return formatAttendanceTime(record?.[field]);
        };
        const attendanceByDate = attendanceRecords.reduce((grouped, record) => {
          const date = record.date || "N/A";
          if (!grouped[date]) grouped[date] = [];
          grouped[date].push(record);
          return grouped;
        }, {});
        const groupedAttendance = Object.entries(attendanceByDate).map(([date, records]) => {
          const sortedRecords = [...records].sort((a, b) =>
            String(a.time_in || "").localeCompare(String(b.time_in || "")),
          );
          return {
            date,
            morning: sortedRecords[0],
            afternoon: sortedRecords[1],
          };
        });
        const attendanceMonth = attendanceRecords.length > 0 && attendanceRecords[0].date
          ? new Date(`${attendanceRecords[0].date}T00:00:00`).toLocaleDateString("en-US", {
              month: "long",
              year: "numeric",
            })
          : "";
        const renderAttendanceStatus = (record) => {
          if (!record || !record.status) return "—";
          const status = String(record.status).replaceAll("_", " ");
          const statusClass = attendanceStatusClass[record.status] || "secondary";
          return `<span class="badge bg-${statusClass}">${escapeHtml(status)}</span>`;
        };
        const attendanceTab = `
          <div class="col-12">
            <ul class="nav nav-tabs" id="technicianDetailsTabs" role="tablist">
              ${staff.role === "technician" ? `
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="assigned-jobs-tab" data-bs-toggle="tab"
                        data-bs-target="#assigned-jobs-pane" type="button" role="tab"
                        aria-controls="assigned-jobs-pane" aria-selected="true">
                  <i class="bi bi-clipboard-check me-1"></i>Assigned Job Orders
                </button>
              </li>` : ""}
              <li class="nav-item" role="presentation">
                <button class="nav-link ${staff.role === "technician" ? "" : "active"}" id="attendance-tab" data-bs-toggle="tab"
                        data-bs-target="#attendance-pane" type="button" role="tab"
                        aria-controls="attendance-pane" aria-selected="${staff.role === "technician" ? "false" : "true"}">
                  <i class="bi bi-calendar-check me-1"></i>Attendance
                </button>
              </li>
            </ul>
            <div class="tab-content border border-top-0 rounded-bottom p-3">
              ${staff.role === "technician" ? `
              <div class="tab-pane fade show active" id="assigned-jobs-pane" role="tabpanel"
                   aria-labelledby="assigned-jobs-tab">
                ${assignedJOs.length === 0
                  ? '<p class="mb-0 text-muted">No assigned job orders.</p>'
                  : `
                    <div class="table-responsive assigned-job-orders-scroll">
                      <table class="table table-sm table-hover align-middle mb-0" style="font-size:12px;">
                        <thead class="table-light">
                          <tr>
                            <th>JO #</th>
                            <th>Customer</th>
                            <th>Plate</th>
                            <th>Status</th>
                            <th>My Time</th>
                            <th>Date</th>
                          </tr>
                        </thead>
                        <tbody>
                          ${assignedJOs.map((jo, idx) => {
                            const statusLabel = String(jo.status || "").replaceAll("_", " ");
                            const techStatus = jo.tech_status || '';
                            const isActive = (techStatus === 'assigned' || techStatus === 'working');
                            const statusBg = isActive ? 'success' : 'dark';
                            const createdDate = jo.created_at
                              ? new Date(jo.created_at).toLocaleDateString("en-US", {
                                  year: "numeric",
                                  month: "short",
                                  day: "numeric",
                                })
                              : "N/A";
                            const sessions = Array.isArray(jo.work_sessions) ? jo.work_sessions : [];
                            const hasActivity = sessions.length > 0;
                            return `
                              <tr style="cursor:${hasActivity ? 'pointer' : 'default'};" ${hasActivity ? `onclick="document.getElementById('techActivity_${idx}').style.display = document.getElementById('techActivity_${idx}').style.display === 'none' ? '' : 'none';"` : ''}>
                                <td class="fw-semibold">${jo.job_order_number || "N/A"}</td>
                                <td>${jo.customer_name || "N/A"}</td>
                                <td>${jo.plate_number || "N/A"}</td>
                                <td><span class="badge bg-secondary">${statusLabel || "N/A"}</span></td>
                                <td><span class="badge bg-${statusBg}" style="font-size:10px;">${isActive ? 'Active' : 'Inactive'}</span> <strong>${jo.tech_elapsed_display || "00:00:00"}</strong></td>
                                <td>${createdDate}</td>
                              </tr>
                              ${hasActivity ? `
                              <tr id="techActivity_${idx}" style="display:none;">
                                <td colspan="6" style="padding:0 8px 8px 8px;background:#f9f9f9;">
                                  <small class="text-muted fw-bold d-block mb-1" style="padding-top:6px;">Time Activity Log</small>
                                  <table class="table table-sm table-bordered mb-0" style="font-size:11px;">
                                    <thead><tr class="table-light">
                                      <th>#</th><th>Start</th><th>Stop</th><th>Worked</th><th>Idle</th>
                                    </tr></thead>
                                    <tbody>
                                      ${sessions.map((s, si) => {
                                        const startTime = s.start_time ? new Date(s.start_time).toLocaleString('en-PH', {month:'short',day:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit'}) : '—';
                                        const endTime = s.end_time ? new Date(s.end_time).toLocaleString('en-PH', {month:'short',day:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit'}) : '<span class="badge bg-success" style="font-size:9px;">Running</span>';
                                        let durDisplay = '—';
                                        if (s.end_time && s.start_time) {
                                          const durSec = Math.max(0, Math.floor((new Date(s.end_time) - new Date(s.start_time)) / 1000));
                                          durDisplay = String(Math.floor(durSec/3600)).padStart(2,'0') + ':' + String(Math.floor((durSec%3600)/60)).padStart(2,'0') + ':' + String(durSec%60).padStart(2,'0');
                                        } else if (!s.end_time && s.start_time) {
                                          const durSec = Math.max(0, Math.floor((Date.now() - new Date(s.start_time).getTime()) / 1000));
                                          durDisplay = '<span class="text-success">' + String(Math.floor(durSec/3600)).padStart(2,'0') + ':' + String(Math.floor((durSec%3600)/60)).padStart(2,'0') + ':' + String(durSec%60).padStart(2,'0') + '</span>';
                                        }
                                        let idle = '—';
                                        if (si > 0 && sessions[si-1].end_time && s.start_time) {
                                          const gapSec = Math.max(0, Math.floor((new Date(s.start_time) - new Date(sessions[si-1].end_time)) / 1000));
                                          if (gapSec > 0) idle = String(Math.floor(gapSec/3600)).padStart(2,'0') + ':' + String(Math.floor((gapSec%3600)/60)).padStart(2,'0') + ':' + String(gapSec%60).padStart(2,'0');
                                        }
                                        const notesHtml = s.notes ? '<div style="font-size:10px;color:#666;">'+s.notes+'</div>' : '';
                                        return '<tr><td>'+(si+1)+'</td><td>'+startTime+'</td><td>'+endTime+notesHtml+'</td><td class="font-monospace">'+durDisplay+'</td><td class="font-monospace text-muted">'+idle+'</td></tr>';
                                      }).join('')}
                                    </tbody>
                                  </table>
                                </td>
                              </tr>` : ''}
                            `;
                          }).join("")}
                        </tbody>
                      </table>
                    </div>
                  `}
              </div>` : ""}
              <div class="tab-pane fade ${staff.role === "technician" ? "" : "show active"}" id="attendance-pane" role="tabpanel" aria-labelledby="attendance-tab">
                ${attendanceRecords.length === 0
                  ? '<p class="mb-0 text-muted">No attendance records found.</p>'
                  : `
                    <div class="fw-semibold text-muted small mb-2">
                      <i class="bi bi-calendar3 me-1"></i>${escapeHtml(attendanceMonth)}
                    </div>
                    <div class="table-responsive assigned-job-orders-scroll">
                      <table class="table table-sm table-hover align-middle mb-0" style="font-size:12px;">
                        <thead class="table-light">
                          <tr>
                            <th>Date</th>
                            <th>Morning</th>
                            <th>Status</th>
                            <th>Afternoon</th>
                            <th>Status</th>
                            <th>Other details</th>
                          </tr>
                        </thead>
                        <tbody>
                          ${groupedAttendance.map((entry) => {
                            const morning = entry.morning || {};
                            const afternoon = entry.afternoon || {};
                            return `<tr>
                              <td>${escapeHtml(entry.date)}</td>
                              <td>${formatRecordTime(morning, "time_in")} - ${formatRecordTime(morning, "time_out")}</td>
                              <td>${renderAttendanceStatus(morning)}</td>
                              <td>${formatRecordTime(afternoon, "time_in")} - ${formatRecordTime(afternoon, "time_out")}</td>
                              <td>${renderAttendanceStatus(afternoon)}</td>
                              <td>
                                ${(morning.notes || afternoon.notes) ? `<button type="button" class="btn btn-sm btn-outline-secondary" onclick="viewAttendanceNotes(this)" data-morning-note="${escapeHtml(morning.notes || "")}" data-afternoon-note="${escapeHtml(afternoon.notes || "")}">View</button>` : "—"}
                              </td>
                            </tr>`;
                          }).join("")}
                        </tbody>
                      </table>
                    </div>
                  `}
              </div>
            </div>
          </div>
        `;
        const staffDetailsTabs =
          staff.role !== "admin"
            ? `
                      ${attendanceTab}
              `
            : "";

      const content = `
                <div class="text-center">
                    ${profileImageHtml}
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Staff ID</label>
                        <p class="mb-0">${staff.staff_id}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Full Name</label>
                        <p class="mb-0">${staff.full_name}</p>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-bold small text-muted">Login ID</label>
                      <p class="mb-0">${staff.staff_id}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Email</label>
                        <p class="mb-0">${staff.email}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Contact Number</label>
                        <p class="mb-0">${staff.contact_number}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Role/Position</label>
                        <p class="mb-0"><span class="badge bg-secondary">${staff.role}</span></p>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold small text-muted">Address</label>
                        <p class="mb-0">${staff.address || "N/A"}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Status</label>
                        <p class="mb-0">${statusBadge}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Date Created</label>
                        <p class="mb-0">${new Date(
                          staff.created_at,
                        ).toLocaleDateString("en-US", {
                          year: "numeric",
                          month: "long",
                          day: "numeric",
                        })}</p>
                    </div>
                    ${staffDetailsTabs}
                </div>
            `;

      document.getElementById("viewStaffContent").innerHTML = content;

      // Show modal
      const modal = new bootstrap.Modal(
        document.getElementById("viewStaffModal"),
      );
      modal.show();
    } else {
      showToast(data.message, "error");
    }
  } catch (error) {
    console.error("Error:", error);
    showToast("Failed to load staff data", "error");
  }
}

// Toggle Status Function
async function toggleStatus(id, currentStatus, expectedUpdatedAt = "") {
  const action = currentStatus === "active" ? "deactivate" : "activate";
  const confirmMessage = `Are you sure you want to ${action} this staff member?`;

  const confirmed = await appConfirm(confirmMessage, {
    title: "Change Staff Status",
    confirmText: action === "deactivate" ? "Deactivate" : "Activate",
    variant: action === "deactivate" ? "warning" : "primary",
  });

  if (!confirmed) {
    return;
  }

  const newStatus = currentStatus === "active" ? "inactive" : "active";

  try {
    const response = await fetch(`${APP_URL}/api/staff.php`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        id: id,
        status: newStatus,
        expected_updated_at: expectedUpdatedAt || "",
      }).toString(),
    });

    const data = await response.json();

    if (data.success) {
      showToast(`Staff ${action}d successfully`, "success");
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } else {
      showToast(data.message, "error");
    }
  } catch (error) {
    console.error("Error:", error);
    showToast("An error occurred. Please try again.", "error");
  }
}

// Delete Staff Function
async function deleteStaff(id, staffName = '') {
  const confirmed = await appConfirm(
    `Delete this staff member?\n\nName: ${staffName || 'Unknown'}\nStaff ID: ${id}\nThis action cannot be undone.`,
    {
      title: "Delete Staff",
      confirmText: "Delete",
      variant: "danger",
    },
  );

  if (!confirmed) {
    return;
  }

  try {
    const response = await fetch(`${APP_URL}/api/staff.php?id=${id}`, {
      method: "DELETE",
    });

    const data = await response.json();

    if (data.success) {
      showToast(data.message, "success");
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } else {
      showToast(data.message, "error");
    }
  } catch (error) {
    console.error("Error:", error);
    showToast("An error occurred. Please try again.", "error");
  }
}

// Make functions globally available
window.editStaff = editStaff;
window.viewStaff = viewStaff;
window.toggleStatus = toggleStatus;
window.deleteStaff = deleteStaff;
window.viewAttendanceNotes = viewAttendanceNotes;
