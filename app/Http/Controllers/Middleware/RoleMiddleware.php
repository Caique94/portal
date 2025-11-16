<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $u = $request->user();
        if (!$u) return redirect()->route('login');
        if (!in_array($u->papel, $roles, true)) abort(403, 'Acesso negado.');
        return $next($request);
    }
}
