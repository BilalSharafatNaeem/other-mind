<?php

namespace App\Http\Controllers;

use App\Http\apiresponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Pushok\Client;
use Pushok\Notification;
use Pushok\Payload;
use Pushok\Payload\Alert;

class CallController extends Controller
{
    public function createCall(Request $request){
        $token = $request->fcm_token;
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required',
            'type' => 'required|in:android,ios',
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
        );
        if($request->type == 'android'){
            sendFCMNotificationPanel('Hello', null, $token, $data);
        }else{
            $this->sendVoip(null,null,'default',$data,$token,null,null);
        }
        return $response = (new apiresponse())->customResponse(
            'call created successfully',
             200,
            (object)[]);

    }
    public function sendVoip($title, $body, $sound, $data, $token, $name, $bundleId)
    {
        try {
            $expiryInSeconds = 30;
            $options = [
                'key_id' => 'Y2U7L4XL5Y',
                'team_id' => '5MVY25HYQD',
                'app_bundle_id' => $bundleId,
                'private_key_path' => public_path('/certificate') . '/AuthKey_Y2U7L4XL5Y.p8', // Path to private key
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


            $client = new Client($authProvider, $production = true);
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

}
