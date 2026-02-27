<?php
namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole(['admin','manager']);

        $query = $isAdmin ? Task::query() : Task::where(function($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('assigned_to', $user->id);
        });

        if ($request->status) $query->where('status', $request->status);
        if ($request->priority) $query->where('priority', $request->priority);
        if ($request->type) $query->where('type', $request->type);
        if ($request->overdue) $query->overdue();

        $tasks = $query
            ->with(['customer','assignedUser'])
            ->orderBy('due_date')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['status','priority','type','overdue']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'assigned_to' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:call,email,meeting,follow_up,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'reminder_at' => 'nullable|date',
        ]);

        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';

        Task::create($data);
        return back()->with('success', 'Task creato!');
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:pending,in_progress,completed,cancelled',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'reminder_at' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if (isset($data['status']) && $data['status'] === 'completed') {
            $data['completed_at'] = now();
        }

        $task->update($data);
        return back()->with('success', 'Task aggiornato!');
    }

    public function complete(Task $task)
    {
        $task->update(['status' => 'completed', 'completed_at' => now()]);
        return back()->with('success', 'Task completato!');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return back()->with('success', 'Task eliminato.');
    }
}
