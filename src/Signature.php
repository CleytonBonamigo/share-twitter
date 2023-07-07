<?php

namespace CleytonBonamigo\ShareTwitter;

use CleytonBonamigo\ShareTwitter\Enums\Action;
use CleytonBonamigo\ShareTwitter\Utils\Util;

class Signature
{
    /** @var array */
    protected array $config;

    /** @var string */
    protected string $url;

    /** @var AbstractController */
    protected AbstractController $controller;

    /**
     * Constructor of the class.
     * @param AbstractController $controller
     */
    public function __construct(AbstractController $controller)
    {
        $this->config = [
            'version'          => '1.0',
            'consumer_key'     => 'anonymous',
            'consumer_secret'  => 'anonymous',
            'signature_method' => 'HMAC-SHA1',
        ];

        foreach ($controller->getConfigs() ?? [] as $key => $value) {
            $this->config[$key] = $value;
        }

        $this->controller = $controller;
        $this->setUrl();
    }

    /**
     * Set the URL.
     * @return void
     */
    public function setUrl(): void
    {
        $this->url = $this->controller->getApiBaseUrl().$this->controller->getEndpoint();
    }

    /**
     * Return the Authorization: OAuth header.
     * @param array $postfields
     * @return string
     */
    public function authorize(array $postfields = []): string
    {
        $nonce = $this->generateNonce();
        $timestamp = time();

        $signableParams = [
            'oauth_version' => $this->config['version'],
            'oauth_nonce' => $nonce,
            'oauth_timestamp' => $timestamp,
            'oauth_consumer_key' => $this->config['consumer_key'],
            'oauth_token' => $this->config['access_token'],
            'oauth_signature_method' => $this->config['signature_method']
        ];

        if(!($this->controller->getAction() === Action::POST_TWITTER)){
            $signableParams = Util::buildHttpQuery(array_merge($signableParams, $postfields));
        }else{
            $signableParams = Util::buildHttpQuery($signableParams);
        }

        $parts = [
            strtoupper($this->controller->getMethod()->value),
            $this->url,
            $signableParams
        ];

        $signatureBase = implode('&', Util::urlencodeRfc3986($parts));

        $parts = [$this->config['consumer_secret'], $this->config['access_token_secret'] ?? ''];
        $key = implode('&', $parts);

        $signature = Util::urlencodeRfc3986(base64_encode(hash_hmac('sha1', $signatureBase, $key, true)));

        return 'Authorization: OAuth oauth_consumer_key="'.$this->config['consumer_key'].'", oauth_nonce="'.$nonce
            .'", oauth_signature="'.$signature.'", oauth_signature_method="'.$this->config['signature_method'].'", oauth_timestamp="'.$timestamp.'"'.
            ', oauth_token="'.$this->config['access_token'].'", oauth_version="'.$this->config['version'].'"';
    }

    /**
     * Generate Nonce to add it to the authentication
     * @return string
     */
    protected function generateNonce(): string
    {
        return sha1(uniqid('', true) . $this->getSanitizedUrl());
    }

    /**
     * Remove https:// and http:// for nonce.
     * @return string
     */
    protected function getSanitizedUrl(): string
    {
        return str_replace(['https://', 'http://'], '', $this->url);
    }
}
