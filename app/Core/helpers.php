<?php

use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

function apiCustomResponse($message,$code,$detail){
    $response = [
        'message' => $message,
        'code' => $code,
        'data' => $detail
    ];
    return response($response,$code)
        ->header('Content-Type', 'application/json');
}

function sendFCMNotificationPanel($title,$message,$tokens,$data)
{


    $optionBuilder = new OptionsBuilder();
    $optionBuilder->setTimeToLive(60 * 20);
    $optionBuilder->setPriority('high');
    $optionBuilder->setContentAvailable(1);


    $notificationBuilder = new PayloadNotificationBuilder($title);
    $notificationBuilder->setBody($message)
        ->setSound('default');

    $dataBuilder = new PayloadDataBuilder();
    $dataBuilder->addData(['data' => $data]);


    $option = $optionBuilder->build();
    $notification = $notificationBuilder->build();
    $data = $dataBuilder->build();
    $downstreamResponse = FCM::sendTo($tokens, null, null, $data);

    $result = array(
        'success' => $downstreamResponse->numberSuccess(),
        'fail' => $downstreamResponse->numberFailure()
    );


    return $result;


}




