<?php

namespace App\Http\Controllers;
use App\Services\AdminStatsService;
use App\Services\AgentStatsService;
use App\Services\UserStatsService;
use Illuminate\Http\JsonResponse;
class StatsController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $user = request()->user();

        $stats = match (true) {
            $user->isAdmin() => app(AdminStatsService::class)->get(),
            $user->isAgent() => app(AgentStatsService::class)->get($user),
            default          => app(UserStatsService::class)->get($user),
        };

        return response()->json($stats);
    }
}
