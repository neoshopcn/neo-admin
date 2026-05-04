<?php

namespace App\Http\Middleware;

use App\Models\AdminOperationLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 记录后台 API 写操作
 */
class LogAdminOperation
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        if (! auth()->check()) {
            return $response;
        }

        $user = auth()->user();
        $payload = $request->except(['password', 'password_confirmation', '_token']);
        AdminOperationLog::query()->create([
            'user_id' => $user->id,
            'username' => $user->username,
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'action' => $request->route()?->getName() ?? '',
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        return $response;
    }
}
