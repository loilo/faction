import { Controller } from 'stimulus'
import toast from '../helpers/toast'
import {
  copy,
  prefetchOnInteraction,
  requestIdleCallback,
} from '../helpers/util'

/**
 * Manage app-wide actions
 */
export default class AppController extends Controller {
  static targets = ['container']

  /**
   * @inheritdoc
   */
  connect() {
    // Display certain JS-only events
    this.containerTarget.classList.remove('no-js')

    // Enable on-contact prefetching of all elements
    // with a [data-prefetch] attribute
    requestIdleCallback(() => {
      for (const prefetchingElement of document.querySelectorAll(
        '[data-prefetch]',
      )) {
        const duration =
          prefetchingElement.dataset.prefetch.length > 0
            ? Number(prefetchingElement.dataset.prefetch)
            : undefined

        prefetchOnInteraction(prefetchingElement, duration)
      }
    })
  }

  /**
   * Copy the install command for a package to the user clipboard
   *
   * @param {Event} event
   */
  copyInstallCommand(event) {
    copy(event.currentTarget.dataset.copy)

    toast(window.lang.messages.copiedInstallCommand)
  }

  /**
   * Select the contents of the clicked element
   *
   * @param {MouseEvent} event
   */
  selectOnClick(event) {
    const range = document.createRange()
    range.selectNode(event.currentTarget)
    window.getSelection().removeAllRanges()
    window.getSelection().addRange(range)
  }
}
