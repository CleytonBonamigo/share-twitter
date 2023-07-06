<?php

namespace CleytonBonamigo\ShareTwitter;

use CleytonBonamigo\ShareTwitter\Enums\Action;
use CleytonBonamigo\ShareTwitter\Enums\Methods;
use CleytonBonamigo\ShareTwitter\Utils\Util;

abstract class AbstractController
{
    /** @const API_BASE_URL */
    private const API_BASE_URL = 'https://api.twitter.com/2/';

    /** @const API_BASE_URL_UPLOAD */
    private const API_BASE_URL_UPLOAD = 'https://upload.twitter.com/1.1/';

    /** @var Methods */
    private Methods $method;

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

    /**
     * Abstract Class Constructor
     * @param array<string> $settings
     * @throws \Exception
     */
    public function __construct(array $settings)
    {
        $this->parseSettings($settings);
        $this->setAction(Action::POST_TWITTER);
        $this->setMethod(Methods::POST);
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
     * Set method value
     * @param Methods $method
     * @return void
     */
    public function setMethod(Methods $method): void
    {
        $this->method = $method;
    }

    /**
     * Retrieve the method value
     * @return Methods
     */
    public function getMethod(): Methods
    {
        return $this->method;
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
     * Send request to API with CURL
     * @param array $postfields
     * @param bool $json
     * @return \stdClass
     * @throws \JsonException
     */
    public function request(array $postfields, bool $json = false): \stdClass
    {
        try {
            $url = $this->getApiBaseUrl().$this->getEndpoint();
            $signature = new Signature([
                'access_token' => $this->access_token,
                'access_token_secret' => $this->access_token_secret,
                'consumer_key' => $this->consumer_key,
                'consumer_secret' => $this->consumer_secret
            ]);
            $authorization = $signature->setUrl($url)
                ->setAction($this->getAction())
                ->setMethod($this->getMethod())
                ->getAuthorizationSignature($postfields);
            $options = Util::curlOptions();
            $options[CURLOPT_URL] = $url;
            $options[CURLOPT_HTTPHEADER] = [
                'Accept: application/json',
                $authorization,
                'Expect:',
            ];

            switch ($this->getMethod()) {
                case Methods::GET:
                    break;
                case Methods::POST:
                    $options[CURLOPT_POST] = true;
                    $options = $this->setPostfieldsOptions(
                        $options,
                        $postfields,
                        $json,
                    );
                    break;
            }

            $curlHandle = curl_init();
            curl_setopt_array($curlHandle, $options);
            $response = curl_exec($curlHandle);

            // Throw exceptions on cURL errors.
            if (curl_errno($curlHandle) > 0) {
                $error = curl_error($curlHandle);
                $errorNo = curl_errno($curlHandle);
                curl_close($curlHandle);
                throw new \Exception($error, $errorNo);
            }

            $statusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
            $parts = explode("\r\n", $response);
            $responseBody = array_pop($parts);
            $responseHeader = array_pop($parts);
            $headers = Util::parseHeaders($responseHeader);

            curl_close($curlHandle);

            $body = json_decode($responseBody, false, 512, JSON_THROW_ON_ERROR);
            if($statusCode >= 400){
                $error = [
                    'message' => "Error on endpoint {$this->getEndpoint()}"
                ];
                if($body){
                    $error['details'] = $body;
                }

                throw new \RuntimeException(
                    json_encode($error, JSON_THROW_ON_ERROR),
                    $statusCode
                );
            }

            return $body;
        }catch (\Throwable $e){
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Set POST fields into CURL options.
     * @param array $options
     * @param array $postfields
     * @param bool $json
     * @return array
     * @throws \JsonException
     */
    private function setPostfieldsOptions(
        array $options,
        array $postfields,
        bool $json,
    ): array {
        if ($json) {
            $options[CURLOPT_HTTPHEADER][] = 'Content-type: application/json';
            $options[CURLOPT_POSTFIELDS] = json_encode(
                $postfields,
                JSON_THROW_ON_ERROR,
            );
        } else {
            $options[CURLOPT_POSTFIELDS] = http_build_query($postfields);
        }

        return $options;
    }
}
