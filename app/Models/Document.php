<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Laravel\Scout\Searchable;

class Document extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Searchable;

    protected static function boot()
    {
        parent::boot();

        // Define a global scope to always order by position
        static::addGlobalScope('position', function ($builder) {
            $builder->orderBy('position');
        });

        static::creating(function ($document) {
            if (!isset($document->position)) {
                $document->position = static::max('position') + 1;
            }
            $document->slug = Str::slug($document->name);
        });
        static::updating(function ($document) {
            $document->slug = Str::slug($document->name);
        });
    }

    protected $fillable = [
        'name', 'slug', 'original_name', 'file_path', 'size', 'extension', 'folder_id', 'visibility', 'share', 'download', 'email',
        'url', 'contact', 'owner', 'date', 'emojies', 'position'
    ];

    public function getFileIcon()
    {
        // Check if the extension is in the array
        if (!in_array($this->extension, getImageExtensions())) {
            if (!empty($this->extension)) {
                return asset('img/' . $this->extension . '.png');
            } else {
                return asset($this->file_path);
            }
        } else {
            return asset($this->file_path);
        }
    }

    public function isPublic()
    {
        return $this->visibility;
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    // Method to delete associated file from public path
    public function deleteFile()
    {
        $filePath = public_path($this->file_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function toSearchableArray(): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'original_name' => $this->original_name,
            'extension'     => $this->extension,
            'folder_name'   => $this->folder?->name,
            'tags'          => $this->tags->pluck('name')->implode(' '),
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'folder_id', 'visibility', 'extension'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('document');
    }
}
