import { waitTransition } from './util'

/**
 * @type {Toast|null}
 */
let previousToast = null

class Toast {
  /**
   * @type {number|null}
   */
  timeoutDestruction = null

  /**
   * @type {Promise<void>|null}
   */
  initialShow = null

  /**
   * @param {string} message The message to show in the toast
   * @param {object} options Options to the toast
   */
  constructor(
    message,
    {
      position = 'bottom',
      show = true,
      timeout = 3000,
      container = document.body,
      style = '',
      edge = false,
    } = {},
  ) {
    this.message = message
    this.position = position
    this.container = container
    this.timeout = timeout

    this.element = document.createElement('div')
    this.element.role = 'alert'
    this.element.classList.add('toast', `toast--${this.position}`)

    if (style) {
      this.element.classList.add(`toast--${style}`)
    }

    this.element.innerHTML = this.message

    if (edge) {
      this.element.classList.add('toast--edge')
    }

    this.container.appendChild(this.element)

    const previousToastDestruction = previousToast
      ? previousToast.destroy()
      : Promise.resolve()

    previousToast = this

    if (show) {
      this.initialShow = previousToastDestruction.then(
        () =>
          new Promise(resolve =>
            setTimeout(() => {
              requestAnimationFrame(() => this.show().then(resolve))
            }, 0),
          ),
      )

      this.initialShow.then(() => {
        this.initialShow = null
      })

      if (Number.isFinite(this.timeout) && this.timeout !== 0) {
        this.initialShow.then(() => {
          this.timeoutDestruction = setTimeout(() => {
            this.timeoutDestruction = null
            this.destroy()
          }, this.timeout)
        })
      }
    }
  }

  /**
   * @type {Promise<void>}
   */
  transitionPromise

  /**
   * Show the toast message
   *
   * @returns {Promise<void>} Indicate when show animation has finished
   */
  async show() {
    if (this.destroyed) {
      throw new Error('Toast has already been destroyed and can not be shown')
    }

    if (this.element.classList.contains('toast--shown')) {
      return this.transitionPromise || Promise.resolve()
    }

    if (this.transitionPromise) {
      await this.transitionPromise
    }

    this.transitionPromise = waitTransition(this.element)
    this.transitionPromise.then(() => {
      this.transitionPromise = null
    })

    this.element.classList.add('toast--shown')

    return this.transitionPromise
  }

  /**
   * Hide the toast message
   *
   * @returns {Promise<void>} Indicate when hide animation has finished
   */
  async hide() {
    // Cancel inherit timeout destruction if manually hiding
    if (this.timeoutDestruction !== null) {
      clearTimeout(this.timeoutDestruction)
      this.timeoutDestruction = null
    }

    if (this.destroyed) {
      throw new Error('Toast has already been destroyed and can not be hidden')
    }

    if (!this.element.classList.contains('toast--shown')) {
      return this.transitionPromise || Promise.resolve()
    }

    if (this.transitionPromise) {
      await this.transitionPromise
    }

    this.transitionPromise = waitTransition(this.element)
    this.transitionPromise.then(() => {
      this.transitionPromise = null
    })

    requestAnimationFrame(() => this.element.classList.remove('toast--shown'))

    return this.transitionPromise
  }

  /**
   * @type {boolean}
   */
  destroyed = false

  /**
   * Hide the toast and remove it from the DOM
   */
  async destroy() {
    if (this.destroyed) {
      throw new Error(
        'Toast has already been destroyed and can not be destroyed again',
      )
    }

    if (this.initialShow !== null) {
      await this.initialShow
    }

    const hidden = this.hide()
    this.destroyed = true
    if (this === previousToast) {
      previousToast = null
    }
    await hidden

    this.element.remove()
  }
}

/**
 * @param {string} message The message to show in the toast
 * @param {object} options Options to the toast
 */
function toast(message, options) {
  return new Toast(message, options)
}

for (const style of ['success', 'info', 'warning', 'error']) {
  toast[style] = (message, options) => toast(message, { ...options, style })
}

export default toast
