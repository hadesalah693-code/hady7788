<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountType
{
    /**
     * Allow the request only when the authenticated user has one of the given account types.
     */
    public function handle(Request $request, Closure $next, string ...$types): Response
    {
        $user = $request->user();

        abort_if($user === null, 403);

        $accountType = $user->account_type?->value;

        abort_unless(in_array($accountType, $types, true), 403);

        return $next($request);
    }
}
