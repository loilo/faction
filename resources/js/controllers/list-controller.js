import { Controller } from 'stimulus'
import {
  cancelIdleCallback,
  copy,
  modifierKeyPressed,
  requestIdleCallback,
  key,
} from '../helpers/util'
import toast from '../helpers/toast'

/**
 * @typedef {import('./search-controller').SearchOpenEvent} SearchOpenEvent
 * @typedef {import('./search-controller').SearchResultsEvent} SearchResultsEvent
 */

/**
 * Control behavior of the package list
 */
export default class ListController extends Controller {
  static targets = [
    'filterStyle',
    'packageList',
    'package',
    'packageLink',
    'searchInput',
    'resultsNum',
  ]
  tabIndexHandle = -1

  /**
   * @inheritdoc
   */
  initialize() {
    // Bind context of methods
    this.searchResultsHandler = this.searchResultsHandler.bind(this)
    this.open = this.open.bind(this)
  }

  /**
   * @inheritdoc
   */
  connect() {
    window.addEventListener('search:results', this.searchResultsHandler)
    window.addEventListener('search:open', this.open)

    // Store unfiltered targets order to be able to restore on search abort
    this.unfilteredTargetsOrder = this.packageTargets
    this.sortedPackageTargets = this.packageTargets

    // If server-side search was performed, unfiltered targets cannot be
    // inferred from the DOM (as it is in filtered, non-canonical order).
    // In that case, the original package order is available in the global
    // `unsortedPackageNames` variable, so we infer original order from there.
    if ('unsortedPackageNames' in window) {
      this.tabIndexHandle = requestIdleCallback(
        () => {
          const unsortedPackageNames = window.unsortedPackageNames

          this.unfilteredTargetsOrder = [...this.packageTargets].sort(
            (a, b) =>
              unsortedPackageNames.indexOf(a.dataset.package) -
              unsortedPackageNames.indexOf(b.dataset.package),
          )
        },
        {
          timeout: 100,
        },
      )
    }
  }

  /**
   * @inheritdoc
   */
  disconnect() {
    window.removeEventListener('search:results', this.searchResultsHandler)
    window.removeEventListener('search:open', this.open)
  }

  /**
   * Navigate to a package's details page
   *
   * @param {SearchOpenEvent} event
   */
  open(event) {
    /**
     * @type {string}
     */
    const pkg = event.detail

    const packageTarget = this.packageTargets.find(
      target => target.dataset.package === pkg,
    )
    if (packageTarget) {
      packageTarget.querySelector('a').click()
    }
  }

  /**
   * Focus the first item in the package list
   *
   * @param {KeyboardEvent} event
   */
  focusFirstPackage(event) {
    if (event.key === 'ArrowDown' && this.sortedPackageTargets.length > 0) {
      event.preventDefault()

      key.markPressed()
      const link = this.sortedPackageTargets[0].querySelector('a')
      link.focus({ preventScroll: true })
      link.dispatchEvent(new Event('focusin', { target: link }))
    }
  }

  /**
   * Get the number of columns in the list view
   *
   * @returns {number}
   */
  determineColumns() {
    if (this.sortedPackageTargets.length === 0) return 0
    if (this.sortedPackageTargets.length === 1) return 1

    const firstOffset = this.sortedPackageTargets[0].getBoundingClientRect()
      .left

    let columns = 1
    while (columns < this.sortedPackageTargets.length) {
      if (
        firstOffset ===
        this.sortedPackageTargets[columns].getBoundingClientRect().left
      ) {
        return columns
      }

      columns++
    }

    return columns
  }

  /**
   * Handle keyboard navigation on list items
   *
   * @param {KeyboardEvent} event
   */
  navigate(event) {
    if (this.sortedPackageTargets.length === 0) return

    let columns
    switch (event.key) {
      case 'ArrowDown':
      case 'ArrowUp':
      case 'ArrowLeft':
      case 'ArrowRight':
        columns = this.determineColumns()
        break

      case 'Home':
      case 'End':
      case 'Escape':
        break

      case 'c':
        if (!modifierKeyPressed(event)) return
        break

      default:
        return
    }

    const activeElement = document.activeElement

    const index = this.sortedPackageTargets.indexOf(
      activeElement.closest('.package'),
    )

    // Abort if no list item is currently focused
    if (index === -1) return

    const meta = modifierKeyPressed(event)
    const currentColumn = (index % columns) + 1
    const lastRowItems = this.sortedPackageTargets.length % columns

    let target
    switch (event.key) {
      case 'c':
        copy(
          `composer require ${document.activeElement.parentNode.dataset.fullPackage}`,
        )

        toast(window.lang.messages.copiedInstallCommand)

        event.preventDefault()
        return

      case 'Escape':
        document.activeElement.blur()
        return

      case 'Home':
        target = this.sortedPackageTargets[0]
        break

      case 'End':
        target = this.sortedPackageTargets[this.sortedPackageTargets.length - 1]
        break

      case 'ArrowDown':
        if (meta) {
          let newIndex =
            this.sortedPackageTargets.length - lastRowItems + currentColumn - 1

          // If last row has fewer items than current column number,
          // stop at second-last row
          if (lastRowItems < currentColumn) {
            newIndex -= columns
          }

          target = this.sortedPackageTargets[newIndex]
        } else {
          target = this.sortedPackageTargets[index + columns]
        }
        break

      case 'ArrowUp':
        if (meta) {
          target = this.sortedPackageTargets[currentColumn - 1]
        } else {
          target = this.sortedPackageTargets[index - columns]

          if (!target) {
            target = this.searchInputTarget
          }
        }
        break

      case 'ArrowLeft':
        if (meta) {
          target = this.sortedPackageTargets[index - (currentColumn - 1)]
        } else if (index % columns !== 0) {
          target = this.sortedPackageTargets[index - 1]
        }
        break

      case 'ArrowRight':
        if (meta) {
          target = this.sortedPackageTargets[
            Math.min(
              index - currentColumn + columns,
              this.sortedPackageTargets.length - 1,
            )
          ]
        } else if (index % columns !== columns - 1) {
          target = this.sortedPackageTargets[index + 1]
        }
        break
    }

    if (target) {
      if (this.sortedPackageTargets.includes(target)) {
        key.markPressed()
        const link = target.querySelector('a')
        link.focus({ preventScroll: true })
        link.dispatchEvent(new Event('focusin', { target: link }))

        target.scrollIntoView({
          behavior: 'smooth',
          block: 'center',
        })
      } else {
        // Target is search field
        target.focus({ preventScroll: true })
        target.dispatchEvent(new Event('focus'))
        target.select()
      }
    }

    event.preventDefault()
  }

  /**
   * Handle incoming search results
   *
   * @param {SearchResultsEvent} event
   */
  searchResultsHandler(event) {
    const results = event.results

    cancelIdleCallback(this.tabIndexHandle)

    if (results === null) {
      this.packageListTarget.classList.remove('packages-list--empty')
      this.filterStyleTarget.innerHTML = ''
      this.resultsNumTarget.innerHTML = ''

      // Reset package DOM order
      this.sortedPackageTargets = this.unfilteredTargetsOrder
      this.packageListTarget.append(...this.sortedPackageTargets)
    } else {
      this.packageListTarget.classList.toggle(
        'packages-list--empty',
        results.length === 0,
      )

      let visibilityRule = ''
      let orderRules = ''
      if (results.length > 0) {
        const selector = results
          .map(result => `.package[data-package="${result}"]`)
          .join(',\n')

        visibilityRule = `${selector} { display: block; }`

        // Packages are sorted only visually, by setting the "order" property.
        // This is significantly more performand than rearranging the actual DOM.
        // The DOM will be rearranged anyway (to retain tabbing order), but this
        // will happen in a separate step, when the browser signals that it has
        // CPU capabilities to spare.
        orderRules = results
          .map(
            (result, index) =>
              `.package[data-package="${result}"] { order: ${index + 1}; }`,
          )
          .join('\n')
      }

      this.filterStyleTarget.innerHTML = `
        .package { display: none; }
        ${visibilityRule}
        ${orderRules}
        `

      switch (results.length) {
        case 0:
          this.resultsNumTarget.innerHTML = ''
          break

        case 1:
          this.resultsNumTarget.innerHTML = `1 ${window.lang.results.singular}`
          break

        default:
          this.resultsNumTarget.innerHTML = `${results.length} ${window.lang.results.plural}`
      }

      // When CPU cycles are available, sort elements in the actual DOM
      // to retain tab order
      // Note that visual and correct arrow key navigation order are
      // already functional without this.
      this.tabIndexHandle = requestIdleCallback(
        () => {
          this.setSortedPackageTargets(results)
          this.packageListTarget.append(...this.sortedPackageTargets)
        },
        { timeout: 100 },
      )
    }
  }

  /**
   * Sort `sortedPackageTargets` to match the list of provided package names
   *
   * @param {string[]} results
   */
  setSortedPackageTargets(results) {
    this.sortedPackageTargets = this.packageTargets
      .filter(target => results.includes(target.dataset.package))
      .sort(
        (a, b) =>
          results.indexOf(a.dataset.package) -
          results.indexOf(b.dataset.package),
      )
  }
}
