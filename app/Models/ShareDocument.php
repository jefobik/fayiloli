<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShareDocument extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'shared_id', 'name', 'token',
        'url', 'slug', 'valid_until', 'visibility',
        'share_id', 'share_type', 'user_type', 'user_id'
    ];


    public function folder()
    {
        return $this->hasMany(Folder::class, 'id', 'share_id');
    }


    public function document()
    {
        return $this->hasMany(Document::class, 'id', 'share_id');
    }


    public function sharesBySlug($slug)
    {
        if ($slug == "folder") {
            return $this->hasMany(Folder::class, 'id', 'share_id');
        } else {
            return $this->hasMany(Document::class, 'id', 'share_id');
        }
    }


    public function scopeIsPublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeHasExpired($query)
    {
        return $query->whereNotNull('valid_until')->where('valid_until', '<', now());
    }
}