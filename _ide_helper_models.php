<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * Represents a package that is part of the app's Composer repository.
 *
 * @property string $name
 * @property string $description
 * @property \Carbon\Carbon $lastModified
 * @property string $source
 * @property string $repo
 * @property string $commit
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Dependency[] $allDependencies
 * @property-read int|null $allDependenciesCount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Dependency[] $dependants
 * @property-read int|null $dependantsCount
 * @property-read mixed $allDependencies
 * @property-read mixed $dependencies
 * @property-read mixed $devDependencies
 * @property-read string $githubRepo
 * @property-read mixed $githubUrl
 * @property-read \App\Models\Group|null $group
 * @property-read bool $hasGroup
 * @property-read bool $hasPreRelease
 * @property-read bool $hasStableRelease
 * @property-read \App\Models\Release|null $head
 * @property-read mixed $installCommand
 * @property-read \App\Models\Release|null $latestRelease
 * @property-read \App\Models\Release|null $latestStableRelease
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Release[] $releases
 * @property-read string|null $shortName
 * @property-read mixed $url
 * @property-read string|null $vendor
 * @property-read int|null $releasesCount
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Package newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Package newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Package query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Package whereCommit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Package whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Package whereLastModified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Package whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Package whereRepo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Package whereSource($value)
 */
	class Package extends \Eloquent {}
}

namespace App\Models{
/**
 * An entity required by a package, idenitfied by a name and a version.
 * 
 * Can be a dependency package, a dev dependency package or a
 * required PHP version or PHP module like "ext-mb".
 *
 * @property string $package
 * @property string $dependency
 * @property string $constraint
 * @property bool $dev
 * @property-read \App\Models\Package $dependentPackage
 * @property-read mixed $name
 * @property-read string|null $shortName
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dependency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dependency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dependency query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dependency whereConstraint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dependency whereDependency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dependency whereDev($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dependency wherePackage($value)
 */
	class Dependency extends \Eloquent {}
}

namespace App\Models{
/**
 * A release points to a certain commit of a package.
 * 
 * It may tagged as a stable ("0.6.2") or unstable ("1.0.4-beta.3")
 * semver release or point to a branch ("dev-master").
 *
 * @property string $name
 * @property string $version
 * @property string $commit
 * @property \Illuminate\Support\Carbon $time
 * @property-read mixed $fullCommit
 * @property-read mixed $index
 * @property-read mixed $next
 * @property-read mixed $nextStable
 * @property-read \App\Models\Package $package
 * @property-read mixed $previous
 * @property-read mixed $previousStable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Release newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Release newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Release query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Release whereCommit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Release whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Release whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Release whereVersion($value)
 */
	class Release extends \Eloquent {}
}

namespace App\Models\Helpers{
/**
 * A Model which caches get*Attribute() accessor results for all
 * attributes listed in its $memoize property.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Helpers\MemoizingModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Helpers\MemoizingModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Helpers\MemoizingModel query()
 */
	class MemoizingModel extends \Eloquent {}
}

namespace App\Models{
/**
 * Represents an unknown user.
 * 
 * This is needed because Laravel authentication
 * requires to always return a user object on success.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnonymousUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnonymousUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnonymousUser query()
 */
	class AnonymousUser extends \Eloquent {}
}

