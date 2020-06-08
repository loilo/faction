/**
 * An event being dispatched when new seach results are available
 */
export default class SearchResultsEvent extends CustomEvent {
  constructor(results) {
    super('search:results', {
      detail: results,
    })
  }

  /**
   * Search results
   *
   * @returns {string[]|null}
   */
  get results() {
    return this.detail
  }
}
