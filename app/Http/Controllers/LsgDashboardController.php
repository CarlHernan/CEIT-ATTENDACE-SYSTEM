<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LsgDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $now = now();

        $upcomingClause = function ($q) use ($now) {
            $q->where(function ($w) use ($now) {
                $w->whereNull('end_at')->where('start_at', '>=', $now);
            })->orWhere(function ($w) use ($now) {
                $w->whereNotNull('end_at')->where('end_at', '>=', $now);
            });
        };

        // LSG-created events only (society_id null or creator role lsg_officer)
        $lsgEvents = Event::with(['society', 'creator'])
            ->where(function ($q) {
                $q->whereNull('society_id')
                    ->orWhereHas('creator.role', fn ($r) => $r->where('slug', 'lsg_officer'));
            })
            ->where($upcomingClause)
            ->orderBy('start_at', 'desc')
            ->take(8)
            ->get();

        return view('dashboards.lsg', compact('lsgEvents'));
    }
}
