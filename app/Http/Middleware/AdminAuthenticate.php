<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** 后台会话鉴权 */
class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $request->expectsJson()
                ? response()->json(['code' => 401, 'message' => '未登录'], 401)
                : redirect()->guest(route('admin.login'));
        }

        return $next($request);
    }
}
