const cart = []

document.addEventListener("DOMContentLoaded", () => {
  const reservationForm = document.getElementById("reservationForm")
  if (reservationForm) {
    reservationForm.addEventListener("submit", async function (e) {
      e.preventDefault()

      const submitBtn = this.querySelector('button[type="submit"]')
      const originalText = submitBtn.textContent
      submitBtn.disabled = true
      submitBtn.textContent = "Processing..."

      try {
        console.log("[v0] Submitting reservation form...")

        const formData = new FormData(this)
        const response = await fetch("process_reservation.php", {
          method: "POST",
          body: formData,
        })

        console.log("[v0] Reservation response status:", response.status)
        const responseText = await response.text()
        console.log("[v0] Reservation response:", responseText)

        if (response.ok) {
          // Show success message
          showAlert("success", "Reservation submitted successfully! We will contact you to confirm.")
          this.reset()
          // Set minimum date again after reset
          document.getElementById("date").min = new Date().toISOString().split("T")[0]
        } else {
          showAlert("error", "Failed to submit reservation. Please try again.")
        }
      } catch (error) {
        console.error("[v0] Reservation error:", error)
        showAlert("error", "An error occurred. Please try again.")
      } finally {
        submitBtn.disabled = false
        submitBtn.textContent = originalText
      }
    })
  }

  loadFeaturedMenuItems()
})

function showAlert(type, message) {
  const alertTypes = {
    success: { class: "alert-success", icon: "✓" },
    error: { class: "alert-danger", icon: "✗" },
    warning: { class: "alert-warning", icon: "⚠" },
    info: { class: "alert-info", icon: "ℹ" },
  }

  const alertConfig = alertTypes[type] || alertTypes.info

  // Remove existing alerts
  const existingAlert = document.getElementById("dynamicAlert")
  if (existingAlert) existingAlert.remove()

  const alertDiv = document.createElement("div")
  alertDiv.id = "dynamicAlert"
  alertDiv.className = `alert ${alertConfig.class} alert-dismissible fade show position-fixed`
  alertDiv.style.cssText = "top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 300px;"
  alertDiv.innerHTML = `
    <strong>${alertConfig.icon}</strong> ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  `

  document.body.appendChild(alertDiv)

  // Auto-remove after 5 seconds
  setTimeout(() => {
    if (alertDiv && alertDiv.parentNode) {
      alertDiv.remove()
    }
  }, 5000)
}

async function loadFeaturedMenuItems() {
  const menuItemsContainer = document.getElementById("menu-items")
  if (!menuItemsContainer) return

  try {
    console.log("[v0] Loading featured menu items...")
    const response = await fetch("api/get_featured_items.php")

    if (response.ok) {
      const items = await response.json()
      console.log("[v0] Featured items loaded:", items)

      if (items.length > 0) {
        menuItemsContainer.innerHTML = items
          .map(
            (item) => `
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <img src="${item.image_url || "/placeholder.svg?height=200&width=300"}" 
                   class="card-img-top" alt="${item.name}" style="height: 200px; object-fit: cover;">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">${item.name}</h5>
                <p class="card-text flex-grow-1">${item.description || ""}</p>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                  <span class="text-primary fw-bold">$${Number.parseFloat(item.price).toFixed(2)}</span>
                  ${item.is_featured ? '<span class="badge bg-warning">Featured</span>' : ""}
                </div>
              </div>
            </div>
          </div>
        `,
          )
          .join("")
      } else {
        menuItemsContainer.innerHTML =
          '<div class="col-12 text-center"><p class="text-muted">No featured items available</p></div>'
      }
    }
  } catch (error) {
    console.error("[v0] Error loading featured items:", error)
    menuItemsContainer.innerHTML =
      '<div class="col-12 text-center"><p class="text-muted">Unable to load menu items</p></div>'
  }
}

function updateReservationStatus(reservationId, status) {
  if (!confirm(`Are you sure you want to ${status} this reservation?`)) {
    return
  }

  console.log("[v0] Updating reservation status:", reservationId, status)

  fetch("admin/reservation-update-status.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `id=${reservationId}&status=${status}&ajax=1`,
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("[v0] Status update response:", data)
      if (data.success) {
        // Update the status in the table
        const statusCell = document.querySelector(`tr[data-reservation-id="${reservationId}"] .status-cell`)
        if (statusCell) {
          statusCell.innerHTML = `<span class="badge bg-${getStatusBadgeClass(status)}">${status}</span>`
        }
        showAlert("success", `Reservation ${status} successfully!`)
      } else {
        showAlert("error", data.message || "Failed to update reservation status")
      }
    })
    .catch((error) => {
      console.error("[v0] Status update error:", error)
      showAlert("error", "An error occurred while updating the reservation")
    })
}

function deleteReservation(reservationId) {
  if (!confirm("Are you sure you want to delete this reservation? This action cannot be undone.")) {
    return
  }

  console.log("[v0] Deleting reservation:", reservationId)

  fetch("admin/reservation-delete.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `id=${reservationId}&ajax=1`,
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("[v0] Delete response:", data)
      if (data.success) {
        // Remove the row from the table
        const row = document.querySelector(`tr[data-reservation-id="${reservationId}"]`)
        if (row) {
          row.remove()
        }
        showAlert("success", "Reservation deleted successfully!")
      } else {
        showAlert("error", data.message || "Failed to delete reservation")
      }
    })
    .catch((error) => {
      console.error("[v0] Delete error:", error)
      showAlert("error", "An error occurred while deleting the reservation")
    })
}

function deleteMenuItem(itemId) {
  if (!confirm("Are you sure you want to delete this menu item? This action cannot be undone.")) {
    return
  }

  console.log("[v0] Deleting menu item:", itemId)

  fetch("admin/menu-delete.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `id=${itemId}&ajax=1`,
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("[v0] Menu delete response:", data)
      if (data.success) {
        // Remove the row from the table
        const row = document.querySelector(`tr[data-item-id="${itemId}"]`)
        if (row) {
          row.remove()
        }
        showAlert("success", "Menu item deleted successfully!")
      } else {
        showAlert("error", data.message || "Failed to delete menu item")
      }
    })
    .catch((error) => {
      console.error("[v0] Menu delete error:", error)
      showAlert("error", "An error occurred while deleting the menu item")
    })
}

function getStatusBadgeClass(status) {
  const statusClasses = {
    pending: "warning",
    confirmed: "success",
    cancelled: "danger",
    completed: "info",
  }
  return statusClasses[status] || "secondary"
}

async function cancelReservation(reservationId) {
  if (!confirm("Are you sure you want to cancel this reservation?")) {
    return
  }

  const button = event.target
  const originalText = button.textContent
  button.disabled = true
  button.textContent = "Cancelling..."

  try {
    console.log("[v0] Cancelling reservation:", reservationId)

    const response = await fetch("cancel-reservation.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `reservation_id=${reservationId}`,
    })

    console.log("[v0] Cancel response status:", response.status)
    const responseText = await response.text()
    console.log("[v0] Cancel response:", responseText)

    if (response.ok) {
      // Remove the reservation card from the page
      const reservationCard = button.closest(".reservation-card, .card, tr")
      if (reservationCard) {
        reservationCard.style.transition = "opacity 0.3s ease"
        reservationCard.style.opacity = "0"
        setTimeout(() => {
          reservationCard.remove()
        }, 300)
      }

      showAlert("success", "Reservation cancelled successfully!")
    } else {
      showAlert("error", "Failed to cancel reservation. Please try again.")
    }
  } catch (error) {
    console.error("[v0] Cancel reservation error:", error)
    showAlert("error", "An error occurred while cancelling the reservation.")
  } finally {
    button.disabled = false
    button.textContent = originalText
  }
}
