<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para validar Content-Type e tamanho de payload
 */
class ValidateContentType
{
    /**
     * Tamanhos máximos por tipo de requisição (em bytes)
     */
    protected array $maxSizes = [
        'application/json' => 1048576,      // 1 MB
        'multipart/form-data' => 10485760,  // 10 MB
        'application/x-www-form-urlencoded' => 1048576, // 1 MB
    ];

    /**
     * Content-Types permitidos
     */
    protected array $allowedContentTypes = [
        'application/json',
        'multipart/form-data',
        'application/x-www-form-urlencoded',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Apenas para requisições com body (POST, PUT, PATCH)
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }

        // Validar Content-Type
        $contentType = $request->header('Content-Type');

        if (!$contentType) {
            return response()->json([
                'success' => false,
                'message' => 'Header Content-Type é obrigatório'
            ], 400);
        }

        // Extrair apenas o tipo (remover charset, boundary, etc)
        $baseContentType = explode(';', $contentType)[0];
        $baseContentType = trim($baseContentType);

        // Verificar se Content-Type é permitido
        $isAllowed = false;
        foreach ($this->allowedContentTypes as $allowed) {
            if (str_starts_with($baseContentType, $allowed)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            \Log::warning('Content-Type não permitido', [
                'content_type' => $contentType,
                'ip' => $request->ip(),
                'route' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Content-Type não suportado',
                'allowed_types' => $this->allowedContentTypes,
            ], 415);
        }

        // Validar tamanho do payload
        $contentLength = $request->header('Content-Length', 0);
        $maxSize = $this->maxSizes[$baseContentType] ?? 1048576;

        if ($contentLength > $maxSize) {
            \Log::warning('Payload muito grande', [
                'content_length' => $contentLength,
                'max_size' => $maxSize,
                'content_type' => $contentType,
                'ip' => $request->ip(),
                'route' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payload muito grande',
                'max_size' => $maxSize,
                'received_size' => $contentLength,
            ], 413);
        }

        return $next($request);
    }
}
