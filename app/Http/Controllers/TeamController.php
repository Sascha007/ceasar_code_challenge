<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Show the team page.
     */
    public function show(Team $team)
    {
        $team->load(['competition', 'puzzle']);

        return view('team.show', compact('team'));
    }

    /**
     * Submit a solution.
     */
    public function submit(Request $request, Team $team)
    {
        $request->validate([
            'solution' => 'required|string',
        ]);

        $success = $team->recordAttempt($request->solution);

        if (! $success) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lösung konnte nicht eingereicht werden.',
                'attempts' => $team->attempts,
            ]);
        }

        $team->refresh();

        if ($team->isSolved()) {
            $time = $team->solved_at->diffInSeconds($team->competition->started_at);

            return response()->json([
                'status' => 'success',
                'message' => 'Richtig! Glückwunsch!',
                'attempts' => $team->attempts,
                'time' => $time,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Leider falsch. Versuche es noch einmal!',
            'attempts' => $team->attempts,
        ]);
    }
}
