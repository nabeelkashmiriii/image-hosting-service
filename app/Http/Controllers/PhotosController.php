<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Database;
use Exception;
use App\Services\JwtAuthentication;
use App\Http\Requests\UploadPhotoRequest;
use phpDocumentor\Reflection\Types\Null_;

class PhotosController extends Controller
{
    //Uplaod Photo
    public function uploadPhoto(UploadPhotoRequest $request)
    {
        try {
            $token = $request->bearerToken();
            $jwt = new JwtAuthentication;
            $decoded_data = $jwt->jwt_decode($token);

            $validator = $request->validated();
            $connection = new Database('photos');
            $db = $connection->getConnection();
            // dd($validator["photo"]);

            $fileName = $validator["photo"]->getClientOriginalName();
            $filePath = $validator["photo"]->storeAs('uploads', $fileName, 'public');
            $pathInfo = pathinfo($filePath);
            // dd(pathinfo($filePath));

            $upload_photo = $db->insertOne([
                'photo' => '/storage/' . $filePath,
                'pathInfo' => $pathInfo,
                'privacy' => "hidden",
                // 'private' => false,
                // 'public' => false,
                'user_id' => $decoded_data->data->id,
                'created_at' => date(DATE_RFC2822),
            ]);

            $upload_photo->shareableLink = url('storage/' . $filePath);
            // dd($upload_photo);

            if ($upload_photo) {
                return response()->success([
                    'message' => 'Your Photo has been Posted',
                    $upload_photo
                ], 201);
            } else {
                return response()->error(['message' => 'Failed to Upload'], 400);
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
    }

    public function deletePhoto(Request $request)
    {
        try {
            // Get Connection
            $connection = new Database('photos');
            $db = $connection->getConnection();

            $photo_id = $request->photo_id;




            $delete = $db->deleteOne(["_id" => new \MongoDB\BSON\ObjectId($photo_id)]);
            if ($delete) {

                return response()->success(['message' => 'deleted'], 201);
            } else {
                return response()->error(['message' => ' not deleted'], 404);
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
    }

    // Make Photo Public
    public function setPrivacy(Request $request)
    {
        try {
            // Get Connection
            $connection = new Database('photos');
            $db = $connection->getConnection();

            $photo_id = new \MongoDB\BSON\ObjectId($request->photo_id);
            $privacy = $request->privacy;
            switch ($privacy) {
                case "hidden":
                    $public = $db->updateOne(
                        ['_id' => $photo_id],
                        ['$set' => ['privacy' => 'hidden',]],
                    );
                    return response()->success(['message' => 'Set To Hidden'], 201);
                    break;
                case "public":
                    $public = $db->updateOne(
                        ['_id' => $photo_id],
                        ['$set' => ['privacy' => 'public',]],
                    );
                    return response()->success(['message' => 'Set To Public'], 201);
                    break;
                case "private":
                    $public = $db->updateOne(
                        ['_id' => $photo_id],
                        ['$set' => ['privacy' => 'private',]],
                    );
                    return response()->success(['message' => 'Set To Private'], 201);
                    break;
                default:
                    return response()->error(['message' => 'Something Went Wrong'], 404);
            }

            // $public = $db->updateOne(
            //     ['_id' => new \MongoDB\BSON\ObjectId($photo_id)],
            //     ['$set' => ['privacy' => 1]],
            // );

            //     if ($public) {

            //         return response()->success(['message' => 'Set To Public'], 201);
            //     } else {
            //         return response()->error(['message' => 'Something Went Wrong'], 404);
            //     }
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
    }


    // list all Photos
    public function listAllPhotos(Request $request)
    {

        try {
            $token = $request->bearerToken();
            $jwt = new JwtAuthentication;
            $decoded_data = $jwt->jwt_decode($token);
            // dd($decoded_data);
            // Get Connection
            $connection = new Database('photos');
            $db = $connection->getConnection();


            $user_id = $decoded_data->data->id;

            $result = $db->find(
                ['user_id' => $user_id],
            )->toArray();
            // dd($allPhotos);
            // var_dump($allPhotos);
            if (!empty($result)) {

                return response()->success(
                    [
                        'message' => 'Photo results',
                        'pics' => $result,
                    ],
                    200
                );
            } else {
                return response()->error(['message' => 'No Result Found'], 404);
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
    }


    // Search Photos

    public function searchPhoto(Request $request)
    {
        try {
            $query = $request->all();
            $token = $request->bearerToken();
            $jwt = new JwtAuthentication;
            $decoded_data = $jwt->jwt_decode($token);
            // dd($decoded_data);
            // Get Connection
            $connection = new Database('photos');
            $db = $connection->getConnection();

            $user_id = $decoded_data->data->id;

            if ($query['type'] === 'privacy') {
                $result = $db->find(
                    ['user_id' => $user_id, 'privacy' => $request->privacy],
                )->toArray();
                // return $result;
                if (!empty($result)) {

                    return response()->success(
                        [
                            'message' => 'Photo results',
                            'pics' => $result,
                        ],
                        200
                    );
                } else {
                    return response()->error(['message' => 'No Result Found'], 404);
                }
            } else if ($query['type'] === 'name') {
                $result = $db->find(
                    ['user_id' => $user_id, 'pathInfo.filename' => $request->name],
                )->toArray();
                // return $result;
                if (!empty($result)) {

                    return response()->success(
                        [
                            'message' => 'Photo results',
                            'pics' => $result,
                        ],
                        200
                    );
                } else {
                    return response()->error(['message' => 'No Result Found'], 404);
                }
            } else if ($query['type'] === 'ext') {
                $result = $db->find(
                    ['user_id' => $user_id, 'pathInfo.extension' => $request->ext],
                )->toArray();
                // return $result;
                if (!empty($result)) {

                    return response()->success(
                        [
                            'message' => 'Photo results',
                            'pics' => $result,
                        ],
                        200
                    );
                } else {
                    return response()->error(['message' => 'No Result Found'], 404);
                }
            } else {
                return response()->error(['message' => 'No Result Found'], 404);
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
    }
}
