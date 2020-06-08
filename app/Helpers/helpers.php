<?php

use App\Models\Perishable;

/**
 * Create a perishable object
 *
 * @param string $duration How long until the object should expire
 * @param mixed  $data     Data to attach to the object
 * @return Perishable
 */
function perishable(string $duration, $data = null)
{
    return Perishable::create($duration, $data);
}

/**
 * Supplement a package name with the app's configured vendor name
 *
 * @param string $name The package name or short package name to supplement
 * @return string
 */
function full_package_name(string $name): string
{
    if (Str::contains($name, '/')) {
        return $name;
    }

    return sprintf('%s/%s', config('app.repository.package_vendor'), $name);
}

/**
 * Get the short name of a package
 *
 * @param string $name The package name or short package name to shorten
 * @return string
 */
function short_package_name(string $name): string
{
    if (Str::contains($name, '/')) {
        return preg_replace('#^[^/]*/#', '', $name);
    } else {
        return $name;
    }
}

/**
 * Supplement a repository name with the app's vendor name
 *
 * @param string $name The repo name or short repo name to supplement
 * @return string
 */
function full_repo_name(string $name): string
{
    if (Str::contains($name, '/')) {
        return $name;
    }

    return sprintf('%s/%s', config('app.repository.github_org'), $name);
}

/**
 * Get the short name of a repository
 *
 * @param string $name The repo name or short repo name to shorten
 * @return string
 */
function short_repo_name(string $name): string
{
    return short_package_name($name);
}

/**
 * Split a comma-separated value.
 * Super basic, no support for enclosure characters etc.
 * Mostly designed to create simple config arrays from .env values.
 *
 * @param string $value
 * @return array
 */
function comma_split(string $value): array
{
    return array_filter(array_map('trim', explode(',', $value)));
}
