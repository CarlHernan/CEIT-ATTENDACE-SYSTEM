<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $societyIds = $user->societies()->pluck('societies.id');
        $isSocOfficer = $user->is_society_officer;
        $isLsgOfficer = $user->is_lsg_officer;
        $year = $user->year_level;

        $baseVisibility = ($isSocOfficer || $isLsgOfficer) ? ['students', 'all', 'officers'] : ['students', 'all'];

        // Society panel: only events from the student's societies
        $societyEvents = Event::with('society')
            ->whereIn('society_id', $societyIds)
            ->where(function ($sub) use ($societyIds, $isSocOfficer, $isLsgOfficer, $year) {
                $sub->where(function ($q) use ($societyIds) {
                    $q->whereIn('audience', ['all', 'society'])
                        ->whereIn('society_id', $societyIds);
                })
                ->orWhere(function ($q) use ($societyIds, $year) {
                    $q->where('audience', 'year_specific')
                        ->whereIn('society_id', $societyIds)
                        ->where(function ($w) use ($year) {
                            $w->whereRaw('FIND_IN_SET(?, audience_years)', [$year]);
                        });
                })
                ->orWhere(function ($q) use ($societyIds, $isSocOfficer) {
                    $q->where('audience', 'society_officers')
                        ->whereIn('society_id', $societyIds)
                        ->whereRaw($isSocOfficer ? '1=1' : '0=1');
                })
                ->orWhere(function ($q) use ($societyIds, $isSocOfficer) {
                    $q->where('audience', 'officers_only')
                        ->whereIn('society_id', $societyIds)
                        ->whereRaw($isSocOfficer ? '1=1' : '0=1');
                });
            })
            ->whereIn('visibility', $baseVisibility)
            ->whereDate('start_at', '>=', now()->subDays(7))
            ->orderBy('start_at')
            ->take(6)
            ->get();

        // CEIT/LSG panel: CEIT-wide events or LSG-type events (not society-scoped)
        $ceitEvents = Event::with('society')
            ->where(function ($q) {
                $q->where('is_ceit_wide', true)
                    ->orWhereIn('type', ['ceit', 'lsg']);
            })
            ->where(function ($q) use ($isSocOfficer, $isLsgOfficer, $year) {
                $q->where('audience', 'all')
                    ->orWhere(function ($s) use ($isLsgOfficer) {
                        $s->where('audience', 'lsg_officers')
                            ->whereRaw($isLsgOfficer ? '1=1' : '0=1');
                    })
                    ->orWhere(function ($s) use ($isSocOfficer, $isLsgOfficer) {
                        $s->where('audience', 'lsg_and_society_officers')
                            ->whereRaw(($isSocOfficer || $isLsgOfficer) ? '1=1' : '0=1');
                    })
                    ->orWhere(function ($s) use ($isSocOfficer, $isLsgOfficer) {
                        $s->where('audience', 'officers_only')
                            ->whereRaw(($isSocOfficer || $isLsgOfficer) ? '1=1' : '0=1');
                    })
                    ->orWhere(function ($s) use ($year) {
                        $s->where('audience', 'year_specific')
                            ->where(function ($w) use ($year) {
                                $w->whereRaw('FIND_IN_SET(?, audience_years)', [$year]);
                            });
                    });
            })
            ->whereIn('visibility', $baseVisibility)
            ->whereDate('start_at', '>=', now()->subDays(7))
            ->orderBy('start_at')
            ->take(6)
            ->get();

        return view('dashboards.student', [
            'societyEvents' => $societyEvents,
            'ceitEvents' => $ceitEvents,
        ]);
    }
}
