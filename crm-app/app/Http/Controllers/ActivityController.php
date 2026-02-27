<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ActivityController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'type' => 'required|in:email,call,whatsapp,sms,meeting,note,task,email_incoming,email_outgoing',
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string',
            'direction' => 'required|in:inbound,outbound,internal',
            'status' => 'required|in:pending,completed,cancelled,scheduled',
            'duration_minutes' => 'nullable|integer|min:0',
            'phone_number' => 'nullable|string|max:20',
            'email_from' => 'nullable|email',
            'email_to' => 'nullable|email',
            'occurred_at' => 'required|date',
        ]);

        $data['user_id'] = Auth::id();
        $data['customer_id'] = $customer->id;

        $activity = Activity::create($data);

        // Update last_contact_at
        $customer->update(['last_contact_at' => $data['occurred_at']]);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                $activity->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return back()->with('success', 'Attività aggiunta!');
    }

    public function update(Request $request, Activity $activity)
    {
        $this->authorize('update', $activity);

        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string',
            'status' => 'required|in:pending,completed,cancelled,scheduled',
            'duration_minutes' => 'nullable|integer|min:0',
            'occurred_at' => 'required|date',
        ]);

        $activity->update($data);
        return back()->with('success', 'Attività aggiornata!');
    }

    public function destroy(Activity $activity)
    {
        $this->authorize('delete', $activity);
        $activity->delete();
        return back()->with('success', 'Attività eliminata.');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole(['admin','manager']);

        $query = $isAdmin ? Activity::query() : Activity::where('user_id', $user->id);

        if ($request->type) $query->where('type', $request->type);
        if ($request->customer_id) $query->where('customer_id', $request->customer_id);
        if ($request->date_from) $query->where('occurred_at', '>=', $request->date_from);
        if ($request->date_to) $query->where('occurred_at', '<=', $request->date_to . ' 23:59:59');

        $activities = $query
            ->with(['customer','user','attachments'])
            ->orderByDesc('occurred_at')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Activities/Index', [
            'activities' => $activities,
            'filters' => $request->only(['type','customer_id','date_from','date_to']),
        ]);
    }
}
