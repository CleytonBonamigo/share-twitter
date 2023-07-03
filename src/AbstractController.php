<?php

namespace CleytonBonamigo\ShareTwitter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;

abstract class AbstractController
{
    /** @const API_BASE_URL */
    private const API_BASE_URL = 'https://api.twitter.com/api/2/';

    /** @var string */
    private string $enpoint = '';

    /** @var int */
    private int $account_id;

    /** @var string */
    private string $access_token;

    /** @var string */
    private string $access_token_secret;

    /** @var string */
    private string $consumer_key;

    /** @var string */
    private string $consumer_secret;

    /** @var string */
    private string $bearer_token;

    /**
     * Abstract Class Constructor
     * @param array<string> $settings
     * @throws \Exception
     */
    public function __construct(array $settings)
    {
        $this->parseSettings($settings);
    }

    /**
     * Set endpoint value
     * @param string $endpoint
     * @return void
     */
    protected function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Retrieve endpoint value
     * @return string
     */
    protected function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param array<string> $settings
     * @return void
     * @throws \Exception
     */
    public function parseSettings(array $settings): void
    {
        if(!isset($settings['account_id'],
            $settings[ 'access_token'],
            $settings['access_token_secret'],
            $settings['consumer_key'],
            $settings['consumer_secret'],
            $settings['bearer_token']
        )){
            throw new \Exception('Incomplete settings');
        }

        $this->account_id = (int)$settings['account_id'];
        $this->access_token = $settings['access_token'];
        $this->access_token_secret = $settings['access_token_secret'];
        $this->consumer_key = $settings['consumer_key'];
        $this->consumer_secret = $settings['consumer_secret'];
        $this->bearer_token = $settings['bearer_token'];
    }

    /**
     * Perform a request to Twitter API, with OAuth1.
     * @param array<string> $data
     * @return mixed
     */
    public function request(array $data = []): mixed
    {
        try {
            $stack = HandlerStack::create();
            $middleware = new Oauth1([
                'consumer_key' => $this->consumer_key,
                'consumer_secret' => $this->consumer_secret,
                'token' => $this->access_token,
                'token_secret' => $this->access_token_secret
            ]);
            $stack->push($middleware);
            $client = new Client([
                'base_uri' => self::API_BASE_URL,
                'handler' => $stack,
                'auth' => 'oauth'
            ]);

            $response = $client->request('POST', $this->getEndpoint(), [
                'verify' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                // If you send an empty array, Twitter will return an error.
                'json' => count($data) ? $data : null
            ]);

            $body = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
            dd($body);
        } catch (ServerException $e){
            $payload = json_decode(str_replace("\n", "", $e->getResponse()->getBody()->getContents()), false, 512,
                JSON_THROW_ON_ERROR);
            throw new \RuntimeException($payload->detail, $payload->status);
        }
    }
}
