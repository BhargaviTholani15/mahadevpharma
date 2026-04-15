<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('admin.login');
        }

        if (!in_array($user->role?->slug, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied for your role'], 403);
            }
            abort(403, 'Access denied for your role.');
        }

        return $next($request);
    }
}
