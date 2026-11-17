<?php

namespace App\Libraries;

use League\OAuth2\Client\Provider\Google;

class GoogleAuth
{
    protected $provider;

    public function __construct()
    {
        $this->provider = new Google([
            'clientId'     => getenv('GOOGLE_CLIENT_ID'),
            'clientSecret' => getenv('GOOGLE_CLIENT_SECRET'),
            'redirectUri'  => getenv('GOOGLE_REDIRECT_URI'),
        ]);
    }

    public function getAuthUrl()
    {
        return $this->provider->getAuthorizationUrl();
    }

    public function getAccessToken($code)
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);
    }

    public function getUserInfo($accessToken)
    {
        return $this->provider->getResourceOwner($accessToken);
    }

    public function getProvider()
    {
        return $this->provider;
    }
}
