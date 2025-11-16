<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;

class EnsureUserIsActive {
    public function handle(Request $request, Closure $next){
        $u = $request->user();
        if($u && !$u->ativo){ auth()->logout(); return redirect()->route('login')->withErrors(['email'=>'Usuário inativo.']); }
        return $next($request);
    }
}
