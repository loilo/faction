<?php

namespace App\Library\Satis;

/**
 * A map of packages to GitHub repositories, basically a wrapper around
 * an associative array of the form:
 * [ 'vendor/package-name' => 'user/repo-name' ]
 */
class PackageRepoMap
{
    /**
     * The contained packages of the form
     * [ 'vendor/package-name' => 'git@github.com:user/repo-name.git' ]
     */
    protected array $map = [];

    /**
     * Create a new package collection
     *
     * @param array $map Associative array of packages mapped to their repositories
     */
    public function __construct(array $map = [])
    {
        $this->map = array_combine(
            array_keys($map),
            array_map(function ($repoName) {
                return $this::getRepoAddress($repoName);
            }, array_values($map)),
        );
    }

    /**
     * Check if the map includes the given repo
     *
     * @param string $repoName The name of the repository to search for
     */
    public function containsRepo(string $repoName): bool
    {
        return array_search(static::getRepoAddress($repoName), $this->map) !==
            false;
    }

    /**
     * Check if the map includes the given package
     *
     * @param string $packageName The name of the package to search for
     */
    public function containsPackage(string $packageName): bool
    {
        return array_key_exists($packageName, $this->map);
    }

    /**
     * Get the repository name for a given package
     *
     * @param string $packageName The name of the package whose corresponding repository should be found
     */
    public function getCorrespondingRepo(string $packageName): string
    {
        return $this->map[$packageName];
    }

    /**
     * Get the package name for a given repository
     *
     * @param string $repoName The name of the repository whose corresponding package should be found
     * @return string|bool
     */
    public function getCorrespondingPackage(string $repoName)
    {
        return array_search(static::getRepoAddress($repoName), $this->map);
    }

    /**
     * Performs a before/after diff with $this map as current and the provided value as future state
     *
     * @param PackageRepoMap|array $mapOrArray The map to diff against
     * @return array An associative array providing lists of package/repo pairs at different keys:
     * - changed: packages that exist in both maps but moved to another repo
     * - added: packages that don't exist in $this map but do exist in the diffed map
     * - removed: packages that do exist in $this map but don't exist in the diffed map
     * - touched: packages that have been changed, added or removed
     * - untouched: packages that have neither been changed nor added or removed
     */
    public function diff($mapOrArray): array
    {
        $arr1 = $this->toArray();
        $arr2 = is_array($mapOrArray) ? $mapOrArray : $mapOrArray->toArray();

        $changed = array_intersect_key(array_diff($arr1, $arr2), $arr2);
        $added = array_diff_key($arr2, $arr1);
        $removed = array_diff_key($arr1, $arr2);
        $touched = array_merge($added, $removed, $changed);
        $untouched = array_diff_key($arr1, $touched);

        return [
            'untouched' => $untouched,
            'touched' => $touched,

            'changed' => $changed,
            'added' => $added,
            'removed' => $removed,
        ];
    }

    /**
     * Get this map as an associative array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->map;
    }

    /**
     * Get this map a plain packages array
     *
     * @return array
     */
    public function toPackagesArray(): array
    {
        return array_keys($this->map);
    }

    /**
     * Get this map a plain repos array
     *
     * @return array
     */
    public function toReposArray(): array
    {
        return array_values($this->map);
    }

    /**
     * Generate the "require" part of a satis config from the map's packages
     *
     * @return array|object An associative array mapping package names to "*" (stands for "all versions"). If the map is empty, a `stdClass` instance with no properties will be returned (for proper JSON serialization).
     */
    public function generateSatisConfigRequire()
    {
        $packages = array_fill_keys(array_keys($this->map), '*');
        if (sizeof($packages)) {
            return $packages;
        } else {
            return new \stdClass();
        }
    }

    /**
     * Verifies if a string is a valid GitHub repository identifier
     *
     * @param string $repositoryIdentifier The repository identifier to validate
     */
    public static function validateRepositoryIdentifier(
        string $repositoryIdentifier
    ): bool {
        return (bool) preg_match(
            '@^[a-zA-Z0-9._-]+/[a-zA-Z0-9._-]+$@',
            $repositoryIdentifier,
        );
    }

    /**
     * Idempotently expands a repository identifier (e.g. vendor/repository) to a valid (GitHub) remote address
     *
     * @param string $repoIdentifier The repository identifier
     * @return string The remote address
     *
     * @throws \InvalidArgumentException If the $repoIdentifier is not of a valid format
     */
    public static function getRepoAddress(string $repoIdentifier): string
    {
        // Return $repoIdentifier if it contains an "@" or is a valid URL
        if (
            (bool) filter_var($repoIdentifier, FILTER_VALIDATE_URL) ||
            strpos($repoIdentifier, '@') !== false
        ) {
            return $repoIdentifier;

            // Otherwise, use the default GitHub SSH address
        } elseif (static::validateRepositoryIdentifier($repoIdentifier)) {
            return 'git@github.com:' . $repoIdentifier . '.git';
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'The provided string "%s" is neither a valid URL nor a valid SSH address or repository identifier.',
                    $repoIdentifier,
                ),
            );
        }
    }

    /**
     * Idempotently shrinks a GitHub repository address down to the bare repository identifier (e.g. vendor/repository)
     *
     * @param string $repoAddress The remote address
     * @return string The repository identifier
     *
     * @throws \InvalidArgumentException If the $repoAddress is not of a valid format
     */
    public static function extractRepoIdentifier(string $repoAddress): string
    {
        if (preg_match('/^git@github\\.com:.+\\.git$/', $repoAddress)) {
            return substr($repoAddress, 15, -4);
        } elseif (
            preg_match('@^https://github\\.com/.+\\.git$@', $repoAddress)
        ) {
            return substr($repoAddress, 19, -4);
        } elseif (static::validateRepositoryIdentifier($repoAddress)) {
            return $repoAddress;
        } else {
            throw new \InvalidArgumentException(
                sprintf('Invalid repository address: "%s"', $repoAddress),
            );
        }
    }

    /**
     * From a repository identifier or remote address, only get the bare name (without the vendor)
     *
     * @param string $repoIdentifier The repository identifier or remote address
     * @return string The repository name
     */
    public static function nameOnly(string $repoIdentifier): string
    {
        return explode(
            '/',
            static::extractRepoIdentifier($repoIdentifier),
            2,
        )[1];
    }

    /**
     * For a Satis configuration, generate the "repositories" object
     * @see https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#setup
     *
     * @return array
     */
    public function generateSatisConfigRepositories(): array
    {
        return array_map(
            function ($_package, $repo) {
                return [
                    'type' => 'vcs',
                    'url' => static::getRepoAddress($repo),
                ];
            },
            array_keys($this->map),
            array_values($this->map),
        );
    }

    /**
     * Set a package-repository mapping in the map
     *
     * @param string $package The name of the package
     * @param string $repository The name of the repository
     * @return void
     */
    public function set(string $package, string $repository): void
    {
        $this->map[$package] = static::getRepoAddress($repository);
    }

    /**
     * Removes a package from the map
     *
     * @param string $package The name of the package to be removed
     * @return boolean True, if a package of the given name was contained in the map, false otherwise
     */
    public function remove(string $package): bool
    {
        $exists = $this->containsPackage($package);
        unset($this->map[$package]);
        return $exists;
    }

    /**
     * The number of packages contained in the map
     *
     * @return integer
     */
    public function count(): int
    {
        return sizeof($this->map);
    }
}
