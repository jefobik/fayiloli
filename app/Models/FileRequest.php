<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ProtectsUuidRouteBindings;

class FileRequest extends Model
{
    use HasFactory, HasUuids, ProtectsUuidRouteBindings;

    protected $fillable = [
        'name',
        'request_to',
        'folder_id',
        'tag_id',
        'due_date_in_number',
        'due_date_in_word',
        'note',
    ];

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
