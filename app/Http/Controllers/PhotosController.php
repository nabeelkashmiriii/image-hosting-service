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

            $fileName = time() . '_' . $validator["photo"]->getClientOriginalName();
            $filePath = $validator["photo"]->storeAs('uploads', $fileName, 'public');

            $upload_photo = $db->insertOne([
                'photo' => '/storage/' . $filePath,
                'privacy' => 0,
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
    public function makePublic(Request $request)
    {
        try {
            // Get Connection
            $connection = new Database('photos');
            $db = $connection->getConnection();

            $photo_id = $request->photo_id;

            $public = $db->updateOne(
                ['_id' => new \MongoDB\BSON\ObjectId($photo_id)],
                ['$set' => ['privacy' => 1]],
            );

            if ($public) {

                return response()->success(['message' => 'Set To Public'], 201);
            } else {
                return response()->error(['message' => 'Something Went Wrong'], 404);
            }
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

            $allPhotos = $db->find(
                ['user_id' => $user_id],
            )->toArray();
            // dd($allPhotos);
            if (!empty($allPhotos)) {

                return response()->success(
                    [
                        'message' => 'Photo results',
                        'pics' => $allPhotos,
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
}
