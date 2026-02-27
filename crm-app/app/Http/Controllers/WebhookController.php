<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Microsoft Graph webhook notifications (email push notifications)
     * This requires setting up a subscription in Microsoft Graph
     */
    public function outlookNotification(Request $request)
    {
        // Validation token (first time subscription verification)
        if ($request->has('validationToken')) {
            return response($request->validationToken, 200)->header('Content-Type', 'text/plain');
        }

        $notifications = $request->json('value', []);

        foreach ($notifications as $notification) {
            try {
                $this->processEmailNotification($notification);
            } catch (\Exception $e) {
                Log::error('Outlook webhook error: ' . $e->getMessage(), $notification);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    protected function processEmailNotification(array $notification): void
    {
        if ($notification['changeType'] !== 'created') return;

        $resourceData = $notification['resourceData'] ?? [];
        $messageId = $resourceData['id'] ?? null;
        if (!$messageId) return;

        // Find the user by subscription ID
        // In a real implementation, store subscription IDs per user
        $userId = $notification['clientState'] ?? null;
        if (!$userId) return;

        $user = User::find($userId);
        if (!$user) return;

        // Sync emails for this user
        app(\App\Services\MicrosoftGraphService::class)->syncEmails($user, 10);
    }
}
