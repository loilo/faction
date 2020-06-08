import Fuse from 'fuse.js'

let fuse

/**
 * Provide the search with necessary data
 *
 * @param {string[]} packages The short (vendor-stripped) names of all packages
 * @param {object[]} groups   The defined package groups
 */
export async function feed(packages, groups = []) {
  if (fuse) return

  const prefixes = groups.map(group => group.prefix)

  fuse = new Fuse(
    packages.map(name => {
      const appliedPrefix = prefixes.find(prefix => name.startsWith(prefix))

      // The concise name is the package's short name with its group's prefix
      // stripped away. For example a package named "acme-foo-package" with the
      // group prefix "acme-" will have the concise name "foo-package".
      // This allows proper search result ranking when searching only for "foo"
      // as the match between "foo" and "foo-package" is naturally much stronger
      // than the match between "foo" and "acme-foo-package".
      const conciseName = appliedPrefix
        ? name.substr(appliedPrefix.length)
        : name

      return { name, conciseName }
    }),
    {
      keys: ['name', 'conciseName'],
      shouldSort: true,
      threshold: 0.4,
      tokenize: true,
      matchAllTokens: true,
    },
  )
}

/**
 * Query the search engine
 *
 * @param {string} query The keyword(s) to search for
 */
export async function query(query) {
  if (!fuse) {
    throw new Error(
      'Cannot search if index is not initialized yet. Send a "feed" command to the search worker first.',
    )
  }

  return fuse.search(query).map(item => item.name)
}
