<?php

namespace App\Http\Controllers\Status;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StatusController extends Controller
{
    /**
     * Display the status page for the active competition.
     */
    public function show(Request $request, Competition $competition): Response
    {
        $teams = $competition->teams()
            ->with('puzzle', 'submissions')
            ->get()
            ->map(function ($team) use ($competition) {
                return [
                    'name' => $team->display_name,
                    'status' => $this->getTeamStatus($team),
                    'time' => $team->solved_at
                        ? $team->solved_at->diffInSeconds($competition->started_at)
                        : null,
                    'attempts' => $team->attempts,
                ];
            })
            ->sortBy([
                ['time', 'asc'],
                ['attempts', 'asc'],
                ['name', 'asc'],
            ]);

        return response()->view('status.show', [
            'competition' => $competition,
            'teams' => $teams,
        ]);
    }

    /**
     * Handle SSE connection for live updates.
     */
    public function events(Request $request, Competition $competition): Response
    {
        $response = new Response;
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no'); // For Nginx

        $response->setCallback(function () use ($competition) {
            while (true) {
                echo 'data: '.json_encode([
                    'teams' => $competition->teams()
                        ->with('puzzle', 'submissions')
                        ->get()
                        ->map(function ($team) use ($competition) {
                            return [
                                'name' => $team->display_name,
                                'status' => $this->getTeamStatus($team),
                                'time' => $team->solved_at
                                    ? $team->solved_at->diffInSeconds($competition->started_at)
                                    : null,
                                'attempts' => $team->attempts,
                            ];
                        })
                        ->sortBy([
                            ['time', 'asc'],
                            ['attempts', 'asc'],
                            ['name', 'asc'],
                        ]),
                ])."\n\n";

                ob_flush();
                flush();

                if (connection_aborted()) {
                    break;
                }

                sleep(1);
            }
        });

        return $response;
    }

    /**
     * Get the current status of a team.
     */
    private function getTeamStatus(Team $team): string
    {
        if ($team->solved_at) {
            return 'solved';
        }

        if ($team->competition->status === Competition::STATUS_RUNNING) {
            return 'active';
        }

        if ($team->ready_at) {
            return 'ready';
        }

        return 'waiting';
    }
}
