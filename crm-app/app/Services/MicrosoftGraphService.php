<?php
namespace App\Services;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\EmailSyncLog;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MicrosoftGraphService
{
    protected Client $client;
    protected string $baseUrl = 'https://graph.microsoft.com/v1.0';
    protected string $tenantId;
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
        $this->tenantId = config('services.microsoft.tenant_id', 'common');
        $this->clientId = config('services.microsoft.client_id');
        $this->clientSecret = config('services.microsoft.client_secret');
        $this->redirectUri = config('services.microsoft.redirect_uri');
    }

    public function getAuthorizationUrl(): string
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => 'openid email profile Mail.Read Mail.Send Calendars.Read offline_access',
            'response_mode' => 'query',
            'state' => csrf_token(),
        ]);
        return "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/authorize?{$params}";
    }

    public function exchangeCodeForToken(string $code): array
    {
        $response = $this->client->post(
            "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token",
            ['form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
                'grant_type' => 'authorization_code',
            ]]
        );
        return json_decode($response->getBody(), true);
    }

    public function refreshToken(User $user): ?string
    {
        if (empty($user->microsoft_refresh_token)) return null;

        try {
            $response = $this->client->post(
                "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token",
                ['form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'refresh_token' => $user->microsoft_refresh_token,
                    'grant_type' => 'refresh_token',
                    'scope' => 'Mail.Read Mail.Send Calendars.Read offline_access',
                ]]
            );
            $tokens = json_decode($response->getBody(), true);
            $user->update([
                'microsoft_token' => $tokens['access_token'],
                'microsoft_refresh_token' => $tokens['refresh_token'] ?? $user->microsoft_refresh_token,
                'microsoft_token_expires_at' => now()->addSeconds($tokens['expires_in']),
            ]);
            return $tokens['access_token'];
        } catch (\Exception $e) {
            Log::error('Microsoft token refresh failed: ' . $e->getMessage());
            return null;
        }
    }

    public function getValidToken(User $user): ?string
    {
        if ($user->isMicrosoftConnected()) {
            return $user->microsoft_token;
        }
        return $this->refreshToken($user);
    }

    public function syncEmails(User $user, int $limit = 50): array
    {
        $token = $this->getValidToken($user);
        if (!$token) return ['error' => 'No valid Microsoft token'];

        $synced = 0;
        $matched = 0;

        try {
            $response = $this->client->get("{$this->baseUrl}/me/messages", [
                'headers' => ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'],
                'query' => [
                    '$top' => $limit,
                    '$select' => 'id,subject,from,toRecipients,bodyPreview,receivedDateTime,isRead,conversationId',
                    '$orderby' => 'receivedDateTime desc',
                ],
            ]);

            $emails = json_decode($response->getBody(), true)['value'] ?? [];

            foreach ($emails as $email) {
                // Skip if already synced
                if (EmailSyncLog::where('message_id', $email['id'])->exists()) {
                    continue;
                }

                $fromAddress = $email['from']['emailAddress']['address'] ?? null;
                $fromName = $email['from']['emailAddress']['name'] ?? null;

                // Try to match with a customer
                $customer = Customer::where('email', $fromAddress)->first();

                // Create email sync log
                $log = EmailSyncLog::create([
                    'user_id' => $user->id,
                    'message_id' => $email['id'],
                    'conversation_id' => $email['conversationId'] ?? null,
                    'subject' => $email['subject'] ?? '(No Subject)',
                    'from_address' => $fromAddress,
                    'from_name' => $fromName,
                    'to_addresses' => json_encode(array_map(
                        fn($r) => $r['emailAddress']['address'],
                        $email['toRecipients'] ?? []
                    )),
                    'body_preview' => $email['bodyPreview'] ?? null,
                    'is_read' => $email['isRead'] ?? false,
                    'direction' => 'inbound',
                    'customer_id' => $customer?->id,
                    'received_at' => $email['receivedDateTime'],
                ]);

                $synced++;

                // If matched with customer, create activity
                if ($customer) {
                    $activity = Activity::create([
                        'customer_id' => $customer->id,
                        'user_id' => $user->id,
                        'type' => 'email_incoming',
                        'subject' => $email['subject'] ?? '(No Subject)',
                        'body' => $email['bodyPreview'] ?? null,
                        'direction' => 'inbound',
                        'status' => 'completed',
                        'email_from' => $fromAddress,
                        'email_message_id' => $email['id'],
                        'outlook_conversation_id' => $email['conversationId'] ?? null,
                        'occurred_at' => $email['receivedDateTime'],
                        'metadata' => ['source' => 'outlook_sync'],
                    ]);

                    $log->update(['activity_id' => $activity->id]);
                    $customer->update(['last_contact_at' => $email['receivedDateTime']]);
                    $matched++;
                }
            }

            return ['synced' => $synced, 'matched' => $matched];
        } catch (\Exception $e) {
            Log::error('Email sync failed: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function sendEmail(User $user, array $data): bool
    {
        $token = $this->getValidToken($user);
        if (!$token) return false;

        try {
            $this->client->post("{$this->baseUrl}/me/sendMail", [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'message' => [
                        'subject' => $data['subject'],
                        'body' => ['contentType' => 'HTML', 'content' => $data['body']],
                        'toRecipients' => [[
                            'emailAddress' => ['address' => $data['to'], 'name' => $data['to_name'] ?? ''],
                        ]],
                    ],
                    'saveToSentItems' => true,
                ],
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Send email failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getUserProfile(string $token): array
    {
        try {
            $response = $this->client->get("{$this->baseUrl}/me", [
                'headers' => ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'],
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return [];
        }
    }
}
