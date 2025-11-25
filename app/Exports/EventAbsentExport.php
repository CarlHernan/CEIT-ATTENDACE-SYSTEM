<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EventAbsentExport implements FromCollection, WithHeadings, WithMapping, Responsable
{
    public $fileName = 'absent.xlsx';

    protected Event $event;
    protected array $filters;
    protected array $expectedUserIds;

    public function __construct(Event $event, array $expectedUserIds, array $filters = [], string $fileName = null)
    {
        $this->event = $event;
        $this->expectedUserIds = $expectedUserIds;
        $this->filters = $filters;
        if ($fileName) {
            $this->fileName = $fileName;
        }
    }

    public function collection()
    {
        $attendedIds = $this->event->attendanceRecords()->pluck('user_id')->all();
        $absentIds = array_values(array_diff($this->expectedUserIds, $attendedIds));

        return User::with('societies')
            ->whereIn('id', $absentIds)
            ->tap(fn ($q) => $this->applyFilters($q))
            ->get();
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Full Name',
            'Year Level',
            'Course',
            'Section',
            'Society',
            'Department',
            'Position',
        ];
    }

    public function map($user): array
    {
        $society = $user->societies->first();
        return [
            $user->id_number,
            $user->name,
            $user->year_level,
            $user->course,
            $user->section,
            $society?->abbreviation,
            $user->department ?? 'CEIT',
            $society?->pivot?->position,
        ];
    }

    protected function applyFilters($query): void
    {
        if ($courses = $this->filters['course'] ?? null) {
            $courses = is_array($courses) ? $courses : [$courses];
            $query->whereIn('course', $courses);
        }

        if ($section = $this->filters['section'] ?? null) {
            $query->where('section', $section);
        }

        if ($year = $this->filters['year_level'] ?? null) {
            $query->where('year_level', $year);
        }

        if ($position = $this->filters['position'] ?? null) {
            $query->whereHas('societies', fn ($s) => $s->where('position', $position));
        }

        if ($societyId = $this->filters['society'] ?? null) {
            $query->whereHas('societies', fn ($s) => $s->where('society_id', $societyId));
        }
    }
}
