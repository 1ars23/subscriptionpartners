<?php

namespace App\Providers;

use App\Middleware\JwtAuthenticationMiddleware;
use Firebase\JWT\JWT;
use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerInterface;
use Firebase\JWT\Key;

class JwtProvider extends ServiceProvider
{
    public function register()
    {
        // Bind the JwtAuthenticationMiddleware to the container
        $this->app->bind(JwtAuthenticationMiddleware::class, function (ContainerInterface $container) {
            $publicKeyPath = env('JWT_PUBLIC_KEY_PATH');
            $privateKeyPath = env('JWT_PRIVATE_KEY_PATH');

            $publicKey = file_get_contents($publicKeyPath);
            $privateKey = file_get_contents($privateKeyPath);

            return new JwtAuthenticationMiddleware($publicKey, $privateKey);
        });

        // Define the encode() method to generate a JWT token
        $this->app->singleton('jwt', function () {
            $privateKey = file_get_contents(env('JWT_PRIVATE_KEY_PATH'));
            $algorithm = 'RS256';
            $keyId = env('JWT_KEY_ID');

            return new class ($privateKey, $algorithm, $keyId) {
                private $privateKey;
                private $algorithm;
                private $keyId;

                public function __construct($privateKey, $algorithm, $keyId)
                {
                    $this->privateKey = $privateKey;
                    $this->algorithm = $algorithm;
                    $this->keyId = $keyId;
                }

                public function encode($payload)
                {
                    return JWT::encode($payload, $this->privateKey, $this->algorithm, $this->keyId);
                }



            };
        });
    }

    public static function decode($jwtToken)
    {

        // Read the public key from file
        $publicKeyContent = file_get_contents(env('JWT_PUBLIC_KEY_PATH'));
        $publicKey = new Key($publicKeyContent, 'RS256');

        // Decode the token and return the payload
        return JWT::decode($jwtToken, $publicKey, [$publicKey->getAlgorithm()]);
    }

}
