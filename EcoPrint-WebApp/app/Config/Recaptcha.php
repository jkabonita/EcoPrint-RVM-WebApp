<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Recaptcha extends BaseConfig
{
    public string $siteKey;
    public string $secretKey;
    public bool $enabled;

    public function __construct()
    {
        $this->siteKey   = env('recaptcha.site_key');
        $this->secretKey = env('recaptcha.secret_key');
        $this->enabled   = filter_var(env('recaptcha.enabled'), FILTER_VALIDATE_BOOLEAN);
    }
}
