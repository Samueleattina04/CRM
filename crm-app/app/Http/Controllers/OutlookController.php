<?php
namespace App\Http\Controllers;

use App\Services\MicrosoftGraphService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OutlookController extends Controller
{
    public function __construct(protected MicrosoftGraphService $graph) {}

    public function connect()
    {
        return redirect($this->graph->getAuthorizationUrl());
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('dashboard')
                ->with('error', 'Connessione Outlook annullata: ' . $request->error_description);
        }

        try {
            $tokens = $this->graph->exchangeCodeForToken($request->code);
            $profile = $this->graph->getUserProfile($tokens['access_token']);

            $user = Auth::user();
            $user->update([
                'microsoft_token' => $tokens['access_token'],
                'microsoft_refresh_token' => $tokens['refresh_token'] ?? null,
                'microsoft_token_expires_at' => now()->addSeconds($tokens['expires_in']),
                'microsoft_user_id' => $profile['id'] ?? null,
            ]);

            return redirect()->route('dashboard')->with('success', 'Outlook connesso con successo!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Errore connessione Outlook: ' . $e->getMessage());
        }
    }

    public function disconnect()
    {
        Auth::user()->update([
            'microsoft_token' => null,
            'microsoft_refresh_token' => null,
            'microsoft_token_expires_at' => null,
            'microsoft_user_id' => null,
        ]);
        return back()->with('success', 'Outlook disconnesso.');
    }

    public function sync()
    {
        $result = $this->graph->syncEmails(Auth::user());

        if (isset($result['error'])) {
            return back()->with('error', 'Errore sync: ' . $result['error']);
        }

        return back()->with('success', "Sync completato: {$result['synced']} email importate, {$result['matched']} associate a clienti.");
    }
}
