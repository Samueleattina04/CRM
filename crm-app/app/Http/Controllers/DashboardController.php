<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Task;
use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole(['admin','manager']);

        $baseCustomers = $isAdmin ? Customer::query() : Customer::where('assigned_to', $user->id);
        $baseActivities = $isAdmin ? Activity::query() : Activity::where('user_id', $user->id);
        $baseTasks = $isAdmin ? Task::query() : Task::where(function($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('assigned_to', $user->id);
        });

        // Stats
        $stats = [
            'total_customers' => (clone $baseCustomers)->count(),
            'active_customers' => (clone $baseCustomers)->where('status', 'active')->count(),
            'new_leads' => (clone $baseCustomers)->where('status', 'lead')->count(),
            'activities_today' => (clone $baseActivities)->whereDate('occurred_at', today())->count(),
            'pending_tasks' => (clone $baseTasks)->where('status','pending')->count(),
            'overdue_tasks' => (clone $baseTasks)->where('status','pending')->where('due_date','<',now())->count(),
            'pipeline_value' => $isAdmin
                ? Pipeline::where('stage','!=','won')->where('stage','!=','lost')->sum('value')
                : Pipeline::where('user_id',$user->id)->where('stage','!=','won')->where('stage','!=','lost')->sum('value'),
        ];

        // Recent customers
        $recentCustomers = (clone $baseCustomers)
            ->with(['assignedUser','latestActivity','tags'])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        // Today's activities
        $todayActivities = (clone $baseActivities)
            ->with('customer')
            ->whereDate('occurred_at', today())
            ->orderByDesc('occurred_at')
            ->limit(10)
            ->get();

        // Upcoming tasks
        $upcomingTasks = (clone $baseTasks)
            ->with(['customer','assignedUser'])
            ->where('status','pending')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Activity chart (last 7 days)
        $activityChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $activityChart[] = [
                'date' => $date->format('D'),
                'count' => (clone $baseActivities)->whereDate('occurred_at', $date)->count(),
            ];
        }

        // Pipeline by stage
        $pipelineStages = Pipeline::selectRaw('stage, COUNT(*) as count, SUM(value) as total_value')
            ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
            ->whereNotIn('stage', ['won','lost'])
            ->groupBy('stage')
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentCustomers' => $recentCustomers,
            'todayActivities' => $todayActivities,
            'upcomingTasks' => $upcomingTasks,
            'activityChart' => $activityChart,
            'pipelineStages' => $pipelineStages,
        ]);
    }
}
