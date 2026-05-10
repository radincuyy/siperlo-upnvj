<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isRole(...$roles)) {
            abort(403, 'Akses halaman ini tidak sesuai dengan role akun.');
        }

        return $next($request);
    }
}
