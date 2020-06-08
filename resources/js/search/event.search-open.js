/**
 * An event being dispatched by the search controller when a resulting package's
 * details page should directly be navigated to
 */
export default class SearchOpenEvent extends CustomEvent {
  /**
   * @param {string} packageName The package to open (short name)
   */
  constructor(packageName) {
    super('search:open', {
      detail: packageName,
    })
  }

  /**
   * The the package (short) name to open
   *
   * @returns {string}
   */
  get package() {
    return this.detail
  }
}
