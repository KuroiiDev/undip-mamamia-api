<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'icon_path',
    ];

    public function restaurants(): BelongsToMany
    {
        return $this->belongsToMany(Restaurant::class, 'category_restaurant');
    }
}
