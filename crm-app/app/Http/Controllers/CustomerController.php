<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole(['admin','manager']);

        $query = $isAdmin ? Customer::query() : Customer::where('assigned_to', $user->id);

        if ($request->search) {
            $query->search($request->search);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->assigned_to && $isAdmin) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->tag) {
            $query->whereHas('tags', fn($q) => $q->where('tags.id', $request->tag));
        }

        $customers = $query
            ->with(['assignedUser','tags','latestActivity','pendingTasks'])
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $agents = $isAdmin ? User::where('is_active',true)->get(['id','name','email']) : collect();
        $tags = Tag::all();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'agents' => $agents,
            'tags' => $tags,
            'filters' => $request->only(['search','status','priority','assigned_to','tag']),
        ]);
    }

    public function create()
    {
        $agents = User::where('is_active', true)->get(['id','name','email']);
        $tags = Tag::all();
        return Inertia::render('Customers/Create', [
            'agents' => $agents,
            'tags' => $tags,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'status' => 'required|in:lead,prospect,active,inactive,churned',
            'priority' => 'required|in:low,medium,high',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'annual_value' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $data['created_by'] = Auth::id();
        if (empty($data['assigned_to'])) {
            $data['assigned_to'] = Auth::id();
        }

        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $customer = Customer::create($data);
        if ($tags) $customer->tags()->sync($tags);

        return redirect()->route('customers.show', $customer)->with('success', 'Cliente creato con successo!');
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        $customer->load([
            'assignedUser','createdBy','tags',
            'activities.user','activities.attachments',
            'tasks.assignedUser',
            'pipelines',
            'attachments.uploader',
        ]);

        $agents = User::where('is_active', true)->get(['id','name','email']);
        $tags = Tag::all();

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
            'agents' => $agents,
            'tags' => $tags,
        ]);
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);
        $agents = User::where('is_active', true)->get(['id','name','email']);
        $tags = Tag::all();
        $customer->load('tags');
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
            'agents' => $agents,
            'tags' => $tags,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'status' => 'required|in:lead,prospect,active,inactive,churned',
            'priority' => 'required|in:low,medium,high',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'annual_value' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $customer->update($data);
        $customer->tags()->sync($tags);

        return redirect()->route('customers.show', $customer)->with('success', 'Cliente aggiornato!');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Cliente eliminato.');
    }
}
