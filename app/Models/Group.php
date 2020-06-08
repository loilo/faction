<?php

namespace App\Models;

use Cache;
use FS;
use Illuminate\Support\Collection;

/**
 * A category for a class of packages.
 * Packages are associated with a group by matching a certain prefix.
 * Groups are a purely presentational helper for improved
 * recognizability of related packages in the package list.
 *
 * @property string $id     A unique identifier of the group
 * @property string $name   The name of the group
 * @property string $prefix The prefix to match to associate a package with this group
 * @property string $logo   Some SVG source code representing the group's logo
 * @property object $colors An associative array of colors used
 *                          to visually contrast from others
 */
class Group extends Helpers\MemoizingOfflineModel
{
    use Helpers\CollectsLazily;

    protected $memoize = ['colors'];

    public static function getDefault()
    {
        return Cache::rememberForever('groups.default', function () {
            return new static([
                'id' => 'default',
                'colors' => [
                    'darker' => '#b7a2bf',
                ],
            ]);
        });
    }

    public function getColorsAttribute()
    {
        return (object) $this->attributes['colors'];
    }

    public function collect()
    {
        $groups = config('app.package_groups');

        if (empty($groups)) {
            return collect([]);
        }

        $hash = md5(json_encode($groups));

        if (Cache::get('groups.hash') !== $hash) {
            $groupModels = collect($groups)->map(
                fn($group) => new static((array) $group),
            );

            $this::generateAssets($groupModels);

            Cache::set('groups.hash', $hash);
            Cache::set('groups.collection', $groupModels);

            return $groupModels;
        }

        return Cache::get('groups.collection');
    }

    public static function generateAssets(Collection $groups)
    {
        $groupsPath = public_path('img/groups');
        FS::remove($groupsPath);

        $allGroups = collect([static::getDefault()])->concat($groups);

        foreach ($allGroups as $group) {
            /**
             * @var Group $group
             */

            $groupPath = "$groupsPath/{$group->id}";

            // Copy icons
            if (isset($group->colors->darker)) {
                // Save GitHub, Install and Branch buttons for non-grouped packages
                FS::dumpFile(
                    "$groupPath/github.svg",
                    (string) view('icons.github', [
                        'fill' => $group->colors->darker,
                    ]),
                );

                FS::dumpFile(
                    "$groupPath/install.svg",
                    (string) view('icons.install', [
                        'fill' => $group->colors->darker,
                    ]),
                );

                FS::dumpFile(
                    "$groupPath/branch.svg",
                    (string) view('icons.branch', [
                        'fill' => $group->colors->darker,
                    ]),
                );

                FS::dumpFile(
                    "$groupPath/history.svg",
                    (string) view('icons.history', [
                        'fill' => $group->colors->darker,
                    ]),
                );
            } else {
                // If no color is set, copy over default icons
                FS::copy(
                    "$groupsPath/default/github.svg",
                    "$groupPath/github.svg",
                );

                FS::copy(
                    "$groupsPath/default/install.svg",
                    "$groupPath/install.svg",
                );

                FS::copy(
                    "$groupsPath/default/branch.svg",
                    "$groupPath/branch.svg",
                );

                FS::copy(
                    "$groupsPath/default/history.svg",
                    "$groupPath/history.svg",
                );
            }

            // Dump logo or use GitHub icon
            if (!is_null($group->logo)) {
                FS::dumpFile("$groupPath/logo.svg", $group->logo);
            } else {
                FS::copy("$groupPath/github.svg", "$groupPath/logo.svg");
            }
        }
    }
}
