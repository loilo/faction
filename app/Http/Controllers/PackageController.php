<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Package;
use GitHubUtils;
use Illuminate\Http\Request;
use Str;

/**
 * Handle package-related requests
 */
class PackageController extends Controller
{
    use SearchesPackages;

    /**
     * Show a list of all packages
     */
    public function list(Request $request)
    {
        $searchQuery = $request->query('search', '');
        $packagesListClasses = '';

        $packages = Package::orderByDesc('last_modified')->get();

        $filterStyle = '';
        $searchResultsNum = '';
        $unsortedPackageNames = '';
        if (!empty($searchQuery) && $searchQuery !== '!') {
            $hasBang = Str::endsWith($searchQuery, '!');

            if ($hasBang) {
                $searchQuery = substr($searchQuery, 0, -1);
            }

            $results = $this->searchPackages($searchQuery);

            $searchResultsNum = trans_choice(
                'messages.search.results_num',
                sizeof($results),
            );

            // If there's only a single result or the query ends with a bang,
            // redirect directly to the first found package.
            if (sizeof($results) === 1 || ($hasBang && sizeof($results) > 0)) {
                return response()->redirectToRoute(
                    'package',
                    ['name' => $results[0]],
                    307,
                );
            }

            $visibilityRule = '';
            if (sizeof($results) > 0) {
                $packagesListClasses = '';

                // Generate compound selector to declare which packages are shown
                $selectors = join(
                    ",\n",
                    array_map(
                        fn($name) => ".package[data-package=\"$name\"]",
                        $results,
                    ),
                );

                $visibilityRule = "$selectors { display: block; }";
            } else {
                $packagesListClasses = ' packages-list--empty';
            }

            // Put the unsorted package order into a JS variable,
            // allowing JS to sort packages in canonical order
            // when the search field is cleared.
            $encodedUnsortedPackageNames = json_encode(
                $packages->pluck('short_name'),
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
            $unsortedPackageNames = <<<HTML
<script>window.unsortedPackageNames = {$encodedUnsortedPackageNames}</script>
HTML;

            // Sort packages to match search results order
            $packages = $packages
                ->sort(function (Package $a, Package $b) use ($results) {
                    $aPositionInResults = array_search($a->shortName, $results);
                    $bPositionInSample = array_search($b->shortName, $results);

                    if (
                        $aPositionInResults !== false &&
                        $bPositionInSample !== false
                    ) {
                        // Both packages are found in search results,
                        // compare their position in the results
                        return $aPositionInResults <=> $bPositionInSample;
                    } elseif ($aPositionInResults !== false) {
                        // Only package "a" is in results, put it first
                        return -1;
                    } elseif ($bPositionInSample !== false) {
                        // Only package "b" is in results, put it first
                        return 1;
                    } else {
                        // Order of packages not found in search results
                        // is not relevant since those are hidden anyway
                        return 0;
                    }
                })
                ->values();

            $filterStyle = ".package { display: none; }\n$visibilityRule";
        }

        return view('package-list.list', [
            'packages' => $packages,
            'groups' => Group::all(),
            'filterStyle' => $filterStyle,
            'packagesListClasses' => $packagesListClasses,
            'unsortedPackageNames' => $unsortedPackageNames,
            'searchQuery' => $searchQuery,
            'searchResults' => $searchResultsNum,
        ]);
    }

    /**
     * Show details about one specific package; this redirects to the package's
     * readme or, if no readme is available, to its versions list.
     *
     * @param string $name The package's name
     */
    public function show(string $name)
    {
        $package = Package::findByName($name);
        $readme = GitHubUtils::getReadme($package);

        if (!empty($readme)) {
            return redirect(null, 307)->route('package.readme', [
                'name' => $name,
            ]);
        } else {
            return redirect(null, 307)->route('package.versions', [
                'name' => $name,
            ]);
        }
    }

    /**
     * Show the readme of a package
     *
     * @param string $name The package's name
     */
    public function showReadme(string $name)
    {
        $package = Package::findByName($name);
        $readme = GitHubUtils::getReadme($package);
        $isArchived = GitHubUtils::isArchived($package);

        return view('package-details.package-details', [
            'scope' => 'readme',
            'package' => $package,
            'isArchived' => $isArchived,
            'hasReadme' => !empty($readme),
            'readme' => $readme,
        ]);
    }

    /**
     * Show the branches and versions of a package
     *
     * @param string $name The package's name
     */
    public function showVersions(string $name)
    {
        $package = Package::findByName($name);
        $hasReadme = GitHubUtils::hasCachedReadme($package);
        $isArchived = GitHubUtils::isArchived($package);

        return view('package-details.package-details', [
            'scope' => 'versions',
            'package' => $package,
            'isArchived' => $isArchived,
            'hasReadme' => $hasReadme,
        ]);
    }

    /**
     * Show the dependencies and dependants of a package
     *
     * @param string $name The package's name
     */
    public function showRelations(string $name)
    {
        $package = Package::findByName($name);
        $hasReadme = GitHubUtils::hasCachedReadme($package);
        $isArchived = GitHubUtils::isArchived($package);

        return view('package-details.package-details', [
            'scope' => 'relations',
            'package' => $package,
            'isArchived' => $isArchived,
            'hasReadme' => $hasReadme,
        ]);
    }
}
