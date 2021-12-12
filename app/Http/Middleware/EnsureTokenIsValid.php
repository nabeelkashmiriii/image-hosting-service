<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Database;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // $token = $request->bearerToken();
        $connection = new Database('users');
        $db = $connection->getConnection();
        $find_token = $db->findOne(['jwt_token' => $request->bearerToken()]);
        if (empty($find_token)) {
            return response()->error([
                'message' => 'User Invalid',
            ], 404);
        }

        return $next($request);
    }
}
