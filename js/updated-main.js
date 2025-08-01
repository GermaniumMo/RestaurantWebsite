const cart = []
const bootstrap = window.bootstrap // Declare the bootstrap variable

document.addEventListener("DOMContentLoaded", () => {
  const buttons = document.querySelectorAll(".card")

  buttons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault()

      const card = btn.closest(".card")
      const title = card.querySelector(".card-title").childNodes[0].textContent.trim()
      const price = card.querySelector(".text-primary").textContent.trim()
      const imageSrc = card.querySelector("img").getAttribute("src")

      cart.push({ title, price, imageSrc })
      updateCartModal()

      const cartModal = new bootstrap.Modal(document.getElementById("cartModal"))
      cartModal.show()
    })
  })

  // Handle reservation form
  const reserveBtn = document.getElementById("reserveBtn")
  if (reserveBtn) {
    const inputs = [
      document.getElementById("name"),
      document.getElementById("email"),
      document.getElementById("date"),
      document.getElementById("time"),
      document.getElementById("numberGuests"),
    ]

    function checkInputsFilled() {
      const allFilled = inputs.every((input) => input && input.value.trim() !== "")
      if (reserveBtn) {
        reserveBtn.disabled = !allFilled
      }
    }

    inputs.forEach((input) => {
      if (input) {
        input.addEventListener("input", checkInputsFilled)
      }
    })

    reserveBtn.addEventListener("click", (e) => {
      e.preventDefault()

      // Get form data
      const formData = new FormData()
      formData.append("name", document.getElementById("name").value)
      formData.append("email", document.getElementById("email").value)
      formData.append("date", document.getElementById("date").value)
      formData.append("time", document.getElementById("time").value)
      formData.append("NumberofGuests", document.getElementById("numberGuests").value)

      // Submit to PHP backend
      fetch("process_reservation.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (response.ok) {
            showAlert("An email has been sent to confirm your reservation.", "success")
            // Reset form
            inputs.forEach((input) => {
              if (input) input.value = ""
            })
            checkInputsFilled()
          } else {
            showAlert("Failed to submit reservation. Please try again.", "error")
          }
        })
        .catch((error) => {
          showAlert("Failed to submit reservation. Please try again.", "error")
        })
    })
  }

  // Handle contact form
  const contactForm = document.querySelector('form[action=""]')
  if (contactForm && window.location.pathname.includes("contact")) {
    contactForm.addEventListener("submit", (e) => {
      e.preventDefault()

      const formData = new FormData()
      formData.append("first_name", document.querySelector('input[name="name"]').value)
      formData.append("last_name", document.querySelector('input[name="text"]').value)
      formData.append("email", document.querySelector('input[name="email"]').value)
      formData.append("message", document.getElementById("message-box").value)

      fetch("process_contact.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (response.ok) {
            showAlert("Message sent successfully!", "success")
            contactForm.reset()
          } else {
            showAlert("Failed to send message. Please try again.", "error")
          }
        })
        .catch((error) => {
          showAlert("Failed to send message. Please try again.", "error")
        })
    })
  }
})

function updateCartModal() {
  const modalBody = document.querySelector("#cartModal .modal-body")

  if (cart.length === 0) {
    modalBody.innerHTML = "<p>Your cart is currently empty.</p>"
    return
  }

  modalBody.innerHTML = ""
  cart.forEach((item) => {
    const itemHTML = `
      <div class="d-flex align-items-center mb-3">
        <img src="${item.imageSrc}" alt="${item.title}" width="60" class="me-3 rounded" />
        <div>
          <h6 class="mb-0">${item.title}</h6>
          <small class="text-muted">${item.price}</small>
        </div>
      </div>`
    modalBody.innerHTML += itemHTML
  })
}

function showAlert(message, type) {
  const oldAlert = document.getElementById("dynamicAlert")
  if (oldAlert) oldAlert.remove()

  const alertBox = document.createElement("div")
  alertBox.id = "dynamicAlert"
  alertBox.innerHTML = `
    <div style="
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background-color: ${type === "success" ? "#d1e7dd" : "#f8d7da"};
      color: ${type === "success" ? "#0f5132" : "#721c24"};
      border: 1px solid ${type === "success" ? "#badbcc" : "#f5c6cb"};
      padding: 16px 24px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      font-weight: 500;
      font-size: 16px;
      z-index: 9999;
      display: flex;
      align-items: center;
      gap: 10px;
    ">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
        ${
          type === "success"
            ? '<path d="M16 8A8 8 0 1 0 0 8a8 8 0 0 0 16 0zM6.93 11.588l-3.39-3.39 1.06-1.06 2.33 2.33 4.95-4.95 1.06 1.06-6.01 6.01z"/>'
            : '<path d="M16 8A8 8 0 1 0 0 8a8 8 0 0 0 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>'
        }
      </svg>
      ${message}
    </div>
  `
  document.body.appendChild(alertBox)
  setTimeout(() => alertBox.remove(), 5000)
}

// Navigation active state
document.addEventListener("DOMContentLoaded", () => {
  const currentPage = window.location.pathname.split("/").pop()

  const navLinks = document.querySelectorAll("header a")

  navLinks.forEach((link) => {
    if (link.getAttribute("href") === currentPage) {
      link.classList.add("active")
    }
  })
})

// Menu category filtering (for menu page)
function changeColor(button) {
  document.querySelectorAll(".btn-menu").forEach((btn) => {
    btn.classList.remove("btn-menu-active")
  })
  button.classList.add("btn-menu-active")

  const bg = document.querySelector(".active-bg")
  if (bg) {
    const container = document.querySelector(".btn-container")
    const rect = button.getBoundingClientRect()
    const containerRect = container.getBoundingClientRect()

    const left = rect.left - containerRect.left
    const width = rect.width

    bg.style.left = left + "px"
    bg.style.width = width + "px"
  }
}
