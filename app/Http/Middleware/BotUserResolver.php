<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BotUserResolver
{
    public function handle(
        Request $request,
        Closure $next
    ): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }

        $request->merge([
            'botUser' => $user
        ]);

        $request->attributes->set(
            'botUser',
            $user
        );

        $request->botUser = $user;

        return $next($request);
    }
}
