<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SecurityAudit
{
    /**
     * Catat jejak audit keamanan terstruktur tanpa membocorkan kredensial/rahasia.
     *
     * @param  array<string, mixed>  $context
     */
    public static function log(string $event, array $context = [], string $level = 'info'): void
    {
        $safeContext = [];
        foreach ($context as $key => $value) {
            if (preg_match('/password|token|secret|private_key|signature_data/i', (string) $key)) {
                $safeContext[$key] = '[REDACTED]';
                continue;
            }

            $safeContext[$key] = $value;
        }

        $req = request();
        $payload = array_merge([
            'event' => $event,
            'actor_id' => Auth::id(),
            'actor_email' => Auth::user()?->email,
            'ip' => $req ? $req->ip() : null,
            'user_agent' => $req ? substr((string) $req->userAgent(), 0, 200) : null,
        ], $safeContext);

        $message = "[SECURITY_AUDIT] {$event}";

        if ($level === 'warning') {
            Log::warning($message, $payload);
            return;
        }

        Log::info($message, $payload);
    }
}
