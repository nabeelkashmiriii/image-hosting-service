<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Database;
use Illuminate\Support\Facades\hash;
// use App\Http\Requests\LoginRequest;

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
        if (empty($user_data)) {

            return response()->error([
                'message' => 'User Not Found',
            ], 404);
        } elseif (!isset($user_data['verify'])) {
            return response()->error([
                'message' => 'User email not verified Please Check Your email to verify',

            ], 400);
        } elseif (!Hash::check($request->password, $user_data->password)) {
            return response()->error([
                'message' => 'incorrect Password',
            ], 404);
        }
        return $next($request);
    }
}
