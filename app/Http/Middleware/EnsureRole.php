<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            return ApiResponse::error(
                message: 'Anda tidak memiliki hak akses untuk tindakan ini.',
                code: 'forbidden',
                status: 403
            );
        }

        return $next($request);
    }
}
