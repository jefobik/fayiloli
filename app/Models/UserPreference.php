<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    /**
     * Get the user that owns the preference.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
