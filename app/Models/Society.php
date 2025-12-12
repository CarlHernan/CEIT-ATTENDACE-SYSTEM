<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Society extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'abbreviation',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('position')
            ->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
