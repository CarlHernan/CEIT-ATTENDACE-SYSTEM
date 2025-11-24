<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'id_number',
        'email',
        'course',
        'section',
        'year_level',
        'department',
        'qr_raw_text',
        'role_id',
        'is_society_officer',
        'is_lsg_officer',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_society_officer' => 'boolean',
        'is_lsg_officer' => 'boolean',
    ];
}

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function societies(): BelongsToMany
    {
        return $this->belongsToMany(Society::class)
            ->withPivot('position')
            ->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }
}
