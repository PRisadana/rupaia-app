<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preset extends Model
{
    protected $fillable = [
        'preset_name',
        'preset_file_path',
        'is_active',
    ];
}
