<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedFilter extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'filters',
        'is_favorite',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_favorite' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
