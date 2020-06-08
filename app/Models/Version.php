<?php

namespace App\Models;

use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use Illuminate\Support\Collection;
use VersionParser;

/**
 * A version represents a tag on a release while offering utilities to deal
 * with typical Composer-related tags (such as "dev-*" or semver version tags).
 */
class Version extends Helpers\MemoizingOfflineModel
{
    protected $memoize = [
        'isMajor',
        'isMinor',
        'isPatch',
        'isSemver',
        'ref',
        'type',
    ];

    private $originalString;
    private $version;
    private $normalized;
    private $patch;
    private $minor;
    private $major;
    private $suffix;

    public static function wrap($version)
    {
        if ($version instanceof self) {
            return $version;
        } else {
            return new self($version);
        }
    }

    public function __construct(string $version)
    {
        $this->originalString = $version;
        $this->version = ltrim($version, 'v');

        try {
            $this->normalized = VersionParser::normalize($this->version);

            if (
                preg_match(
                    '/^([0-9]+)\\.([0-9]+)\\.([0-9]+)\\.([0-9]+)(-[a-z0-9._-]+)?$/i',
                    $this->normalized,
                    $matches,
                )
            ) {
                $this->patch = (int) $matches[3];
                $this->minor = (int) $matches[2];
                $this->major = (int) $matches[1];
                $this->suffix = isset($matches[5])
                    ? substr($matches[5], 1)
                    : null;
            }
        } catch (\UnexpectedValueException $_e) {
        }
    }

    public function getTypeAttribute()
    {
        if ($this->isPatch) {
            return 'patch';
        } elseif ($this->isMinor) {
            return 'minor';
        } elseif ($this->isMajor) {
            return 'major';
        } elseif (!empty($this->suffix)) {
            return 'prerelease';
        } else {
            return 'dev';
        }
    }

    public function getPatchAttribute()
    {
        return $this->patch;
    }

    public function getMinorAttribute()
    {
        return $this->minor;
    }

    public function getMajorAttribute()
    {
        return $this->major;
    }

    public function getIsPatchAttribute()
    {
        return is_int($this->patch) &&
            $this->patch !== 0 &&
            is_null($this->suffix);
    }

    public function getIsMinorAttribute()
    {
        return $this->patch === 0 &&
            $this->minor !== 0 &&
            is_null($this->suffix);
    }

    public function getIsMajorAttribute()
    {
        return $this->patch === 0 &&
            $this->minor === 0 &&
            $this->major !== 0 &&
            is_null($this->suffix);
    }

    public function getNormalizedAttribute()
    {
        return $this->normalized;
    }

    public function getOriginalStringAttribute()
    {
        return $this->originalString;
    }

    public function getRefAttribute()
    {
        return substr($this->originalString, $this->type === 'dev' ? 4 : 0);
    }

    public function getVersionAttribute()
    {
        return $this->version;
    }

    public function getIsValidAttribute()
    {
        return !is_null($this->normalized);
    }

    public function getIsSemverAttribute()
    {
        return (bool) preg_match(
            '/^[0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+(-[a-z0-9._-]+)?$/i',
            $this->normalized,
        );
    }

    public function getIsStableAttribute()
    {
        return $this->type !== 'dev' && $this->type !== 'prerelease';
    }

    protected function doMatchesStability(
        string $desiredStability,
        bool $exact
    ): bool {
        static $stabilities = ['stable', 'RC', 'beta', 'alpha', 'dev'];
        $normalizedDesiredStability = VersionParser::normalizeStability(
            $desiredStability,
        );
        $desiredIndex = array_search($normalizedDesiredStability, $stabilities);
        if ($desiredIndex === false) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unknown desired stability "%s"',
                    $normalizedDesiredStability,
                ),
            );
        }

        $versionIndex = array_search(
            VersionParser::parseStability($this->normalized),
            $stabilities,
        );

        if ($exact) {
            return $desiredIndex === $versionIndex;
        } else {
            return $versionIndex <= $desiredIndex;
        }
    }

    /**
     * Check if a version matches at least a certain stability
     * @param  string $desiredStability The desired mininum stability
     * @return bool                     If the version matches the stability
     *
     * @throws \InvalidArgumentException If the desired stability is not known
     */
    public function matchesMinStability(string $desiredStability)
    {
        return $this->doMatchesStability($desiredStability, false);
    }

    /**
     * Check if a version matches exactly a certain stability
     * @param  string $desiredStability The desired stability
     * @return bool                     If the version matches the stability
     *
     * @throws \InvalidArgumentException If the desired stability is not known
     */
    public function matchesStability(string $desiredStability)
    {
        return $this->doMatchesStability($desiredStability, true);
    }

    public static function filter($versions, string $constraint): Collection
    {
        return Collection::make(
            Semver::satisfiedBy(
                Collection::make($versions)
                    ->map(function ($version) {
                        if ($version instanceof self) {
                            return $version->normalized ?? $version->version;
                        } else {
                            return $version;
                        }
                    })
                    ->toArray(),
                $constraint,
            ),
        )->map(
            fn($normalized) => $versions->first(
                fn($version) => static::wrap($version)->normalized ===
                    $normalized,
            ),
        );
    }

    public static function getMaxSatisfying($versions, string $constraint)
    {
        return static::filter($versions, $constraint)->last();
    }

    public static function getMinSatisfying($versions, string $constraint)
    {
        return static::filter($versions, $constraint)->first();
    }

    public function sortCompare(Version $otherVersion): int
    {
        $a = $this->normalized ?? $this->version;
        $b = $otherVersion->normalized ?? $otherVersion->version;

        if (Comparator::greaterThan($a, $b)) {
            return 1;
        } elseif (Comparator::lessThan($a, $b)) {
            return -1;
        } else {
            return 0;
        }
    }

    public function compare(string $operator, Version $otherVersion): bool
    {
        return Comparator::compare(
            $this->normalized ?? $this->version,
            $operator,
            $otherVersion->normalized ?? $otherVersion->version,
        );
    }

    public function __toString()
    {
        return $this->version;
    }
}
