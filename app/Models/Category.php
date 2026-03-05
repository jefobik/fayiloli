<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\ProtectsUuidRouteBindings;

class Category extends Model
{
    use HasFactory, HasUuids, ProtectsUuidRouteBindings;

    protected $table = 'categories';

    protected $keyType = 'string';


    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });
        static::updating(function ($category) {
            $category->slug = Str::slug($category->name);
        });
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Surgically prevent Postgres 22P02 "invalid input syntax for type uuid"
     * when a non-UUID string is passed in a route parameter.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === null && !\Illuminate\Support\Str::isUuid($value)) {
            return null;
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
