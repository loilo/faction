<?php

namespace App\Library\Satis;

use Github\ResultPager;
use GitHubApi;
use Log;

/**
 * Find certain packages inside a GitHub user's repositories
 */
class RepoScanner
{
    private string $user;
    private string $vendor;
    private string $token;

    public function __construct()
    {
        $this->user = config('app.repository.github_org');
        $this->token = config('app.repository.github_token');
        $this->vendor = config('app.repository.package_vendor');
    }

    /**
     * Get the data from the provided repo's composer.json
     * or false if none is found.
     *
     * @param string $repo The GitHub repository to scan
     * @return array|bool
     */
    public function containedPackage(string $repo)
    {
        // Get file list of the repo
        $contents = GitHubApi::repo()->contents();
        try {
            $files = $contents->show($this->user, $repo);
        } catch (\RuntimeException $e) {
            // Empty repository
            if ($e->getCode() === 404) {
                $files = [];
            } else {
                throw $e;
            }
        }

        // Check if the repo contains a package (= any of the files is a composer.json)
        $isPackage = false;
        foreach ($files as $file) {
            if ($file['name'] === 'composer.json') {
                $isPackage = true;
                break;
            }
        }
        if (!$isPackage) {
            return false;
        }

        // Read the composer.json
        $composerJson = GitHubApi::repo()
            ->contents()
            ->show($this->user, $repo, 'composer.json');
        $composerContents = json_decode(
            base64_decode($composerJson['content']),
            true,
        );

        return $composerContents;
    }

    /**
     * Find all packages of the GitHub user
     *
     * @return PackageRepoMap
     */
    public function findAll(): PackageRepoMap
    {
        Log::info('Fetching repository list', ['user' => $this->user]);

        // Get all user repos
        $userDetails = GitHubApi::user()->show($this->user);
        if ($userDetails['type'] === 'Organization') {
            $targetApi = 'organization';
        } else {
            try {
                $currentUser = GitHubApi::currentUser()->show();

                if ($currentUser['login'] === $this->user) {
                    $targetApi = 'currentUser';
                } else {
                    $targetApi = 'user';
                }
            } catch (\Exception $_e) {
                $targetApi = 'user';
            }
        }

        $paginator = new ResultPager(GitHubApi::connection());
        if ($targetApi === 'user' || $targetApi === 'currentUser') {
            $userApi = GitHubApi::api($targetApi);
            $repositories = $paginator->fetchAll($userApi, 'repositories', [
                $this->user,
            ]);
            $repositories = array_filter($repositories, function ($repo) {
                return $repo['owner']['login'] === $this->user;
            });
        } else {
            $organizationApi = GitHubApi::organization();
            $repositories = $paginator->fetchAll(
                $organizationApi,
                'repositories',
                [$this->user],
            );
        }

        Log::info('Check repositories for composer packages', [
            'repositories' => sizeof($repositories),
        ]);

        // The list we'll add data to
        // Will contain associative arrays with:
        // `package`: The package name the repo has (`null` if it's not a package)
        // `repoName`: The full name of the repo (i.e. user/repo)
        // `sshUrl`: The SSH URL to clone the repo
        // `pushedAt`: When the last change has been made to the repo (important for correctly caching it)
        $repoMetaData = [];

        // Iterate over found repos
        foreach ($repositories as $repo) {
            Log::debug('Scanning repository for a package', [
                'repository' => $repo['full_name'],
            ]);

            $sshUrl = $repo['ssh_url'];

            $package = $this->containedPackage($repo['name']);
            $isProject =
                isset($package['type']) && $package['type'] === 'project';

            // If it's not a package, add meta data to the list and skip to next repo
            if (!$package || $isProject) {
                $repoMetaData[] = [
                    'package' => null,
                    'repoName' => $repo['full_name'],
                    'sshUrl' => $sshUrl,
                    'pushedAt' => $repo['pushed_at'],
                ];

                Log::debug('Successfully scanned repository', [
                    'repository' => $repo['full_name'],
                    'package' => null,
                ]);
                continue;
            }

            // Store repo info in the list
            if (
                isset($package['name']) &&
                substr($package['name'], 0, strlen($this->vendor) + 1) ===
                    $this->vendor . '/'
            ) {
                // Package matches the given vendor
                $repoMetaData[] = [
                    'package' => substr(
                        $package['name'],
                        strlen($this->vendor) + 1,
                    ),
                    'repoName' => $repo['full_name'],
                    'sshUrl' => $sshUrl,
                    'pushedAt' => $repo['pushed_at'],
                ];
            } else {
                // Package doesn't match the given vendor
                $repoMetaData[] = [
                    'package' => null,
                    'repoName' => $repo['full_name'],
                    'sshUrl' => $sshUrl,
                    'pushedAt' => $repo['pushed_at'],
                ];
            }

            Log::debug('Successfully scanned repository', [
                'repository' => $repo['full_name'],
                'package' => $package['name'] ?? null,
            ]);
        }

        $packageMetaData = array_filter($repoMetaData, function ($meta) {
            return !is_null($meta['package']);
        });

        $packages = array_map(function ($meta) {
            return $this->vendor . '/' . $meta['package'];
        }, $packageMetaData);

        $repos = array_map(function ($meta) {
            return $meta['sshUrl'];
        }, $packageMetaData);

        return new PackageRepoMap(array_combine($packages, $repos));
    }
}
