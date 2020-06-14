<?php

namespace App\Models;

use Cache;
use InvalidArgumentException;
use Str;

/**
 * A release points to a certain commit of a package.
 * It may tagged as a stable ("0.6.2") or unstable ("1.0.4-beta.3")
 * semver release or point to a branch ("dev-master").
 *
 * @property Version $version
 */
class Release extends Helpers\MemoizingModel
{
    protected $table = 'versions';
    protected $primaryKey = null;

    protected $memoize = [
        'commit',
        'index',
        'nextStable',
        'next',
        'package',
        'previousStable',
        'previous',
        'version',
    ];
    protected $casts = ['time' => 'datetime'];

    public $incrementing = false;

    public static function find($package, string $versionConstraint): ?Release
    {
        $packageName = $package instanceof Package ? $package->name : $package;

        if (!is_string($packageName)) {
            throw new InvalidArgumentException(
                sprintf('Invalid package name "%s"', $packageName),
            );
        }

        $key = sprintf(
            'model.release.find.%s:%s',
            $packageName,
            $versionConstraint,
        );

        return Cache::rememberForever($key, function () use (
            $packageName,
            $versionConstraint
        ) {
            /**
             * @var \Illuminate\Support\Collection
             */
            $releases = static::where('name', $packageName)->get();

            $latestRelease = null;
            if (Str::startsWith($versionConstraint, '@')) {
                $latestRelease = $releases
                    ->filter(
                        fn(
                            Release $release
                        ) => $release->version->matchesMinStability(
                            substr($versionConstraint, 1),
                        ),
                    )
                    ->sort(function (Release $a, Release $b) {
                        if (!$a->version->isSemver && !$b->version->isSemver) {
                            return $a->time->getTimestamp() <=>
                                $b->time->getTimestamp();
                        } else {
                            return $a->version->sortCompare($b->version);
                        }
                    })
                    ->last();
            } else {
                $latestVersion = Version::getMaxSatisfying(
                    $releases->pluck('version'),
                    $versionConstraint,
                );

                if (!is_null($latestVersion)) {
                    $latestRelease = $releases->first(
                        fn(Release $release) => $release->version
                            ->originalString === $latestVersion->originalString,
                    );
                }
            }

            return $latestRelease;
        });
    }

    /**
     * @inheritdoc
     */
    public function is($model)
    {
        return parent::is($model) &&
            $this->attributes['name'] === $model->getAttributes()['name'] &&
            $this->attributes['version'] === $model->getAttributes()['version'];
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return "$this->name:{$this->attributes['version']}";
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'name');
    }

    public function getPackageAttribute()
    {
        static $packageCache = [];

        // Memoize package based on just the release's package name,
        // not its version.
        if (!isset($packageCache[$this->name])) {
            $packageCache[$this->name] = Package::findByName($this->name);
        }

        return $packageCache[$this->name];
    }

    public function getCommitAttribute()
    {
        return Str::substr($this->attributes['commit'], 0, 7);
    }

    public function getFullCommitAttribute()
    {
        return $this->attributes['commit'];
    }

    public function getVersionAttribute()
    {
        return new Version($this->attributes['version']);
    }

    public function getIndexAttribute()
    {
        return $this->package->releases->search(
            fn($release) => $this->is($release),
        );
    }

    public function getPreviousAttribute()
    {
        $index = $this->index;
        if ($index === 0) {
            return null;
        } else {
            return $this->package->releases->offsetGet($index - 1);
        }
    }

    public function getNextAttribute()
    {
        $index = $this->index;
        if ($index === $this->package->releases->count() - 1) {
            return null;
        } else {
            return $this->package->releases->offsetGet($index + 1);
        }
    }

    public function getPreviousStableAttribute()
    {
        $index = $this->index;
        if ($index === 0) {
            return null;
        } else {
            return $this->package->releases
                ->slice(0, $index)
                ->last(fn(Release $release) => $release->version->isStable);
        }
    }

    public function getNextStableAttribute()
    {
        $index = $this->index;
        if ($index === $this->package->releases->count() - 1) {
            return null;
        } else {
            return $this->package->releases
                ->slice($index + 1)
                ->first(fn(Release $release) => $release->version->isStable);
        }
    }
}
