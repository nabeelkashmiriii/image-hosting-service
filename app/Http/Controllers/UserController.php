<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Database;
use Exception;
use Illuminate\Support\Facades\hash;
use App\Services\JwtAuthentication;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\ForgotPassRequest;

class UserController extends Controller
{
    //User SignUp
    public function signup(SignupRequest $request)
    {


        try {
            // get connection
            $connection = new Database('users');
            $db = $connection->getConnection();
            $validated = $request->validated();
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
            UserController::sendEmail($validated['name'], $validated['email']);

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
    public function login(LoginRequest $request)
    {
        try {
            //get Connection
            $connection = new Database('users');
            $db = $connection->getConnection();
            $user_data = $db->findOne(['email' => $request->email]);
            $user = array(
                "id" => (string)$user_data->_id,
                "profile_pic" => $user_data->profile_pic,
                "name" => $user_data->name,
                "email" => $user_data->email,
                "password" => $request->password,
            );

            $jwt = new JwtAuthentication;
            $token = $jwt->jwt_encode($user);
            $db->updateOne(['email' => $request->email], ['$set' => ['jwt_token' => $token]]);

            return response()->success([
                'message' => 'User successfully Loged In',
                'user' => $user,
                'token' => $token,
            ], 200);
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


    // Forget password
    public function forgotPassword(ForgotPassRequest $request)
    {
        try {
            $connection = new Database('users');
            $db = $connection->getConnection();
            $forgetpass = $db->findone(['email' => $request->email]);
            // dd($forgetpass);

            $string = "ABC";
            $password = substr(str_shuffle(str_repeat($string, 12)), 0, 12);
            $forgetpass->password = ($password);

            $details['link'] = url('user/resetpass/' . $forgetpass->password . '/' . $forgetpass->email . '/');
            \Mail::to($request->email)->send(new \App\Mail\ForgotPassword($details));
            if ($details) {
                $success['message'] =  "New Password Send to Your Mail";
                return response()->success([$success, 200]);
            } else {
                $success['message'] =  "Something went wrong";
                return response()->error($success, 404);
            }
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
    }

    //Reset Password
    public function resetPassword($password, $email)
    {
        try {
            $new_pass = bcrypt($password);
            $connection = new Database('users');
            $db = $connection->getConnection();
            $user_data = $db->updateOne(
                ['email' => $email],
                ['$set' => ['password' => $new_pass]]
            );
            // echo $password;
            // dd($user_data);
            if ($user_data) {
                return response()->success([
                    'message' => 'Your password has been set',
                    'password' => $password
                ], 200);
            } else {
                return response()->error(['message' => 'Someting went Wrong'], 400);
            }
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
    }



    // Profile Update
    public function updateProfile(ProfileUpdateRequest $request, $id)
    {
        try {
            $connection = new Database('users');
            $db = $connection->getConnection();
            $user_exist = (array)$db->findOne([
                '_id' => new \MongoDB\BSON\ObjectId("$id")
            ]);

            $user_exist['name'] = $request->input('name');
            $user_exist['email'] = $request->input('email');
            $user_exist['password'] = bcrypt($request->input('password'));
            // $user_exist['password'] = $request->input('password');
            $user_exist['age'] = $request->input('age');
            // $user_exist['profile_pic'] =
            $input = $request;
            $fileName = null;
            if (!empty($input['profile_pic'])) {

                $fileName = time() . '_' . $request->profile_pic->getClientOriginalName();
                $filePath = $request->profile_pic->storeAs('uploads', $fileName, 'public');
                $user_exist['profile_pic'] = '/storage/' . $filePath;
            }
            // dd($fileName);
            $update = $db->updateOne(
                ['_id' => new \MongoDB\BSON\ObjectId("$id")],
                [
                    '$set' => $user_exist
                ]
            );

            // dd($user_exist);
            if ($update->getModifiedCount()) {
                return response()->success([
                    'message' => 'User Updated',
                    'data' => $user_exist,
                ], 200);
            } else {
                return response()->error([
                    'message' => 'Something Went Wrong',
                    'data' => $user_exist
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->error(['error' => $e->getMessage()], 500);
        }
    }
}
