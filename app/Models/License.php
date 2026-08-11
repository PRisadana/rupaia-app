<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class license extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'terms',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function contents()
    {
        return $this->hasMany(Content::class);
    }

    public function folders()
    {
        return $this->hasMany(Folder::class);
    }
}
