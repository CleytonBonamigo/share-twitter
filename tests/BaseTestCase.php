<?php

namespace CleytonBonamigo\ShareTwitter\Tests;

use CleytonBonamigo\ShareTwitter\Client;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Exception;

abstract class BaseTestCase extends TestCase
{
    /** @var Client */
    protected Client $client;

    /** @var array<string> */
    protected static array $settings = [];

    public function setUp(): void
    {
        if(class_exists(Dotenv::class) && file_exists(__DIR__.'/config/.env')){
            $dotenv = Dotenv::createUnsafeImmutable(__DIR__.'/config/', '.env');
            $dotenv->safeLoad();
        }

        foreach (getenv() as $key => $value){
            if(str_starts_with($key, 'TWITTER_')){
                $name = str_replace('twitter_', '', mb_strtolower($key));
                self::$settings[$name] = $value;
            }
        }

        $this->client = new Client(self::$settings);
    }

    public function uploadImage(): \stdClass
    {
        try{
            $response = $this->client->media()->uploadMediaFromUrl(__DIR__.'/twitter-logo.jpg');

            $this->assertTrue(is_object($response) && property_exists($response, 'media_id'));

            return $response;
        }catch (Exception $e){
            $this->fail("Test failed: {$e->getMessage()}\n{$e->getTraceAsString()}");
        }
    }
}