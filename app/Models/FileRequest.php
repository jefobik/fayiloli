<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'request_to',
        'folder_id',
        'tag_id',
        'due_date_in_number',
        'due_date_in_word',
        'note',
    ];
}