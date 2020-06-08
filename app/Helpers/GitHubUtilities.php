<?php

namespace App\Helpers;

use App\Models\Package;
use Cache;
use DOMDocument;
use DOMXPath;
use Github\Exception\RuntimeException;
use GitHubApi;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Str;

/**
 * Utilities to get relevant data related to GitHub repositories
 */
class GitHubUtilities
{
    /**
     * Readme fetching is relatively costly, therefore readmes are in a separate
     * cache which survives database rebuilds.
     */
    private CacheRepository $readmeCache;

    public function __construct()
    {
        $this->readmeCache = Cache::store('readme');
    }

    /**
     * Check if a package is archived
     *
     * @param Package $package  The package to check
     * @return boolean
     */
    public function isArchived(Package $package): bool
    {
        return Cache::rememberForever(
            "github.$package->shortName.archived",
            function () use ($package) {
                // Fetch `archived` state from GitHub
                return GitHubApi::repo()->show(
                    config('app.repository.github_org'),
                    $package->githubRepo,
                    null,
                    $package->commit,
                )['archived'] ?? false;
            },
        );
    }

    /**
     * Check whether the GitHub readme for a package is in cache
     *
     * @param Package $package  The package to read
     * @return bool
     */
    public function hasCachedReadme(Package $package): bool
    {
        return $this->readmeCache->has("$package->shortName.content") &&
            !empty($this->readmeCache->get("$package->shortName.content"));
    }

    /**
     * Get the GitHub readme for a package
     *
     * @param Package $package  The package to read
     * @return string
     */
    public function getReadme(Package $package): string
    {
        $commitKey = "$package->shortName.commit";
        $hashKey = "$package->shortName.hash";
        $contentKey = "$package->shortName.content";

        // Readme has already been fetched for the current commit, return cached version
        if ($this->readmeCache->get($commitKey) === $package->commit) {
            return $this->readmeCache->get($contentKey);
        }

        // Fetch readme from GitHub
        try {
            // Check if the SHA of the cached readme is still up to date
            $liveReadmeHash = GitHubApi::repo()
                ->contents()
                ->readme(
                    config('app.repository.github_org'),
                    $package->githubRepo,
                    $package->commit,
                )['sha'];

            // If the hash is unchanged, mark the commit as checked
            // and return the cached readme
            if ($this->readmeCache->get($hashKey) === $liveReadmeHash) {
                $this->readmeCache->set($commitKey, $package->commit);
                return $this->readmeCache->get($contentKey);
            }

            $readmeHtml =
                GitHubApi::repo()
                    ->contents()
                    ->configure('html')
                    ->readme(
                        config('app.repository.github_org'),
                        $package->githubRepo,
                        $package->commit,
                    ) ?? '';
        } catch (RuntimeException $e) {
            // 404 means that the repo has no readme and we can safely cache
            // an empty string - all other errors should be re-thrown
            if ($e->getCode() === 404) {
                $readmeHtml = '';
            } else {
                throw $e;
            }
        }

        // Process readme HTML
        if (!empty($readmeHtml)) {
            $readmeHtml = $this->processPackageReadme($package, $readmeHtml);
        }

        $this->readmeCache->set($commitKey, $package->commit);
        $this->readmeCache->set($hashKey, $liveReadmeHash ?? '');
        $this->readmeCache->set($contentKey, $readmeHtml);

        return $readmeHtml;
    }

    /**
     * Optimize HTML code of a readme for usage in this app
     *
     * @param Package $package The package the processed code belongs to
     * @param string  $html    The readme HTML code to optimize
     */
    protected function processPackageReadme(
        Package $package,
        string $html
    ): string {
        // Parse HTML
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        @$doc->loadHTML(
            '<?xml encoding="utf-8" ?>' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        $xpath = new DOMXPath($doc);

        // Replace "user-content-" anchor IDs
        $headlineLinks = $xpath->query(
            '//a[starts-with(@id, "user-content-")]',
        );
        foreach ($headlineLinks as $link) {
            $link->setAttribute(
                'id',
                Str::substr($link->getAttribute('id'), 13),
            );
        }

        // Add "empty-link" class to links not containing text (useful for removing weird hover effect on image-only links)
        $links = $xpath->query('//a');
        foreach ($links as $link) {
            $trimmedContent = trim($link->textContent);

            if (!empty($trimmedContent)) {
                continue;
            }

            $class = $link->hasAttribute('class')
                ? $link->getAttribute('class')
                : '';

            $class .= ' empty-link';
            $link->setAttribute('class', $class);
        }

        // Replace internal GitHub links
        $links = $xpath->query('//a');
        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            if (
                !preg_match('@^https?://@', $href) &&
                !Str::startsWith($href, '#')
            ) {
                $link->setAttribute(
                    'href',
                    $package->githubUrl . '/blob/master/' . $href,
                );
            }
        }

        // Replace links to GitHub repos of internal packages
        $links = $xpath->query('//a');
        $repoPackageMap = Package::all()
            ->pluck('github_repo')
            ->mapWithKeys(
                fn($repoName) => [
                    sprintf(
                        'https://github.com/%s/%s',
                        config('app.repository.github_org'),
                        $repoName,
                    ) => Package::findByRepo(
                        sprintf(
                            '%s/%s',
                            config('app.repository.github_org'),
                            $repoName,
                        ),
                    )->shortName,
                ],
            );

        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            if ($repoPackageMap->has($href)) {
                $link->setAttribute(
                    'href',
                    sprintf(
                        '%s/package/%s',
                        config('app.url'),
                        $repoPackageMap->get($href),
                    ),
                );
            } else {
                // Replace links with hashes
                foreach ($repoPackageMap as $repoLink => $packageName) {
                    if (Str::startsWith($href, $repoLink . '#')) {
                        $link->setAttribute(
                            'href',
                            sprintf(
                                '%s/package/%s#%s',
                                config('app.url'),
                                $packageName,
                                explode('#', $href, 2)[1],
                            ),
                        );
                        break;
                    }
                }
            }
        }

        // Download and replace internal GitHub <img> sources
        $images = $xpath->query('//img');
        $contents = GitHubApi::repo()->contents();

        foreach ($images as $image) {
            $src = $image->getAttribute('src');

            // Ignore external images
            if (preg_match('@^https?://@', $src)) {
                continue;
            }

            // Download the image
            $img = $contents->download(
                config('app.repository.github_org'),
                $package->githubRepo,
                $src,
                $package->commit,
            );

            // Ignore image if it couldn't be downloaded
            if (is_null($img)) {
                continue;
            }

            // Read mime type from image
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($img);

            // SVG mime type is usually not correctly detected
            if ($mime === 'image/svg' || $mime === 'text/plain') {
                $mime = 'image/svg+xml';
            }

            // Use downloaded image as data URL
            $image->setAttribute(
                'src',
                sprintf('data:%s;base64,%s', $mime, base64_encode($img)),
            );
        }

        $processedReadme = trim($doc->saveHTML());

        // Remove XML prefix
        $xmlPrefix = '<?xml encoding="utf-8" ?>';
        if (Str::startsWith($processedReadme, $xmlPrefix)) {
            $processedReadme = Str::substr(
                $processedReadme,
                Str::length($xmlPrefix),
            );
        }

        return $processedReadme;
    }
}
