<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isBanned()) {
            $user->tokens()->delete();

            return ApiResponse::error(
                message: 'Akun Anda telah dinonaktifkan.',
                code: 'account_banned',
                reason: $user->banned_reason,
                status: 403
            );
        }

        return $next($request);
    }
}
