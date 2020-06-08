const isMac = navigator.appVersion.includes('Macintosh')

/**
 * Check if the OS-specific main modifier key is pressed in a keyboard event
 *
 * @param {KeyboardEvent} event
 * @returns {boolean}
 */
export function modifierKeyPressed(event) {
  return isMac ? event.metaKey : event.ctrlKey
}

/**
 * Copy text to the clipboard
 * Can only be executed as a direct effect of a user interaction (e.g. click)
 *
 * @param {string} text The text to copy
 */
export function copy(text) {
  const activeElement = document.activeElement

  const input = document.createElement('input')
  input.value = text
  input.style.position = 'fixed'
  input.style.zIndex = -1
  document.body.appendChild(input)
  input.focus({ preventScroll: true })
  input.select()
  document.execCommand('copy')
  input.remove()

  activeElement.focus({ preventScroll: true })
}

/**
 * Make a link prefetch its target page when being hovered or focused
 * for a certain amount of time
 *
 * @param {HTMLAnchorElement} link   The link to preload
 * @param {number}            delay  The number of milliseconds the user has to stay
 *                                   in touch with the link to trigger a prefetch
 */
export function prefetchOnInteraction(link, delay = 250) {
  function enterHandler() {
    let didLeave = false
    function leaveHandler() {
      didLeave = true
    }

    link.addEventListener('mouseout', leaveHandler, { once: true })
    link.addEventListener('blur', leaveHandler, { once: true })

    setTimeout(() => {
      link.removeEventListener('mouseout', leaveHandler)
      link.removeEventListener('blur', leaveHandler)

      if (!didLeave) {
        link.removeEventListener('mouseover', enterHandler)
        link.removeEventListener('focus', enterHandler)

        const href = link.getAttribute('href')

        const prefetchLink = document.createElement('link')
        prefetchLink.rel = 'prefetch'
        prefetchLink.as = 'document'
        prefetchLink.href = href

        document.head.appendChild(prefetchLink)
      }
    }, delay)
  }

  link.addEventListener('mouseover', enterHandler)
  link.addEventListener('focus', enterHandler)
}

let timeout
/**
 * Utility to mark any key on the keyboard as pressed
 * for the remainder of the call stack
 */
export const key = {
  /**
   * Whether a key is pressed
   */
  isPressed: false,

  /**
   * Mark the key as pressed
   */
  markPressed() {
    this.isPressed = true

    clearTimeout(timeout)
    timeout = setTimeout(() => {
      this.isPressed = false
    }, 0)
  },
}

// Bind the `markPressed` method
key.markPressed = key.markPressed.bind(key)

/**
 * Run a callback as soon as the browser has spare resources
 * Fall back to a simple setTimeout() if feature is not supported
 *
 * @param {function} callback
 * @param {object}   options
 * @returns {number}
 */
export function requestIdleCallback(callback, options = {}) {
  if (typeof window.requestIdleCallback === 'function') {
    return window.requestIdleCallback(callback, options)
  } else {
    return window.setTimeout(callback, 0)
  }
}

/**
 * Cancel a callback registered with requestIdleCallback()
 *
 * @param {number} handle The handle returned by requestIdleCallback()
 */
export function cancelIdleCallback(handle) {
  if (typeof window.requestIdleCallback === 'function') {
    window.cancelIdleCallback(handle)
  } else {
    window.clearTimeout(handle)
  }
}

/**
 * Wait for a CSS transition to finish on an element
 *
 * @param {HTMLElement} element
 * @returns {Promise<void>}
 */
export function waitTransition(element) {
  return new Promise(resolve => {
    const listener = event => {
      if (event.target === event.currentTarget) {
        element.removeEventListener('transitionend', listener)
        resolve()
      }
    }

    element.addEventListener('transitionend', listener)
  })
}
