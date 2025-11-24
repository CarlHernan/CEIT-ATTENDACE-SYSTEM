<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\Event;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EventAttendanceExport implements FromCollection, WithHeadings, WithMapping, Responsable
{
    public $fileName = 'attendance.xlsx';

    protected Event $event;
    protected array $filters;

    public function __construct(Event $event, array $filters = [])
    {
        $this->event = $event;
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = AttendanceRecord::with(['user', 'recorder'])
            ->where('event_id', $this->event->id);

        if ($course = ($this->filters['course'] ?? null)) {
            $query->whereHas('user', fn ($q) => $q->where('course', $course));
        }

        if ($year = ($this->filters['year_level'] ?? null)) {
            $query->whereHas('user', fn ($q) => $q->where('year_level', $year));
        }

        if ($society = ($this->filters['society'] ?? null)) {
            $query->whereHas('user.societies', fn ($q) => $q->where('society_id', $society));
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Course',
            'Year Level',
            'Time In',
            'Time Out',
            'Method',
            'Recorded By',
        ];
    }

    public function map($record): array
    {
        return [
            $record->user->name,
            $record->user->course,
            $record->user->year_level,
            optional($record->time_in)->format('Y-m-d H:i:s'),
            optional($record->time_out)->format('Y-m-d H:i:s'),
            $record->method,
            $record->recorder->name ?? '',
        ];
    }
}
