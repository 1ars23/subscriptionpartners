<?php

namespace App\Helpers;

use Firebase\JWT\JWT;

class JwtHelper
{
    public static function generateJwtPayload($subscriptionId, $msisdn, $action)
    {
        // Set the token payload parameters
        $issuer = env('JWT_ISSUER');
        $audience = env('JWT_AUDIENCE');
        $now = time();
        $expirationTime = $now + env('JWT_EXPIRATION_TIME');
        $notBefore = $now;
        $tokenId = base64_encode(random_bytes(32));

        // Set the token payload
        $payload = [
            'iss' => $issuer,
            'aud' => $audience,
            'exp' => $expirationTime,
            'nbf' => $notBefore,
            'iat' => $now,
            'jti' => $tokenId,
            'subscriptionId' => $subscriptionId,
            'msisdn' => $msisdn,
            'action' => $action
        ];

        // Sign the token with the private key
        $privateKey = file_get_contents(env('JWT_PRIVATE_KEY_PATH'));
        $algorithm = 'RS256';
        $keyId = env('JWT_KEY_ID');
        $signature = JWT::encode($payload, $privateKey, $algorithm, $keyId);

        // Return the signed token
        return $signature;
        // dd($signature);

    }

}
