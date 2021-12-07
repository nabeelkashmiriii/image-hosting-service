<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Database;
use Exception;
use Illuminate\Support\Facades\hash;
use App\Services\JwtAuthentication;

class UserController extends Controller
{
    //User SignUp
    public function signup(Request $request)
    {


        try {
            // get connection
            $connection = new Database('users');
            $db = $connection->getConnection();

            $password = bcrypt($request->password);
            $email_exists = $db->findOne(['email' => $request->email]);
            if (!$email_exists) {
                $fileName = time() . '_' . $request->profile_pic->getClientOriginalName();
                $filePath = $request->profile_pic->storeAs('uploads', $fileName, 'public');
                $user = $db->insertOne([
                    'profile_pic' => '/storage/' . $filePath,
                    "name" => $request->name,
                    "age" => $request->age,
                    "email" => $request->email,
                    "password" => $password,
                    // "verify" => 1,
                ]);
            } else {
                return response()->error(['message' => 'User Already Exist'], 403);
            }
            UserController::sendEmail($request->name, $request->email);

            return response()->success([
                'message' => 'User successfully registered',
            ], 201);
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
    }

    // Send Email
    public static function sendEmail($name, $email)
    {
        try {
            $user = [
                'name' => $name,
                'info' => 'Press the Following Link to Verify Email',
                'Verification_link' => url('user/verifyEmail/' . $email)
            ];
            \Mail::to($email)->send(new \App\Mail\SendMail($user));
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
    }



    // verification
    public function verify($email)
    {
        try {
            $connection = new Database('users');
            $db = $connection->getConnection();
            $user_data = $db->findOne(['email' => $email]);
            // dd($data['verify']);
            if (isset($user_data['verify'])) {
                return response()->success(['message' => 'Your account has been verified'], 200);
            } else {
                $update = $db->updateOne(['email' => $email], ['$set' => ['verify' => 1]]);
                if ($update) {
                    return "Your Account has beem verified";
                } else {
                    return response()->error(['message' => 'Email Not verified verified'], 400);
                }
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
    }



    // User Login
    public function login(Request $request)
    {
        try {
            //get Connection
            $connection = new Database('users');
            $db = $connection->getConnection();
            $user_data = $db->findOne(['email' => $request->email]);

            if (!empty($user_data)) {

                if (Hash::check($request->password, $user_data->password)) {

                    $user = array(
                        "id" => (string)$user_data->_id,
                        "profile_pic" => $user_data->profile_pic,
                        "name" => $user_data->name,
                        "email" => $user_data->email,
                        "password" => $request->password,
                    );
                    // check condition for verified email
                    if (isset($user_data['verify'])) {

                        $jwt = new JwtAuthentication;
                        $token = $jwt->jwt_encode($user);
                        $db->updateOne(['email' => $request->email], ['$set' => ['jwt_token' => $token]]);

                        return response()->success([
                            'message' => 'User successfully Loged In',
                            'user' => $user,
                            'token' => $token,
                        ], 200);
                    } else {

                        UserController::sendEmail($user_data['name'], $user_data['email']);

                        return response()->error([
                            'message' => 'User email not verified Please Check Your email to verify',

                        ], 400);
                    }
                } else {
                    return response()->error([
                        'message' => 'incorrect Password',
                    ], 404);
                }
            } else {
                return response()->error([
                    'message' => 'User Not Found',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->error($e, 404);
        }
    }




    // Logout
    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        try {
            $connection = new Database('users');
            $db = $connection->getConnection();
            $db->updateOne(['jwt_token' => $token], ['$unset' => ['jwt_token' => null]]);
            return response()->success(['message' => 'Logout Successfully!!'], 200);
        } catch (\Exception $e) {
            return response()->error(['error' => $e->getMessage()], 500);
        }
    }
}
