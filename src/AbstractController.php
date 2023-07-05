<?php

namespace CleytonBonamigo\ShareTwitter;

use CleytonBonamigo\ShareTwitter\Enums\Action;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

abstract class AbstractController
{
    /** @const API_BASE_URL */
    private const API_BASE_URL = 'https://api.twitter.com/2/';

    /** @const API_BASE_URL_UPLOAD */
    private const API_BASE_URL_UPLOAD = 'https://upload.twitter.com/1.1/';

    /** @var Action */
    private Action $action;

    /** @var string */
    private string $enpoint = '';

    /** @var string */
    private string $access_token;

    /** @var string */
    private string $access_token_secret;

    /** @var string */
    private string $consumer_key;

    /** @var string */
    private string $consumer_secret;

    /** @var bool  */
    private bool $removeHeaders = false;

    /**
     * Abstract Class Constructor
     * @param array<string> $settings
     * @throws \Exception
     */
    public function __construct(array $settings)
    {
        $this->parseSettings($settings);
        $this->setAction(Action::POST_TWITTER);
    }

    /**
     * Get Api Base URL, because of version 1 and 2.
     * @return string
     */
    public function getApiBaseUrl(): string
    {
        if($this->getAction() === Action::UPLOAD){
            return self::API_BASE_URL_UPLOAD;
        }

        return self::API_BASE_URL;
    }

    /**
     * Set API Action
     * @param Action $action
     * @return void
     */
    public function setAction(Action $action): void
    {
        $this->action = $action;
    }

    /**
     * Retrieve the API Action
     * @return Action
     */
    public function getAction(): Action
    {
        return $this->action;
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
     * Set variable to not send headers
     * @param bool $remove
     * @return AbstractController
     */
    public function removeHeaders(bool $remove = false): AbstractController
    {
        $this->removeHeaders = $remove;
        return $this;
    }

    /**
     * @param array<string> $settings
     * @return void
     * @throws \Exception
     */
    public function parseSettings(array $settings): void
    {
        if(!isset($settings[ 'access_token'],
            $settings['access_token_secret'],
            $settings['consumer_key'],
            $settings['consumer_secret']
        )){
            throw new \Exception('Incomplete settings');
        }

        $this->access_token = $settings['access_token'];
        $this->access_token_secret = $settings['access_token_secret'];
        $this->consumer_key = $settings['consumer_key'];
        $this->consumer_secret = $settings['consumer_secret'];
    }

    /**
     * Perform a request to Twitter API, with OAuth1.
     * @param array<string> $data
     * @param array<string>|null $headers
     * @param string $bodyIndex
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function sendRequest(array $data = [], array $headers = null, string $bodyIndex = 'json'): mixed
    {
        try {
            $defaulHeaders = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ];
            $stack = HandlerStack::create();
            $middleware = new Oauth1([
                'consumer_key' => $this->consumer_key,
                'consumer_secret' => $this->consumer_secret,
                'token' => $this->access_token,
                'token_secret' => $this->access_token_secret
            ]);
            $stack->push($middleware);
            $client = new Client([
                'base_uri' => $this->getApiBaseUrl(),
                'handler' => $stack,
                'auth' => 'oauth'
            ]);

            $response = $client->request('POST', $this->getEndpoint(), [
                'verify' => false,
                'headers' => !$this->removeHeaders ? ($headers ?? $defaulHeaders) : null,
                // If you send an empty array, Twitter will return an error.
                $bodyIndex => count($data) ? $data : null
            ]);

            // Some methods, like media APPENDS returns an empty body, but returns a 200 code
            if($response->getBody()->getContents() === '' && $response->getStatusCode() === 200){
                return '';
            }

            $body = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
            if($response->getStatusCode() >= 400){
                $error = [
                    'message' => "Error on endpoint {$this->getEndpoint()}"
                ];
                if($body){
                    $error['details'] = $response;
                }

                throw new \RuntimeException(
                    json_encode($error, JSON_THROW_ON_ERROR),
                    $response->getStatusCode()
                );
            }

            return $body;
        } catch (ServerException $e){
            $payload = json_decode(str_replace("\n", "", $e->getResponse()->getBody()->getContents()), false, 512,
                JSON_THROW_ON_ERROR);
            throw new \RuntimeException($payload->detail, $payload->status);
        }
    }
}
