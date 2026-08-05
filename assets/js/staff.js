/**
 * Staff Management JavaScript
 * Handles all client-side operations for staff management
 */

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
        preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview" 
                     class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
            `;
      };
      reader.readAsDataURL(file);
    } else {
      preview.innerHTML = "";
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
        preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview" 
                     class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
            `;
      };
      reader.readAsDataURL(file);
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

      // Clear password fields
      document.getElementById("edit_password").value = "";
      document.getElementById("edit_confirm_password").value = "";

      // Show current profile image if exists
      const preview = document.getElementById("edit_image_preview");
      if (staff.profile_image) {
        preview.innerHTML = `
                    <div class="mt-2">
                        <p class="small text-muted mb-1">Current Image:</p>
                        <img src="${APP_URL}/uploads/${staff.profile_image}" alt="Current Profile" 
                             class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                    </div>
                `;
      } else {
        preview.innerHTML = "";
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
async function toggleStatus(id, currentStatus) {
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
async function deleteStaff(id) {
  const confirmed = await appConfirm(
    "Are you sure you want to delete this staff member? This action cannot be undone.",
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
