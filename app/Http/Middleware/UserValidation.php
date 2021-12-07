<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Database;

class UserValidation
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
        //get Connection
        $connection = new Database('users');
        $db = $connection->getConnection();
        $user_data = $db->findOne(['email' => $request->email]);
        if(empty($user_data)){

            return

        }

        return $next($request);
    }
}
