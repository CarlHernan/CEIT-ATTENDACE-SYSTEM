<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_at',
        'end_at',
        'location',
        'attendance_mode',
        'audience',
        'audience_notes',
        'late_threshold_minutes',
        'status',
        'society_id',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function scopeVisibleToStudent(Builder $query, User $user): Builder
    {
        $societyId = $user->societies()->value('societies.id');
        $isSocietyOfficer = (bool) $user->is_society_officer;
        $isLsgOfficer = (bool) $user->is_lsg_officer;

        return $query->where('status', '!=', 'cancelled')
            ->where(function (Builder $visible) use ($societyId, $isSocietyOfficer, $isLsgOfficer) {
                if ($societyId) {
                    $visible->orWhere(function (Builder $societyScope) use ($societyId) {
                        $societyScope->where('society_id', $societyId)
                            ->whereIn('audience', ['society_members', 'others']);
                    });

                    if ($isSocietyOfficer) {
                        $visible->orWhere(function (Builder $officersOnly) use ($societyId) {
                            $officersOnly->where('society_id', $societyId)
                                ->where('audience', 'society_officers');
                        });
                    }
                }

                $visible->orWhere('audience', 'ceit_students');

                if ($isLsgOfficer) {
                    $visible->orWhere('audience', 'lsg_officers');
                }

                if ($isSocietyOfficer || $isLsgOfficer) {
                    $visible->orWhere('audience', 'all_officers');
                }

                // LSG-created events using "others" are treated as CEIT-wide.
                $visible->orWhere(function (Builder $lsgOthers) {
                    $lsgOthers->whereNull('society_id')
                        ->where('audience', 'others');
                });
            });
    }

    public function getScopeLabelAttribute(): string
    {
        if ($this->society) {
            return $this->society->abbreviation;
        }

        if ($this->creator?->role?->slug === 'lsg_officer') {
            return 'CEIT-LSG';
        }

        return 'CEIT';
    }
}
