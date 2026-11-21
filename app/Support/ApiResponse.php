<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Generate a relative storage URL for public disk files.
     * Returns /storage/{path} so the client can prepend its own base URL.
     */
    public static function storageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return '/storage/'.$path;
    }

    public static function success(mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data ?? (object) [],
        ], $status);
    }

    public static function paginated(mixed $data, ?string $nextCursor = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => [
                'next_cursor' => $nextCursor,
            ],
        ], $status);
    }

    public static function error(
        string $message,
        string $code = 'error',
        ?array $errors = null,
        ?string $reason = null,
        int $status = 400
    ): JsonResponse {
        $payload = [
            'message' => $message,
            'code' => $code,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        if ($reason !== null) {
            $payload['reason'] = $reason;
        }

        return response()->json($payload, $status);
    }
}
