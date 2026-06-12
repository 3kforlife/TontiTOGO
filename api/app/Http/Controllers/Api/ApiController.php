<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    /**
     * Réponse JSON succès standardisée.
     */
    protected function success(mixed $data = null, string $message = 'Opération réussie', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Réponse JSON erreur standardisée.
     */
    protected function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Réponse JSON ressource créée (201).
     */
    protected function created(mixed $data = null, string $message = 'Ressource créée avec succès'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Réponse JSON sans contenu (204).
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
