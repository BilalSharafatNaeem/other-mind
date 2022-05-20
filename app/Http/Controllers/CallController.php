<?php

namespace App\Http\Controllers;

use App\Http\apiresponse;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Pushok\AuthProvider;
use Pushok\Client;
use Pushok\Notification;
use Pushok\Payload;
use Pushok\Payload\Alert;

class CallController extends Controller
{
    public function createCall(Request $request){
        try{
            $token = $request->fcm_token;
            $production  = $request->production;
            $validator = Validator::make($request->all(), [
                'fcm_token' => 'required',
                'type' => 'required|in:android,ios',
                'production' => 'required_if:type,ios',
            ]);
            if ($validator->fails()) {
                DB::rollback();
                return $response = (new apiresponse())->customResponse('Fields required.',
                    422,
                    $validator->errors()->toArray());
            }
            $data = (object)array(
                'fcm_token' => $request->fcm_token,
                'type' => $request->type,
                'data'=>$request->data
            );
            if($request->type == 'android'){
                sendFCMNotificationPanel(null, null, $token, $data);
            }else{
//                $bundleId = 'OtherMindEPTEST123';
                $this->sendVoip('testing','','call_tone.mp3',$data,$token,'',$production);
            }
            return $response = (new apiresponse())->customResponse(
                'call created successfully',
                200,
                (object)[]);
        }catch (\Exception $ex) {
            return $response = (new apiresponse())->customResponse(
                'Something went wrong. Please try again.',
                500,
                $ex->getMessage()
            );
        }


    }
    public function sendVoip($title, $body, $sound, $data, $token, $name,$production)
    {
        try {
            $expiryInSeconds = 30;
            $options = [
                'key_id' => '3RK6VXKN39',
                'team_id' => 'V42DL773S5',
                'app_bundle_id' => 'org.name.othermind',
                'private_key_path' => public_path('/certificate') . '/AuthKey_3RK6VXKN39.p8', // Path to private key
                'private_key_secret' => null // Private key secret
            ];

            $authProvider = AuthProvider\Token::create($options);
            $alert = Alert::create()->setTitle($title);
            $alert = $alert->setBody($body);
            $payload = Payload::create()->setAlert($alert);
            $payload->setSound($sound);
            $payload->setCustomValue('data', $data);
            $payload->setCustomValue('caller_name', $name);
            $payload->setCustomValue('handle', 'handle');
            $payload->setPushType('voip');
            $deviceTokens = [$token];
            $notifications = [];
            foreach ($deviceTokens as $deviceToken) {
                $notification = new Notification($payload, $deviceToken);
                $notification->setExpirationAt(date_create(date('Y-m-d H:i:s', time() + $expiryInSeconds), timezone_open('UTC')));
                $notifications[] = $notification;
            }

            $client = new Client($authProvider, $production);
            $client->addNotifications($notifications);
            $responses = $client->push();

//      foreach ($responses as $response) {
//          $result = array(
//              $response->getStatusCode(),
//              $response->getReasonPhrase(),
//              $response->getErrorReason(),
//              $response->getErrorDescription(),
//          );
//      }

            return $responses;
        } catch (\Exception $ex) {
            return $response = (new apiresponse())->customResponse(
                'Too many attempts.',
                422,
                $ex->getMessage()
            );
        }
    }
    public function latLong(Request $request){
        $lat = $request->get('lat');
        $long = $request->get('long');
        $fileUrl = '';
        if($request->hasFile('image')){
            $image = $request->file('image');
            $fileUrl = $this->fileUploads($image,'images/','images');
        }
        $response = (object)[
            'lat'=> $lat ?? "",
            'log'=> $long ?? "",
            'image'=>$fileUrl
        ];
        return $response = (new apiresponse())->customResponse(
            'Record created Successfully.',
            200,
            $response);
    }

    function fileUploads($file, $path, $destination)
    {
        if ($file) {
            $extension = $file->getClientOriginalExtension();
            $fileName = rand(11111, 99999) . '.' . $extension;
            $success = $file->move($destination, $fileName);
            return url("{$path}{$fileName}");
        }
        return null;
    }

    public function login(Request $request)
    {
        try {
            $username = $request->get('username');
            $password = $request->get('password');
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return $response = (new apiresponse())->customResponse('Fields required!',
                    422,
                    $validator->errors()->toArray());
            }

            $fieldType = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            $getUser = User::where($fieldType,$username)->first();
            if ($getUser) {
                $check_password = Hash::check($password, $getUser->password);
                if ($check_password) {
                    return $response = (new apiresponse())->customResponse(
                        'User Successfully Login!',
                        200,
                        $getUser);
                } else {
                    return $response = (new apiresponse())->customResponse(
                        'Incorrect username or password!',
                        422,
                        (object)[]);
                }
            } else {
                return $response = (new apiresponse())->customResponse(
                    'User does not exist!',
                    422,
                    (object)[]);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return $response = (new apiresponse())->customResponse(
                'Something went wrong, Please try again!',
                500,
                $ex->getMessage()
            );
        }
    }



}
