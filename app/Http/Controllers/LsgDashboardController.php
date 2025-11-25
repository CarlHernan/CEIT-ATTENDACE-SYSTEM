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

        // LSG-created events only (society_id null or creator role lsg_officer)
        $lsgEvents = Event::with(['society', 'creator'])
            ->where(function ($q) {
                $q->whereNull('society_id')
                    ->orWhereHas('creator.role', fn ($r) => $r->where('slug', 'lsg_officer'));
            })
            ->orderBy('start_at', 'desc')
            ->take(8)
            ->get();

        return view('dashboards.lsg', compact('lsgEvents'));
    }
}
