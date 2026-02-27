<?php
namespace App\Services;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\EmailSyncLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;

class ImapEmailService
{
    /**
     * Sincronizza le email dalla casella IMAP configurata nel .env
     * Funziona con: Outlook/Office 365, Gmail, qualsiasi server IMAP
     * NON richiede Azure AD.
     */
    public function syncEmails(User $user, int $limit = 50): array
    {
        // Verifica che l'utente abbia credenziali IMAP configurate
        $host     = $user->imap_host     ?? config('imap.accounts.default.host');
        $port     = $user->imap_port     ?? config('imap.accounts.default.port', 993);
        $username = $user->imap_username ?? config('imap.accounts.default.username');
        $password = $user->imap_password ?? config('imap.accounts.default.password');
        $protocol = $user->imap_protocol ?? config('imap.accounts.default.protocol', 'imap');
        $encryption = $user->imap_encryption ?? config('imap.accounts.default.encryption', 'ssl');

        if (!$host || !$username || !$password) {
            return ['error' => 'Credenziali IMAP non configurate per questo utente.'];
        }

        $synced  = 0;
        $matched = 0;

        try {
            $cm = new ClientManager();
            $client = $cm->make([
                'host'          => $host,
                'port'          => $port,
                'encryption'    => $encryption,
                'validate_cert' => false,
                'username'      => $username,
                'password'      => $password,
                'protocol'      => $protocol,
            ]);

            $client->connect();
            $inbox = $client->getFolder('INBOX');

            // Leggi le ultime $limit email
            $messages = $inbox->messages()
                ->setFetchOrder('desc')
                ->limit($limit)
                ->get();

            foreach ($messages as $message) {
                $messageId = $message->getMessageId()->first() ?? (string) $message->getUid();

                // Skip se già sincronizzata
                if (EmailSyncLog::where('message_id', $messageId)->where('user_id', $user->id)->exists()) {
                    continue;
                }

                $fromAddress = $message->getFrom()->first()?->mail ?? null;
                $fromName    = $message->getFrom()->first()?->personal ?? null;
                $subject     = $message->getSubject()->first() ?? '(Nessun oggetto)';
                $bodyPreview = substr(strip_tags($message->getTextBody() ?? $message->getHtmlBody() ?? ''), 0, 500);
                $receivedAt  = $message->getDate()->first() ?? now();

                $toAddresses = $message->getTo()->map(fn($r) => $r->mail)->toArray();

                // Match automatico con cliente per indirizzo email
                $customer = $fromAddress ? Customer::where('email', $fromAddress)->first() : null;

                // Crea il log
                $log = EmailSyncLog::create([
                    'user_id'       => $user->id,
                    'message_id'    => $messageId,
                    'conversation_id' => null,
                    'subject'       => $subject,
                    'from_address'  => $fromAddress,
                    'from_name'     => $fromName,
                    'to_addresses'  => json_encode($toAddresses),
                    'body_preview'  => $bodyPreview,
                    'is_read'       => false,
                    'direction'     => 'inbound',
                    'customer_id'   => $customer?->id,
                    'received_at'   => $receivedAt,
                ]);

                $synced++;

                // Se associato a un cliente, crea l'attività nella sua timeline
                if ($customer) {
                    $activity = Activity::create([
                        'customer_id'       => $customer->id,
                        'user_id'           => $user->id,
                        'type'              => 'email_incoming',
                        'subject'           => $subject,
                        'body'              => $bodyPreview,
                        'direction'         => 'inbound',
                        'status'            => 'completed',
                        'email_from'        => $fromAddress,
                        'email_to'          => implode(', ', $toAddresses),
                        'email_message_id'  => $messageId,
                        'occurred_at'       => $receivedAt,
                        'metadata'          => ['source' => 'imap_sync', 'imap_host' => $host],
                    ]);

                    $log->update(['activity_id' => $activity->id]);
                    $customer->update(['last_contact_at' => $receivedAt]);
                    $matched++;
                }
            }

            $client->disconnect();

            return ['synced' => $synced, 'matched' => $matched];

        } catch (\Exception $e) {
            Log::error("IMAP sync error per utente {$user->id}: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Invia un'email tramite SMTP (configurato in .env come MAIL_*)
     * Outlook: MAIL_HOST=smtp.office365.com, MAIL_PORT=587, MAIL_ENCRYPTION=tls
     */
    public function sendEmail(User $user, array $data): bool
    {
        try {
            \Illuminate\Support\Facades\Mail::raw($data['body'], function ($mail) use ($data, $user) {
                $mail->to($data['to'], $data['to_name'] ?? null)
                    ->subject($data['subject'])
                    ->from(config('mail.from.address'), $user->name);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('SMTP send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Testa la connessione IMAP con le credenziali fornite
     */
    public function testConnection(array $credentials): array
    {
        try {
            $cm = new ClientManager();
            $client = $cm->make([
                'host'          => $credentials['host'],
                'port'          => $credentials['port'] ?? 993,
                'encryption'    => $credentials['encryption'] ?? 'ssl',
                'validate_cert' => false,
                'username'      => $credentials['username'],
                'password'      => $credentials['password'],
                'protocol'      => 'imap',
            ]);
            $client->connect();
            $folders = $client->getFolders();
            $client->disconnect();

            return ['success' => true, 'folders' => $folders->map(fn($f) => $f->name)->toArray()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
