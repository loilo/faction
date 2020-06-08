<?php

namespace View;

use App\Models\Group;
use Carbon\Carbon;
use FS;
use Str;

/**
 * Dedent a multi-line string so that the minimum indentation is 0
 *
 * @param string $string The string to dedent
 * @return string
 */
function dedent(string $string): string
{
    $parts = array_filter(explode("\n", $string), fn($part) => trim($part));

    $spaces = min(
        array_map(function ($part) {
            preg_match('#^ *#', $part, $matches);
            return strlen($matches[0]);
        }, $parts),
    );

    $parts = array_map(fn($part) => substr($part, $spaces), $parts);

    return implode("\n", $parts);
}

/**
 * Remove any group prefixes from a package name and replace it with
 * the group's logo. Also dim the "-bundle" suffix.
 *
 * @param string $name The package name to make more concise
 * @return string
 */
function decoratePackageName($name)
{
    if (Str::endsWith($name, '-bundle')) {
        $name =
            Str::substr($name, 0, -7) .
            '<span class="package__name-suffix">-bundle</span>';
    }

    static $groups = null;
    if (is_null($groups)) {
        $groups = Group::all();
    }

    foreach ($groups as $group) {
        if (Str::startsWith($name, $group->prefix)) {
            if (isset($group->logo)) {
                $groupLogoPath = 'img/groups/' . $group->id . '/logo.svg';
                $stripped = Str::substr($name, Str::length($group->prefix));

                return sprintf(
                    '<img class="package-concise-logo" src="%s" alt="%s logo"> %s',
                    "/$groupLogoPath",
                    $group->name,
                    $stripped,
                );
            } else {
                return $name;
            }
        }
    }

    return $name;
}

/**
 * Use the provided object as HTML attributes
 *
 * @param array $array  The provided attributes
 * @return string
 */
function attributes(array $array): string
{
    return (sizeof($array) ? ' ' : '') .
        join(
            ' ',
            array_map(
                function ($attr, $value) {
                    return sprintf('%s="%s"', $attr, htmlspecialchars($value));
                },
                array_keys($array),
                array_values($array),
            ),
        );
}

/**
 * Get a link from a package name
 *
 * @param string $package The package name
 * @return string|null
 */
function linkifyPackage(string $package): ?string
{
    if (preg_match('/\\//', $package)) {
        // Usual package in vendor/package format
        if (
            Str::startsWith(
                $package,
                config('app.repository.package_vendor') . '/',
            )
        ) {
            // Internal packages from this very registry
            return config('app.url') .
                '/package/' .
                explode('/', $package, 2)[1];
        } else {
            // Regular Packagist packages
            return 'https://packagist.org/packages/' . $package;
        }
    } elseif ($package === 'composer-plugin-api') {
        // Special composer-plugin-api package
        return 'https://getcomposer.org/doc/articles/plugins.md';
    } elseif (
        Str::startsWith($package, 'ext-') ||
        preg_match(
            '/^lib-(curl|iconv|icu|libxml|openssl|pcre|uuid|xsl)$/',
            $package,
        )
    ) {
        // PHP extensions
        return sprintf(
            'https://www.php.net/manual/book.%s.php',
            substr($package, 4),
        );
    } else {
        return null;
    }
}

/**
 * Get a string which tells how long ago a certain time was
 *
 * @param Carbon $time The time to check
 * @return string
 */
function recency(Carbon $time): string
{
    if ($time->isToday()) {
        if (__('time.hours') == '24') {
            return $time->translatedFormat(__('time.time'));
        } else {
            return $time->translatedFormat(__('time.time')) .
                ' ' .
                join(
                    '',
                    array_map(
                        fn($char) => "$char.",
                        str_split($time->translatedFormat('a'), 1),
                    ),
                );
        }
    } elseif ($time->isYesterday()) {
        return __('time.yesterday');
    } elseif ($time->isCurrentYear()) {
        return $time->translatedFormat(__('time.date_current_year'));
    } else {
        return $time->translatedFormat(__('time.date_full'));
    }
}
