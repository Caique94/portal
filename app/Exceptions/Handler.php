<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into a response.
     */
    public function render(Request $request, Throwable $exception): Response
    {
        // Se é uma requisição AJAX/API, retorna JSON
        if ($request->expectsJson()) {
            return $this->handleJsonException($request, $exception);
        }

        // Se é uma requisição para uma rota de API interna (começa com /api ou /listar-)
        // Também retorna JSON para evitar erro de parsing no DataTables
        if ($request->is('api/*') || $request->is('listar-*') || $request->is('salvar-*') ||
            $request->is('toggle-*') || $request->is('excluir-*') || $request->is('remover-*')) {
            return $this->handleJsonException($request, $exception);
        }

        // Caso contrário, usa o comportamento padrão
        return parent::render($request, $exception);
    }

    /**
     * Handle JSON exception responses
     */
    private function handleJsonException(Request $request, Throwable $exception): JsonResponse
    {
        // CSRF Token mismatch / Sessão expirada
        if ($exception instanceof TokenMismatchException) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão expirada ou CSRF token inválido',
                'code' => 419,
            ], 419);
        }

        // Validação
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na validação dos dados',
                'errors' => $exception->errors(),
                'code' => 422,
            ], 422);
        }

        // Não autenticado
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
                'code' => 401,
            ], 401);
        }

        // Não autorizado
        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para acessar este recurso',
                'code' => 403,
            ], 403);
        }

        // Recurso não encontrado
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso não encontrado',
                'code' => 404,
            ], 404);
        }

        // Método não permitido
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Método HTTP não permitido',
                'code' => 405,
            ], 405);
        }

        // Rate limit
        if ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            return response()->json([
                'success' => false,
                'message' => 'Muitas requisições. Tente novamente mais tarde',
                'code' => 429,
                'retry_after' => $exception->getHeaders()['Retry-After'] ?? null,
            ], 429);
        }

        // Erro genérico de servidor
        $statusCode = method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode()
            : 500;

        $response = [
            'success' => false,
            'message' => 'Ocorreu um erro ao processar a requisição',
            'code' => $statusCode,
        ];

        // Em desenvolvimento, inclui detalhes do erro
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return response()->json($response, $statusCode);
    }
}
