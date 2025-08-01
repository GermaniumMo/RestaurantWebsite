const cart = []

document.addEventListener("DOMContentLoaded", () => {
  const buttons = document.querySelectorAll(".card")

  buttons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault()

      const card = btn.closest(".card")
      const itemData = card.getAttribute("data-item")

      if (itemData) {
        const item = JSON.parse(itemData)
        cart.push({
          title: item.name,
          price: "$" + Number.parseFloat(item.price).toFixed(2),
          imageSrc: item.image_url,
        })
        updateCartModal()

        const cartModal = new bootstrap.Modal(document.getElementById("cartModal"))
        cartModal.show()
      }
    })
  })

  // Handle reservation form validation
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
  }

  // Navigation active state
  const currentPage = window.location.pathname.split("/").pop()

  const navLinks = document.querySelectorAll("header a")

  navLinks.forEach((link) => {
    if (link.getAttribute("href") === currentPage) {
      link.classList.add("active")
    }
  })
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
