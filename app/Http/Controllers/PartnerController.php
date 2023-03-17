<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\Subscriber;
use App\Providers\JwtProvider;

class PartnerController extends Controller
{
    public function subscribe(Request $request, $jwtToken)
    {
        try {
            // Verify the JWT token and extract the payload
            $payload = JwtProvider::decode($jwtToken);
        } catch (\Exception $e) {
            // Return an error response if the token is invalid
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Find the subscriber in the database by using subscription_id
        $subscriber = Subscriber::where('subscription_id', $payload->subscriptionId)
            ->where('subscription_status', 'PENDING')
            ->first();

        // If the subscriber is found, update their status to ACTIVE
        if ($subscriber) {
            $subscriber->subscription_status = 'ACTIVE';
            $subscriber->save();

            return response()->json(['status' => 'SUCCESS']);
        } else {
            return response()->json(['error' => 'Subscriber not found'], 404);
        }
    }

    public function unsubscribe(Request $request, $jwtToken)
    {
        // Parse the JWT payload
        $payload = JwtProvider::decode($jwtToken);

        // // Find the subscriber in the database and delete them
        // Subscriber::where('msisdn', $payload->msisdn)
        //     ->where('subscription_id', $payload->subscriptionId)
        //     ->delete();

        // // Return a response to the Merchant
        // return response()->json(['status' => 'SUCCESS']);

        // Find the subscriber in the database by using subscription_id
        $subscriber = Subscriber::where('msisdn', $payload->msisdn)
            ->where('subscription_status', 'ACTIVE')
            ->first();

        // If the subscriber is found, update their status to ACTIVE
        if ($subscriber) {
            $subscriber->subscription_status = 'UNSUBSCRIBE';
            $subscriber->save();

            return response()->json(['status' => 'SUCCESS']);
        } else {
            return response()->json(['error' => 'Subscriber not found'], 404);
        }
    }

    public function notification(Request $request)
    {
        // Get the JSON notification from the request body
        $notification = json_decode($request->getContent());

        // Log the notification in the database
        $newNotification = new Notification();
        $newNotification->msisdn = $notification->msisdn;
        $newNotification->subscription_id = $notification->subscriptionId;
        $newNotification->status = $notification->status;
        $newNotification->save();

        // Find the subscriber in the database and update their subscription status based on the notification
        $subscriber = Subscriber::where('msisdn', $notification->msisdn)
            ->where('subscription_id', $notification->subscriptionId)
            ->firstOrFail();

        if ($notification->status == 'SUCCESS') {
            $subscriber->subscription_status = 'ACTIVE';
        } else {
            $subscriber->subscription_status = 'FAILED';
        }

        $subscriber->save();

        // Return a response to the Partner
        return response()->json(['status' => 'OK']);
    }

}
