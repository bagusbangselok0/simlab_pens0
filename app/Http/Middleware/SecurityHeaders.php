<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Tambahkan HTTP Security Headers ke seluruh respons aplikasi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $viteDev = app()->environment(['local', 'development']) || is_file(public_path('hot'));
        $viteHttp = $viteDev ? ' http://localhost:5173 http://127.0.0.1:5173' : '';
        $viteWs = $viteDev ? ' ws://localhost:5173 ws://127.0.0.1:5173' : '';

        $cspDirectives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'{$viteHttp}",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com{$viteHttp}",
            "img-src 'self' data: blob: https://api.qrserver.com",
            "font-src 'self' data: https://fonts.bunny.net https://fonts.gstatic.com",
            "connect-src 'self' https://cdn.datatables.net{$viteHttp}{$viteWs}",
            "worker-src 'self' blob:",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ];

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()'
        );
        $response->headers->set('Content-Security-Policy', implode('; ', $cspDirectives));

        return $response;
    }
}
