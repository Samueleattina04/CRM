<?php
namespace App\Http\Controllers;

use App\Services\ImapEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ImapController extends Controller
{
    public function __construct(protected ImapEmailService $imap) {}

    /**
     * Salva le credenziali IMAP dell'utente e testa la connessione
     */
    public function saveCredentials(Request $request)
    {
        $data = $request->validate([
            'imap_host'       => 'required|string|max:255',
            'imap_port'       => 'required|integer|min:1|max:65535',
            'imap_encryption' => 'required|in:ssl,tls,none',
            'imap_username'   => 'required|string|max:255',
            'imap_password'   => 'required|string',
            'imap_protocol'   => 'required|in:imap,imap4',
        ]);

        // Testa la connessione prima di salvare
        $test = $this->imap->testConnection($data);
        if (!$test['success']) {
            return back()->withErrors(['imap' => 'Connessione IMAP fallita: ' . $test['error']]);
        }

        // Salva la password cifrata
        $data['imap_password'] = Crypt::encryptString($data['imap_password']);

        Auth::user()->update($data);

        return back()->with('success', 'Credenziali IMAP salvate e testate con successo!');
    }

    /**
     * Sincronizza manualmente le email
     */
    public function sync()
    {
        $user = Auth::user();

        // Decifra la password IMAP prima della sync
        if ($user->imap_password) {
            try {
                $user->imap_password = Crypt::decryptString($user->imap_password);
            } catch (\Exception $e) {
                return back()->with('error', 'Errore credenziali IMAP. Riconfigura la connessione email.');
            }
        }

        $result = $this->imap->syncEmails($user);

        $user->updateQuietly(['imap_last_sync_at' => now()]);

        if (isset($result['error'])) {
            return back()->with('error', 'Errore sync email: ' . $result['error']);
        }

        return back()->with('success', "Sync completata: {$result['synced']} email importate, {$result['matched']} associate a clienti.");
    }

    /**
     * Disconnetti / cancella credenziali IMAP
     */
    public function disconnect()
    {
        Auth::user()->update([
            'imap_host'        => null,
            'imap_port'        => null,
            'imap_encryption'  => null,
            'imap_username'    => null,
            'imap_password'    => null,
            'imap_protocol'    => null,
            'imap_last_sync_at'=> null,
        ]);

        return back()->with('success', 'Account email disconnesso.');
    }

    /**
     * Testa la connessione con credenziali inserite senza salvare
     */
    public function testConnection(Request $request)
    {
        $data = $request->validate([
            'imap_host'       => 'required|string',
            'imap_port'       => 'required|integer',
            'imap_encryption' => 'required|string',
            'imap_username'   => 'required|string',
            'imap_password'   => 'required|string',
        ]);

        $result = $this->imap->testConnection($data);

        return response()->json($result);
    }
}
