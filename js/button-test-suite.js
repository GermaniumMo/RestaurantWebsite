// Comprehensive button functionality testing suite
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

    // Test all form submissions
    this.testReservationForms()
    this.testProfileForms()
    this.testContactForm()
    this.testMenuButtons()
    this.testNavigationButtons()

    // Report results
    this.reportResults()
  }

  testReservationForms() {
    console.log("[v0] Testing reservation forms...")

    // Test main reservation form
    const mainReservationForm = document.getElementById("reservationForm")
    if (mainReservationForm) {
      this.testFormSubmission(mainReservationForm, "Main Reservation Form")
    }

    // Test footer reservation form
    const footerReservationForm = document.getElementById("footerReservationForm")
    if (footerReservationForm) {
      this.testFormSubmission(footerReservationForm, "Footer Reservation Form")
    }
  }

  testProfileForms() {
    console.log("[v0] Testing profile forms...")

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
    console.log("[v0] Testing contact form...")

    const contactForm = document.getElementById("contactForm")
    if (contactForm) {
      this.testFormSubmission(contactForm, "Contact Form")
    }
  }

  testMenuButtons() {
    console.log("[v0] Testing menu buttons...")

    // Test add to cart buttons
    const addToCartButtons = document.querySelectorAll('[onclick*="addToCart"]')
    addToCartButtons.forEach((button, index) => {
      this.testButtonClick(button, `Add to Cart Button ${index + 1}`)
    })

    // Test quantity buttons
    const quantityButtons = document.querySelectorAll('[onclick*="updateQuantity"]')
    quantityButtons.forEach((button, index) => {
      this.testButtonClick(button, `Quantity Button ${index + 1}`)
    })

    // Test checkout button
    const checkoutButton = document.getElementById("checkoutBtn")
    if (checkoutButton) {
      this.testButtonClick(checkoutButton, "Checkout Button")
    }
  }

  testNavigationButtons() {
    console.log("[v0] Testing navigation buttons...")

    // Test reservation button in navigation
    const reservationNavButton = document.querySelector('a[href*="#reservation"]')
    if (reservationNavButton) {
      this.testButtonClick(reservationNavButton, "Navigation Reservation Button")
    }

    // Test cancel reservation buttons
    const cancelButtons = document.querySelectorAll(".cancel-reservation-btn")
    cancelButtons.forEach((button, index) => {
      this.testButtonClick(button, `Cancel Reservation Button ${index + 1}`)
    })
  }

  testFormSubmission(form, formName) {
    try {
      // Check if form has proper event listeners
      const hasSubmitListener = this.hasEventListener(form, "submit")

      // Check if form has CSRF token
      const csrfToken = form.querySelector('input[name="csrf_token"]')

      // Check if submit button exists and is properly configured
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
      // Check if button is clickable
      const isClickable = !button.disabled && button.style.display !== "none"

      // Check if button has proper onclick or event listeners
      const hasClickHandler = button.onclick || this.hasEventListener(button, "click")

      // Check if button has proper loading states
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
    // Check if element has event listeners (simplified check)
    return (
      element[`on${eventType}`] !== null ||
      element.getAttribute(`on${eventType}`) !== null ||
      this.getEventListeners(element, eventType).length > 0
    )
  }

  getEventListeners(element, eventType) {
    // Simplified event listener detection
    try {
      return element.getEventListeners ? element.getEventListeners()[eventType] || [] : []
    } catch {
      return []
    }
  }

  reportResults() {
    console.log("[v0] ===== BUTTON FUNCTIONALITY TEST RESULTS =====")

    const passed = this.testResults.filter((r) => r.status === "PASS").length
    const failed = this.testResults.filter((r) => r.status === "FAIL").length
    const errors = this.testResults.filter((r) => r.status === "ERROR").length

    console.log(`[v0] Total Tests: ${this.testResults.length}`)
    console.log(`[v0] Passed: ${passed}`)
    console.log(`[v0] Failed: ${failed}`)
    console.log(`[v0] Errors: ${errors}`)

    // Detailed results
    this.testResults.forEach((result) => {
      if (result.status !== "PASS") {
        console.log(`[v0] ${result.status}: ${result.name}`, result)
      }
    })

    // Create visual report if in browser
    if (typeof document !== "undefined") {
      this.createVisualReport()
    }

    console.log("[v0] ===== END TEST RESULTS =====")
  }

  createVisualReport() {
    // Create a floating test results panel
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

    // Auto-remove after 10 seconds
    setTimeout(() => {
      if (reportPanel.parentElement) {
        reportPanel.remove()
      }
    }, 10000)
  }
}

// Auto-run tests when page loads
document.addEventListener("DOMContentLoaded", () => {
  // Only run tests if URL contains test parameter
  if (window.location.search.includes("test=buttons") || window.location.hash.includes("test-buttons")) {
    new ButtonTestSuite()
  }
})

// Export for manual testing
window.ButtonTestSuite = ButtonTestSuite
