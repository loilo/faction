<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Batch insert arrays
        $insertPackages = [];
        $insertVersions = [];
        $insertDependencies = [];

        // Iterate packages
        foreach ($this->getRawPackages() as $packageName => $versions) {
            // Read versions
            foreach ($versions as $version => $versionMeta) {
                $insertVersions[] = [
                    'name' => $packageName,
                    'version' => $version,
                    'commit' => $versionMeta['source']['reference'],
                    'time' => $versionMeta['time'],
                ];
            }

            $packageMeta = isset($versions['dev-master'])
                ? $versions['dev-master']
                : end($versions);

            $latestChange = array_reduce(
                $versions,
                function ($carry, $version) {
                    return max($carry, $version['time']);
                },
                0,
            );

            // Read dependencies
            foreach (
                $packageMeta['require'] ?? []
                as $dependencyName => $constraint
            ) {
                $insertDependencies[] = [
                    'package' => $packageName,
                    'dependency' => $dependencyName,
                    'constraint' => $constraint,
                    'dev' => 0,
                ];
            }
            foreach (
                $packageMeta['require-dev'] ?? []
                as $dependencyName => $constraint
            ) {
                $insertDependencies[] = [
                    'package' => $packageName,
                    'dependency' => $dependencyName,
                    'constraint' => $constraint,
                    'dev' => 1,
                ];
            }

            // Extract repository
            $user = config('app.repository.github_org');

            if (Str::startsWith($packageMeta['source']['url'], 'git@')) {
                preg_match(
                    "~^git@github\\.com:{$user}/(.+)\\.git$~",
                    $packageMeta['source']['url'],
                    $matches,
                );
            } else {
                preg_match(
                    "~^https://github\\.com/{$user}/(.+).git$~",
                    $packageMeta['source']['url'],
                    $matches,
                );
            }
            $repo = $matches[1];

            $insertPackages[] = [
                'name' => $packageName,
                'description' => $packageMeta['description'] ?? '-',
                'last_modified' => $latestChange,
                'source' => $packageMeta['source']['url'],
                'repo' => $repo,
                'commit' => $packageMeta['source']['reference'],
            ];
        }

        // Chunk insertions before execution to avoid SQLite batch insert cap of ~1000 items
        Collection::make($insertPackages)
            ->chunk(100)
            ->each(function ($insertPackages) {
                DB::table('packages')->insert($insertPackages->all());
            });

        Collection::make($insertVersions)
            ->chunk(100)
            ->each(function ($insertVersions) {
                DB::table('versions')->insert($insertVersions->all());
            });
        Collection::make($insertDependencies)
            ->chunk(100)
            ->each(function ($insertDependencies) {
                DB::table('dependencies')->insert($insertDependencies->all());
            });
    }

    /**
     * Get a list of raw package data by parsing the packages.json and its included files
     * The result looks like:
     * [
     *   [package-name] => [
     *     [version] => [
     *       ... // Data
     *     ]
     *   ]
     * ]
     *
     * @return array
     */
    private function getRawPackages()
    {
        static $rawPackages = null;

        if (is_null($rawPackages)) {
            $satisOutDir = storage_path('app/satis');

            $packagesJson = FS::readJsonFile(
                $satisOutDir . '/packages.json',
                FS::PARSE_ASSOC,
            );

            $rawPackages = array_merge(
                $packagesJson['packages'],
                array_reduce(
                    array_keys($packagesJson['includes']),
                    fn($carry, $current) => array_merge(
                        $carry,
                        FS::readJsonFile(
                            $satisOutDir . '/' . $current,
                            FS::PARSE_ASSOC,
                        )['packages'],
                    ),
                    [],
                ),
            );
        }

        return $rawPackages;
    }
}
