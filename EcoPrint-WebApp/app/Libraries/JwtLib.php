<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class JwtLib
{
    private $key; // change to a long, random string
    private $algo = 'HS256';

    public function __construct()
    {
        $this->key = getenv('JWT_SECRET_KEY');
    }

    public function generateToken($data, $expiryMinutes = 60)
    {
        $issuedAt = time();
        $expire = $issuedAt + ($expiryMinutes * 60);

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'data' => $data
        ];

        return JWT::encode($payload, $this->key, $this->algo);
    }

    public function verifyToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->key, $this->algo));
            return (array) $decoded->data;
        } catch (Exception $e) {
            return false;
        }
    }
}
