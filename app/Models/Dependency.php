<?php

namespace App\Models;

/**
 * An entity required by a package, idenitfied by a name and a version.
 * Can be a dependency package, a dev dependency package or a
 * required PHP version or PHP module like "ext-mb".
 */
class Dependency extends Helpers\MemoizingModel
{
    protected $primaryKey = null;
    protected $casts = ['dev' => 'boolean'];
    protected $memoize = ['dependentPackage'];
    public $incrementing = false;

    /**
     * Equality check needed for efficient caching.
     * @inheritdoc
     */
    public function is($model)
    {
        return parent::is($model) &&
            $this->attributes['package'] ===
                $model->getAttributes()['package'] &&
            $this->attributes['dependency'] ===
                $model->getAttributes()['dependency'];
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return "$this->package:$this->dependency";
    }

    /**
     * Get the package that required this dependency.
     */
    public function dependentPackage()
    {
        return $this->belongsTo(Package::class, 'package', 'name');
    }

    public function getNameAttribute()
    {
        return $this->dependency;
    }

    public function getShortNameAttribute(): ?string
    {
        $parts = explode('/', $this->dependency);
        return end($parts);
    }
}
