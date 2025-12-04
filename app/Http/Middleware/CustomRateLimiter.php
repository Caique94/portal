<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomRateLimiter
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        // Criar chave única baseada em IP + rota + método
        $key = $this->resolveRequestSignature($request);

        // Verificar se excedeu o limite
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = $this->limiter->availableIn($key);

            \Log::warning('Rate limit excedido', [
                'ip' => $request->ip(),
                'route' => $request->path(),
                'method' => $request->method(),
                'user_id' => $request->user()?->id,
                'retry_after' => $retryAfter,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Muitas requisições. Tente novamente em alguns segundos.',
                'retry_after' => $retryAfter,
            ], 429)->header('Retry-After', $retryAfter);
        }

        // Incrementar contador
        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Adicionar headers informativos
        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $this->limiter->remaining($key, $maxAttempts),
        ]);
    }

    /**
     * Resolve a assinatura única da requisição
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $userId = $request->user()?->id ?? 'guest';

        return sha1(
            $userId . '|' .
            $request->ip() . '|' .
            $request->path() . '|' .
            $request->method()
        );
    }
}
