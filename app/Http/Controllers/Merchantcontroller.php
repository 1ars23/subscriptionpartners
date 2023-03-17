<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtHelper;
use GuzzleHttp\Client;
use App\Models\Subscriber;
use App\Providers\JwtProvider;
use App\Models\Notification;

class MerchantController extends Controller
{

    public function subscribe(Request $request)
    {


        // Create a new subscriber with PENDING subscription status
        $subscriber = new Subscriber();
        $subscriber->subscription_status = 'PENDING';
        $subscriber->subscription_id = uniqid();
        $subscriber->msisdn = $request->input('msisdn');
        $subscriber->save();

        // Generate a JWT token to send to the partner
        $jwtToken = JwtHelper::generateJwtPayload($subscriber->subscription_id, $subscriber->msisdn, 'sub');

        // Send a request to the Partner to create a new subscription
        $client = new Client(['base_uri' => env('PARTNER_API_BASE_URI')]);
        $response = $client->request('POST', "subscribe/{$jwtToken}", [
            'headers' => [
                'Authorization' => "Bearer {$jwtToken}"
            ]
        ]);

        // Check if the request was successful
        if ($response->getStatusCode() == 200) {
            // Log the notification in the database
            $newNotification = new Notification();
            $newNotification->msisdn = $subscriber->msisdn;
            $newNotification->subscription_id = $subscriber->subscription_id;
            $newNotification->status = 'SUBSCRIBED';
            $newNotification->save();

            return response()->json(['status' => 'YOU HAVE SUCCESSFULLY SUBSCRIBED']);
        } else {
            // If the request failed, delete the subscriber from the database
            // $subscriber->delete();

            return response()->json(['status' => 'SUBSCRIPTION FAILED']);
        }
    }

    public function unsubscribe(Request $request)
    {
        // Find the subscriber in the database and update their subscription status to UNSUBSCRIBED
        $subscriber = Subscriber::where('msisdn', $request->input('msisdn'))
            ->where('subscription_status', 'ACTIVE')
            ->first();

        if ($subscriber) {
            // Generate a JWT token to send to the partner
            $jwtToken = JwtHelper::generateJwtPayload($subscriber->subscription_id, $subscriber->msisdn, 'unsub');

            // Send a request to the Partner to delete the subscription
            $client = new Client(['base_uri' => env('PARTNER_API_BASE_URI')]);
            $response = $client->request('POST', "unsubscribe/{$jwtToken}", [
                'headers' => [
                    'Authorization' => "Bearer {$jwtToken}"
                ]
            ]);

            // Check if the request was successful
            if ($response->getStatusCode() == 200) {
                // Update the subscriber's status in the database
                $subscriber->subscription_status = 'UNSUBSCRIBED';
                $subscriber->save();

                // Log the notification in the database
                $newNotification = new Notification();
                $newNotification->msisdn = $subscriber->msisdn;
                $newNotification->subscription_id = $subscriber->subscription_id;
                $newNotification->status = 'UNSUBSCRIBED';
                $newNotification->save();

                return response()->json(['status' => 'YOU HAVE SUCCESSFULLY UNSUBSCRIBED']);
            } else {
                return response()->json(['status' => 'UNSUBSCRIPTION FAILED']);
            }
        } else {
            return response()->json(['status' => 'MSISDN ID NOT FOUND OR INVALID']);
        }
    }
}
