<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = [

        'file_name',
        'original_name',
        'type',
        'file_path',
        'file_size',
        'mime_type',
        'created_by'

    ];
}