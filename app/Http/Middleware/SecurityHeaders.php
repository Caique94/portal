<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para adicionar headers de segurança
 *
 * Proteção contra: XSS, Clickjacking, MIME sniffing, etc.
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevenir XSS (Cross-Site Scripting)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Prevenir Clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevenir MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Forçar HTTPS (apenas em produção)
        if (config('app.env') === 'production') {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content Security Policy (CSP)
        // Ajuste conforme necessário para seu app
        $isDev = config('app.env') !== 'production';

        // Base CSP rules
        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com";
        $connectSrc = "'self' https://viacep.com.br";

        // Add Vite dev server in development/local environments
        if ($isDev) {
            $scriptSrc .= " http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173";
            $connectSrc .= " ws://localhost:5173 ws://127.0.0.1:5173 ws://[::1]:5173 http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173";
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.bunny.net",
            "font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com https://fonts.bunny.net data:",
            "img-src 'self' data: https: blob:",
            "connect-src {$connectSrc}",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (antigo Feature Policy)
        $permissions = implode(', ', [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
        ]);
        $response->headers->set('Permissions-Policy', $permissions);

        // Remover headers que expõem informações do servidor
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
