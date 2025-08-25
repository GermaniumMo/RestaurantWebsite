const cart = [];

document.addEventListener("DOMContentLoaded", () => {
  const reservationForm = document.getElementById("reservationForm");
  if (reservationForm) {
    reservationForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const phoneInput = this.querySelector('input[name="phone"]');

      // --- Phone validation ---
      if (!isValidPhone(phoneInput.value)) {
        showAlert(
          "warning",
          "Please enter a valid phone number (10-15 digits)."
        );
        phoneInput.focus();
        return; // Stop form submission
      }

      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = "Processing...";

      try {
        console.log("[v1] Submitting reservation form...");

        const formData = new FormData(this);
        const response = await fetch("process_reservation.php", {
          method: "POST",
          body: formData,
        });
        let data;
        try {
          data = await response.json(); // read the body once as JSON
          console.log("[v1] Reservation response:", data);
        } catch (err) {
          const text = await response.text(); // fallback to text if JSON fails
          console.error(
            "[v1] Failed to parse JSON response:",
            err,
            "Raw response:",
            text
          );
          showAlert("error", "Unexpected server response. Please try again.");
          return;
        }

        console.log("[v1] Reservation response:", data);

        if (data.success) {
          showAlert("success", data.message);
          this.reset();
          document.getElementById("date").min = new Date()
            .toISOString()
            .split("T")[0];
        } else {
          showAlert(
            "error",
            data.message ||
              "Failed to submit reservation. Please check your input."
          );
        }
      } catch (error) {
        console.error("[v1] Reservation error:", error);
        showAlert("error", "An error occurred. Please try again.");
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    });
  }

  loadFeaturedMenuItems();
});

// --- Phone validation helper ---
function isValidPhone(phone) {
  const digits = phone.replace(/\D/g, "");
  return digits.length >= 10 && digits.length <= 15;
}

function showAlert(type, message) {
  const alertTypes = {
    success: { class: "alert-success", icon: "✓" },
    error: { class: "alert-danger", icon: "✗" },
    warning: { class: "alert-warning", icon: "⚠" },
    info: { class: "alert-info", icon: "ℹ" },
  };

  const alertConfig = alertTypes[type] || alertTypes.info;

  const existingAlert = document.getElementById("dynamicAlert");
  if (existingAlert) existingAlert.remove();

  const alertDiv = document.createElement("div");
  alertDiv.id = "dynamicAlert";
  alertDiv.className = `alert ${alertConfig.class} alert-dismissible fade show position-fixed`;
  alertDiv.style.cssText =
    "top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 300px;";
  alertDiv.innerHTML = `
    <strong>${alertConfig.icon}</strong> ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  `;

  document.body.appendChild(alertDiv);

  setTimeout(() => {
    if (alertDiv && alertDiv.parentNode) {
      alertDiv.remove();
    }
  }, 5000);
}

async function loadFeaturedMenuItems() {
  const menuItemsContainer = document.getElementById("menu-items");
  if (!menuItemsContainer) return;

  try {
    console.log("[v1] Loading featured menu items...");
    const response = await fetch("api/get_featured_items.php");

    if (response.ok) {
      const items = await response.json();
      console.log("[v1] Featured items loaded:", items);

      if (items.length > 0) {
        menuItemsContainer.innerHTML = items
          .map(
            (item) => `
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <img src="${
                item.image_url || "/placeholder.svg?height=200&width=300"
              }" 
                   class="card-img-top" alt="${
                     item.name
                   }" style="height: 200px; object-fit: cover;">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">${item.name}</h5>
                <p class="card-text flex-grow-1">${item.description || ""}</p>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                  <span>$${Number.parseFloat(
                    item.price
                  ).toFixed(2)}</span>
           ${
                  item.is_featured
                    ? `<span style="
                        color:#ea580c;
                        font-size:1rem;
                        border:1px solid #ea580c;
                        background-color:transparent;
                        padding:6px 8px;
                        border-radius:6px;
                      ">
                        Featured
                      </span>`
                    : ""
                }
                </div>
              </div>
            </div>
          </div>
        `
          )
          .join("");
      } else {
        menuItemsContainer.innerHTML =
          '<div class="col-12 text-center"><p class="text-muted">No featured items available</p></div>';
      }
    }
  } catch (error) {
    console.error("[v1] Error loading featured items:", error);
    menuItemsContainer.innerHTML =
      '<div class="col-12 text-center"><p class="text-muted">Unable to load menu items</p></div>';
  }
}

function getStatusBadgeClass(status) {
  const statusClasses = {
    pending: "warning",
    confirmed: "success",
    cancelled: "danger",
    completed: "info",
  };
  return statusClasses[status] || "secondary";
}

async function updateReservationStatus(reservationId, status) {
  if (!confirm(`Are you sure you want to ${status} this reservation?`)) return;

  try {
    const response = await fetch("admin/reservation-update-status.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${reservationId}&status=${status}&ajax=1`,
    });

    const data = await response.json();
    console.log("[v1] Status update response:", data);

    if (data.success) {
      const statusCell = document.querySelector(
        `tr[data-reservation-id="${reservationId}"] .status-cell`
      );
      if (statusCell) {
        statusCell.innerHTML = `<span class="badge bg-${getStatusBadgeClass(
          status
        )}">${status}</span>`;
      }
      showAlert("success", `Reservation ${status} successfully!`);
    } else {
      showAlert("error", data.message || "Failed to update reservation status");
    }
  } catch (error) {
    console.error("[v1] Status update error:", error);
    showAlert("error", "An error occurred while updating the reservation");
  }
}

async function deleteReservation(reservationId) {
  if (
    !confirm(
      "Are you sure you want to delete this reservation? This action cannot be undone."
    )
  )
    return;

  try {
    const response = await fetch("admin/reservation-delete.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${reservationId}&ajax=1`,
    });

    const data = await response.json();
    console.log("[v1] Delete response:", data);

    if (data.success) {
      const row = document.querySelector(
        `tr[data-reservation-id="${reservationId}"]`
      );
      if (row) row.remove();
      showAlert("success", "Reservation deleted successfully!");
    } else {
      showAlert("error", data.message || "Failed to delete reservation");
    }
  } catch (error) {
    console.error("[v1] Delete error:", error);
    showAlert("error", "An error occurred while deleting the reservation");
  }
}

async function cancelReservation(reservationId, event) {
  if (!confirm("Are you sure you want to cancel this reservation?")) return;

  const button = event.target;
  const originalText = button.textContent;
  button.disabled = true;
  button.textContent = "Cancelling...";

  try {
    const response = await fetch("cancel-reservation.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `reservation_id=${reservationId}`,
    });

    const data = await response.json();
    console.log("[v1] Cancel response:", data);

    if (data.success) {
      const reservationCard = button.closest(".reservation-card, .card, tr");
      if (reservationCard) {
        reservationCard.style.transition = "opacity 0.3s ease";
        reservationCard.style.opacity = "0";
        setTimeout(() => reservationCard.remove(), 300);
      }
      showAlert(
        "success",
        data.message || "Reservation cancelled successfully!"
      );
    } else {
      showAlert("error", data.message || "Failed to cancel reservation");
    }
  } catch (error) {
    console.error("[v1] Cancel reservation error:", error);
    showAlert("error", "An error occurred while cancelling the reservation.");
  } finally {
    button.disabled = false;
    button.textContent = originalText;
  }
}
