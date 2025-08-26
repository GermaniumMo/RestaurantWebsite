class ButtonTestSuite {
  constructor() {
    this.testResults = []
    this.init()
  }

  init() {
    console.log("[v0] Button Test Suite initialized")
    this.runAllTests()
  }

  runAllTests() {
    console.log("[v0] Running comprehensive button functionality tests...")

    this.testReservationForms()
    this.testProfileForms()
    this.testContactForm()
    this.testMenuButtons()
    this.testNavigationButtons()
    this.reportResults()
  }

  testReservationForms() {
    const mainReservationForm = document.getElementById("reservationForm")
    if (mainReservationForm) {
      this.testFormSubmission(mainReservationForm, "Main Reservation Form")
    }
    const footerReservationForm = document.getElementById("footerReservationForm")
    if (footerReservationForm) {
      this.testFormSubmission(footerReservationForm, "Footer Reservation Form")
    }
  }

  testProfileForms() {
    const profileForm = document.getElementById("profileForm")
    if (profileForm) {
      this.testFormSubmission(profileForm, "Profile Update Form")
    }

    const passwordForm = document.getElementById("passwordForm")
    if (passwordForm) {
      this.testFormSubmission(passwordForm, "Password Change Form")
    }
  }

  testContactForm() {

    const contactForm = document.getElementById("contactForm")
    if (contactForm) {
      this.testFormSubmission(contactForm, "Contact Form")
    }
  }

  testMenuButtons() {
    const addToCartButtons = document.querySelectorAll('[onclick*="addToCart"]')
    addToCartButtons.forEach((button, index) => {
      this.testButtonClick(button, `Add to Cart Button ${index + 1}`)
    })
    const quantityButtons = document.querySelectorAll('[onclick*="updateQuantity"]')
    quantityButtons.forEach((button, index) => {
      this.testButtonClick(button, `Quantity Button ${index + 1}`)
    })
    const checkoutButton = document.getElementById("checkoutBtn")
    if (checkoutButton) {
      this.testButtonClick(checkoutButton, "Checkout Button")
    }
  }

  testNavigationButtons() {
    const reservationNavButton = document.querySelector('a[href*="#reservation"]')
    if (reservationNavButton) {
      this.testButtonClick(reservationNavButton, "Navigation Reservation Button")
    }
    const cancelButtons = document.querySelectorAll(".cancel-reservation-btn")
    cancelButtons.forEach((button, index) => {
      this.testButtonClick(button, `Cancel Reservation Button ${index + 1}`)
    })
  }

  testFormSubmission(form, formName) {
    try {
      const hasSubmitListener = this.hasEventListener(form, "submit")
      const csrfToken = form.querySelector('input[name="csrf_token"]')
      const submitButton = form.querySelector('button[type="submit"]')

      const result = {
        name: formName,
        hasSubmitListener,
        hasCsrfToken: !!csrfToken,
        hasSubmitButton: !!submitButton,
        status: hasSubmitListener && csrfToken && submitButton ? "PASS" : "FAIL",
      }

      this.testResults.push(result)
      console.log(`[v0] ${formName}: ${result.status}`)
    } catch (error) {
      console.error(`[v0] Error testing ${formName}:`, error)
      this.testResults.push({
        name: formName,
        status: "ERROR",
        error: error.message,
      })
    }
  }

  testButtonClick(button, buttonName) {
    try {
      const isClickable = !button.disabled && button.style.display !== "none"
      const hasClickHandler = button.onclick || this.hasEventListener(button, "click")
      const hasLoadingState =
        button.querySelector(".spinner-border") ||
        button.innerHTML.includes("spinner") ||
        button.classList.contains("btn-loading")

      const result = {
        name: buttonName,
        isClickable,
        hasClickHandler,
        hasLoadingState,
        status: isClickable && hasClickHandler ? "PASS" : "FAIL",
      }

      this.testResults.push(result)
      console.log(`[v0] ${buttonName}: ${result.status}`)
    } catch (error) {
      console.error(`[v0] Error testing ${buttonName}:`, error)
      this.testResults.push({
        name: buttonName,
        status: "ERROR",
        error: error.message,
      })
    }
  }

  hasEventListener(element, eventType) {
    return (
      element[`on${eventType}`] !== null ||
      element.getAttribute(`on${eventType}`) !== null ||
      this.getEventListeners(element, eventType).length > 0
    )
  }

  getEventListeners(element, eventType) {
    try {
      return element.getEventListeners ? element.getEventListeners()[eventType] || [] : []
    } catch {
      return []
    }
  }

  reportResults() {
    const passed = this.testResults.filter((r) => r.status === "PASS").length
    const failed = this.testResults.filter((r) => r.status === "FAIL").length
    const errors = this.testResults.filter((r) => r.status === "ERROR").length

    console.log(`[v0] Total Tests: ${this.testResults.length}`)
    console.log(`[v0] Passed: ${passed}`)
    console.log(`[v0] Failed: ${failed}`)
    console.log(`[v0] Errors: ${errors}`)

    this.testResults.forEach((result) => {
      if (result.status !== "PASS") {
        console.log(`[v0] ${result.status}: ${result.name}`, result)
      }
    })

    if (typeof document !== "undefined") {
      this.createVisualReport()
    }

    console.log("[v0] ===== END TEST RESULTS =====")
  }

  createVisualReport() {
    const reportPanel = document.createElement("div")
    reportPanel.id = "button-test-report"
    reportPanel.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            width: 300px;
            max-height: 400px;
            overflow-y: auto;
            background: white;
            border: 2px solid #ea580c;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 10000;
            font-family: monospace;
            font-size: 12px;
        `

    const passed = this.testResults.filter((r) => r.status === "PASS").length
    const total = this.testResults.length

    reportPanel.innerHTML = `
            <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 10px;">
                <h4 style="margin: 0; color: #ea580c;">Button Tests</h4>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; font-size: 18px; cursor: pointer;">×</button>
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Results: ${passed}/${total} passed</strong>
            </div>
            <div style="max-height: 300px; overflow-y: auto;">
                ${this.testResults
                  .map(
                    (result) => `
                    <div style="margin: 5px 0; padding: 5px; background: ${result.status === "PASS" ? "#d4edda" : "#f8d7da"}; border-radius: 4px;">
                        <strong>${result.status}</strong>: ${result.name}
                        ${result.error ? `<br><small>Error: ${result.error}</small>` : ""}
                    </div>
                `,
                  )
                  .join("")}
            </div>
        `

    document.body.appendChild(reportPanel)

    setTimeout(() => {
      if (reportPanel.parentElement) {
        reportPanel.remove()
      }
    }, 10000)
  }
}

document.addEventListener("DOMContentLoaded", () => {
  if (window.location.search.includes("test=buttons") || window.location.hash.includes("test-buttons")) {
    new ButtonTestSuite()
  }
})

window.ButtonTestSuite = ButtonTestSuite
