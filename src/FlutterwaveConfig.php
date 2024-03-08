<?php

namespace Lunar\Flutterwave;

use Flutterwave\Config\AbstractConfig;
use Flutterwave\Contract\ConfigInterface;
use Flutterwave\Helper\EnvVariables;

class FlutterwaveConfig extends AbstractConfig implements ConfigInterface
{
    const BASE_URL = EnvVariables::BASE_URL;
    public function __construct(string $secretKey, string $publicKey, string $encryptKey, string $env)
    {
        parent::__construct($secretKey, $publicKey, $encryptKey, $env);
    }

    public static function setUp(string $secretKey, string $publicKey, string $enc, string $env): ConfigInterface
    {
        if (is_null(self::$instance)) {
            return new self($secretKey, $publicKey, $enc, $env);
        }

        return self::$instance;
    }

    public function getEncryptkey(): string
    {
        return $this->enc;
    }

    public function getPublicKey(): string
    {
        return $this->public;
    }

    public function getSecretKey(): string
    {
        return $this->secret;
    }

    public function getEnv(): string
    {
        return $this->env;
    }
}
