<?php

namespace App\Http\Middleware;

use App\Support\AdminPermission as Perm;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** permission_code 路由鉴权 */
class AdminPermission
{
    public function handle(Request $request, Closure $next, string $code): Response
    {
        $user = auth()->user();

        if (! Perm::userHasCode($user, $code)) {
            return $request->expectsJson()
                ? response()->json(['code' => 403, 'message' => '无权访问'], 403)
                : response()->view('admin.errors.forbidden', [], 403);
        }

        return $next($request);
    }
}
