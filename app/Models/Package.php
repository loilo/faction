<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Str;

/**
 * Represents a package that is part of the app's Composer repository.
 */
class Package extends Helpers\MemoizingModel
{
    protected $memoize = [
        'dependants',
        'dependencies',
        'devDependencies',
        'githubRepo',
        'githubUrl',
        'group',
        'head',
        'releases',
        'shortName',
        'url',
        'vendor',
    ];

    protected $primaryKey = 'name';
    public $incrementing = false;

    /**
     * Find a package by its identifier ("vendor/package" or just "package")
     */
    public static function findByName(string $nameOrShortName): ?Package
    {
        return parent::find(full_package_name($nameOrShortName));
    }

    /**
     * Find a package by providing a Github-style
     * repository name ("user/repo" or just "repo").
     */
    public static function findByRepo(string $repoName): ?Package
    {
        $repoNameParts = explode('/', $repoName, 2);
        $repoName = end($repoNameParts);

        return static::where('repo', $repoName)->first();
    }

    public function releases()
    {
        return $this->hasMany(Release::class, 'name');
    }

    /**
     * Releases of the package
     */
    public function getReleasesAttribute(): Collection
    {
        return Release::where('name', $this->name)
            ->get()
            ->sort(function (Release $a, Release $b) {
                if (
                    $a->version->type === 'dev' ||
                    $b->version->type === 'dev'
                ) {
                    return $a->time <=> $b->time;
                } else {
                    return $a->version->sortCompare($b->version);
                }
            });
    }

    public function findRelease(string $constraint)
    {
        return Release::find($this->name, $constraint);
    }

    /**
     * Whether the package has any stable releases
     */
    public function getHasStableReleaseAttribute(): bool
    {
        return !is_null($this->latestStableRelease);
    }

    public function getHasPreReleaseAttribute(): bool
    {
        return !is_null($this->latestRelease);
    }

    public function getLatestStableReleaseAttribute(): ?Release
    {
        return $this->findRelease('@stable');
    }

    public function getLatestReleaseAttribute(): ?Release
    {
        return $this->findRelease('@beta');
    }

    public function getHeadAttribute(): ?Release
    {
        return $this->findRelease('@dev');
    }

    public function getLastModifiedAttribute(): Carbon
    {
        return new Carbon($this->attributes['last_modified']);
    }

    public function getVendorAttribute(): ?string
    {
        return explode('/', $this->name, 2)[0] ?? null;
    }

    public function getShortNameAttribute(): ?string
    {
        return explode('/', $this->name, 2)[1] ?? null;
    }

    public function getGithubRepoAttribute(): string
    {
        $user = config('app.repository.github_org');

        if (Str::startsWith($this->source, 'git@')) {
            preg_match(
                "~^git@github\\.com:{$user}/(.+)\\.git$~",
                $this->source,
                $matches,
            );
        } else {
            preg_match(
                "~^https://github\\.com/{$user}/(.+).git$~",
                $this->source,
                $matches,
            );
        }

        return $matches[1];
    }

    public function getUrlAttribute()
    {
        return sprintf('%s/package/%s', config('app.url'), $this->shortName);
    }

    public function getGithubUrlAttribute()
    {
        $user = config('app.repository.github_org');
        return "https://github.com/{$user}/{$this->githubRepo}";
    }

    public function getHasGroupAttribute(): bool
    {
        return $this->group !== Group::getDefault();
    }

    public function getGroupAttribute(): ?Group
    {
        return Group::first(
            fn(Group $group) => Str::startsWith(
                $this->githubRepo,
                $group->prefix,
            ) || Str::startsWith($this->shortName, $group->prefix),
        ) ?? Group::getDefault();
    }

    public function allDependencies()
    {
        return $this->hasMany(Dependency::class, 'package');
    }

    public function getAllDependenciesAttribute()
    {
        return $this->getRelationValue('allDependencies')->sortBy(function (
            Dependency $dependency
        ) {
            if ($dependency->dependency === 'php') {
                return 0;
            }

            if (preg_match('/^ext-/', $dependency->dependency)) {
                return 1;
            }

            return 2;
        });
    }

    public function dependants()
    {
        return $this->hasMany(Dependency::class, 'dependency', 'name');
    }

    public function getDependenciesAttribute()
    {
        return $this->allDependencies
            ->filter(fn(Dependency $dependency) => !$dependency->dev)
            ->values();
    }

    public function getDevDependenciesAttribute()
    {
        return $this->allDependencies
            ->filter(fn(Dependency $dependency) => $dependency->dev)
            ->values();
    }

    public function getInstallCommandAttribute()
    {
        $versionSuffix = '';
        if (!$this->hasStableRelease) {
            if ($this->hasPreRelease) {
                $versionSuffix = ":^{$this->latestRelease->version}";
            } else {
                $versionSuffix = ':@dev';
            }
        }

        return "composer require {$this->name}{$versionSuffix}";
    }
}
