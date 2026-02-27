<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'avatar_url' => $request->user()->avatar_url,
                    'position' => $request->user()->position,
                    'roles' => $request->user()->getRoleNames(),
                    'is_microsoft_connected' => $request->user()->isMicrosoftConnected(),
                    'is_imap_connected' => $request->user()->isImapConnected(),
                    'is_email_connected' => $request->user()->isEmailConnected(),
                    'imap_last_sync_at' => $request->user()->imap_last_sync_at,
                    'imap_host' => $request->user()->imap_host,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
