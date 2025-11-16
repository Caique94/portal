<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Retorna sucesso com dados
     */
    protected function respondSuccess($data = null, string $message = 'Operação realizada com sucesso', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Retorna sucesso com paginação
     */
    protected function respondSuccessPaginated($data, string $message = 'Operação realizada com sucesso', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'meta' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ],
        ], $statusCode);
    }

    /**
     * Retorna erro
     */
    protected function respondError(string $message = 'Ocorreu um erro', array $errors = [], int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Retorna erro de validação (422)
     */
    protected function respondValidationError(array $errors, string $message = 'Erro na validação dos dados'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }

    /**
     * Retorna recurso não encontrado (404)
     */
    protected function respondNotFound(string $message = 'Recurso não encontrado'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 404);
    }

    /**
     * Retorna não autorizado (403)
     */
    protected function respondForbidden(string $message = 'Você não tem permissão para acessar este recurso'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }

    /**
     * Retorna não autenticado (401)
     */
    protected function respondUnauthorized(string $message = 'Não autenticado'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }

    /**
     * Retorna criado (201)
     */
    protected function respondCreated($data, string $message = 'Recurso criado com sucesso'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 201);
    }

    /**
     * Retorna sem conteúdo (204)
     */
    protected function respondNoContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
