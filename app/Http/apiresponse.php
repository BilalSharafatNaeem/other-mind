<?php


namespace App\Http;


class apiresponse
{
    /*Success Response*/

    public function customResponse($message,$code,$detail)
    {
        $response = [
            'message' => $message,
            'code' => $code,
            'data' => $detail
        ];

        return response($response,$code)
            ->header('Content-Type', 'application/json');
    }

    public function customResponses($message,$code,$detail,$metadata = null)
    {
        if(!$detail) {
           return $response = [
                'message' => $message,
                'code' => $code,
                'data' => $detail
            ];
        }

//        return response($response,$code)
//            ->header('Content-Type', 'application/json');

        return $detail->additional(
            array_merge(
                $detail->additional,
                [
                    'message' => $message,
                    'code' => $code,
                    'meta' => [
                        'custom' => $metadata
                    ],
                ]
            )
        );
    }



}
