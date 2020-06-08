import { Controller } from 'stimulus'
import SearchOpenEvent from '../search/event.search-open'
import SearchResultsEvent from '../search/event.search-results'
import Search from '../search/search.worker'
import toast from '../helpers/toast'
import { modifierKeyPressed, requestIdleCallback } from '../helpers/util'

/**
 * Control behavior of the search bar
 */
export default class SearchController extends Controller {
  static targets = ['input']

  /**
   * @type {boolean} Whether package data has already been delivered to the worker
   */
  initialized = false

  /**
   * @type {boolean} Whether a "not initialized" warning toast has been delivered during this request
   */
  hasShownInitializationWarning = false

  /**
   * @type {number} Search counter which serves as an identifier for each search
   */
  searchCounter = 0

  /**
   * @type {string[]} The packages names of the latest search results
   */
  latestResults = []

  /**
   * @inheritdoc
   */
  initialize() {
    this.focusSearch = this.focusSearch.bind(this)
    this.setSearchValue = this.setSearchValue.bind(this)

    // Initialize search as soon as there are CPU capacities
    this.initializeHandle = requestIdleCallback(
      this.initializeSearch.bind(this),
      { timeout: 1000 },
    )
  }

  /**
   * Initialize the search worker
   */
  async initializeSearch() {
    this.search = Search()

    try {
      // Pass short package names and package groups to the worker
      await this.search.feed(
        window.packages.map(pkg => pkg.split('/', 2).pop()),
        window.groups,
      )

      this.initialized = true

      // If an initialization warning has been shown before,
      // also announce the search to being available now
      if (this.hasShownInitializationWarning) {
        toast(window.lang.messages.searchReady)
      }
    } catch (err) {
      toast.error(window.lang.error.search.initializationError, {
        timeout: 6000,
      })
      console.error(err)
    }
  }

  /**
   * Focus search bar on (letter) key press, Windows Start Menu style
   *
   * @param {KeyboardEvent} event
   */
  initializeSearchHandler(event) {
    if (
      !event.ctrlKey &&
      !event.shiftKey &&
      !event.metaKey &&
      !event.altKey &&
      /^[a-z]$/.test(event.key) &&
      !['INPUT', 'SELECT', 'TEXTAREA'].includes(document.activeElement.nodeName)
    ) {
      this.focusSearch()
    }
  }

  /**
   * Focus the search field
   */
  focusSearch() {
    this.inputTarget.value = ''
    this.inputTarget.focus({ preventScroll: true })
    this.inputTarget.dispatchEvent(new Event('focus'))
  }

  /**
   * Set the search field value
   *
   * @param {string} value
   */
  setSearchValue(value) {
    this.inputTarget.value = value
  }

  /**
   * Catch the Cmd/Ctrl + F shortcut to focus the search field
   *
   * @param {KeyboardEvent} event
   */
  shortcutHandler(event) {
    if (modifierKeyPressed(event) && event.key === 'f') {
      event.preventDefault()
      this.inputTarget.focus({ preventScroll: true })
      this.inputTarget.dispatchEvent(new Event('focus'))
      this.inputTarget.select()
    }
  }

  /**
   * Reset results view and leave the search field when pressing the Escape key
   *
   * @param {KeyboardEvent} event
   */
  leave(event) {
    if (event.key === 'Escape') {
      this.inputTarget.blur()
      this.query({ currentTarget: { value: '' } })
    }
  }

  /**
   * Instruct the app to open a certain package's details page
   *
   * @param {SubmitEvent} event
   */
  open(event) {
    event.preventDefault()

    if (this.latestResults.length > 0) {
      window.dispatchEvent(new SearchOpenEvent(this.latestResults[0]))
    }
  }

  /**
   * Scroll to the top of the page
   */
  scrollToTop() {
    if (window.scrollY > 0) {
      window.scrollTo({
        top: 0,

        // If distance is too far, smooth scrolling takes too long
        // for an action that should feel instant
        behavior: window.scrollY < 150 ? 'smooth' : 'auto',
      })
    }
  }

  /**
   * @type {string|null} Holds the previously sent search query
   */
  previousQuery = null

  /**
   * Query the search worker
   *
   * @param {Inputevent} event
   */
  async query(event) {
    const query = event.currentTarget.value.trim()
    if (query === this.previousQuery) return

    this.reflectQueryInUrl(query)

    this.searchCounter++
    const counterOnStart = this.searchCounter

    if (!this.initialized) {
      // Not initialized, cannot search, inform user
      if (!this.hasShownInitializationWarning) {
        toast.warning(window.lang.error.search.notReady, {
          timeout: 4000,
        })

        this.hasShownInitializationWarning = true
      }
    } else if (query.length === 0) {
      // Empty search query provided, equals not searching at all
      window.dispatchEvent(new SearchResultsEvent(null))
    } else {
      // Send query to the worker
      const results = await this.search.query(query)

      // Make sure to always only show results of the latest search request
      if (this.searchCounter === counterOnStart) {
        this.latestResults = results

        window.dispatchEvent(new SearchResultsEvent(results))
      }
    }
  }

  /**
   * Write a search query to the URL's query parameter
   * This enables server-side search on page refresh
   *
   * @param {string} query The search query
   */
  reflectQueryInUrl(query) {
    const params = new URLSearchParams(window.location.search)
    const isInSearchMode = params.has('search')

    if (query.length > 0) {
      params.set('search', query)
    } else {
      params.delete('search')
    }

    let paramsString = String(params)
    if (paramsString.length > 0) {
      paramsString = `?${paramsString}`
    }

    const newUrl = window.location.pathname + paramsString

    // Avoid pushing a new URL to the history stack for each typed character
    if (isInSearchMode) {
      window.history.replaceState({ turbolinks: {} }, null, newUrl)
    } else {
      window.history.pushState({ turbolinks: {} }, null, newUrl)
    }
  }
}
