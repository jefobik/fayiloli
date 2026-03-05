<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ProtectsUuidRouteBindings;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tag extends Model
{
    use HasFactory, HasUuids, Searchable, LogsActivity, ProtectsUuidRouteBindings;

    protected $fillable = ['name', 'slug', 'code', 'background_color', 'foreground_color'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($tag) {
            $tag->slug = Str::slug($tag->name);
            $tag->code = substr(strtoupper(str_replace([' ', '-'], '_', $tag->name)), 0, 10);
        });
        static::updating(function ($tag) {
            $tag->slug = Str::slug($tag->name);
            $tag->code = substr(strtoupper(str_replace([' ', '-'], '_', $tag->name)), 0, 10);
        });
    }

    public function folders()
    {
        return $this->belongsToMany(Folder::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function toSearchableArray(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'code' => $this->code];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('tag');
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
