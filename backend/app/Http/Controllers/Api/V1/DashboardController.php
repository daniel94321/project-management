<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCommunication;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::whereIn('status', ['planning', 'active'])->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'total_tasks' => Task::count(),
            'pending_tasks' => Task::where('status', 'pending')->count(),
            'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
            'pending_communications' => ProjectCommunication::where('status', 'pending')->count(),
            'my_projects' => Project::where('owner_id', $user->id)->count(),
            'my_tasks' => Task::where('assigned_to', $user->id)->count(),
        ];

        return response()->json(['data' => $data]);
    }
}
