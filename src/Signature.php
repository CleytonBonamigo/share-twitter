<?php

namespace CleytonBonamigo\ShareTwitter;

use CleytonBonamigo\ShareTwitter\Enums\Action;
use CleytonBonamigo\ShareTwitter\Enums\Methods;
use CleytonBonamigo\ShareTwitter\Utils\Util;

class Signature
{
    /** @var array */
    protected array $config;

    /** @var string */
    protected string $url;

    /** @var Action */
    protected Action $action;

    /** @var Methods */
    protected Methods $method;

    /**
     * Constructor of the class.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = [
            'version'          => '1.0',
            'consumer_key'     => 'anonymous',
            'consumer_secret'  => 'anonymous',
            'signature_method' => 'HMAC-SHA1',
        ];

        foreach ($config as $key => $value) {
            $this->config[$key] = $value;
        }
    }

    /**
     * Se URL.
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): Signature
    {
        $this->url = str_replace(['https://', 'http://'], '', $url);

        return $this;
    }

    /**
     * Set the action.
     * @param Action $action
     * @return $this
     */
    public function setAction(Action $action): Signature
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Set the Method
     * @param Methods $method
     * @return $this
     */
    public function setMethod(Methods $method): Signature
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Return the Authorization: OAuth header.
     * @param array $postfields
     * @return string
     */
    public function getAuthorizationSignature(array $postfields = [])
    {
        $nonce = $this->generateNonce();
        $timestamp = time();
        $signatureMethod = 'HMAC-SHA1';

        $signableParams = [
            'oauth_version' => $this->config['version'],
            'oauth_nonce' => $nonce,
            'oauth_timestamp' => $timestamp,
            'oauth_consumer_key' => $this->config['consumer_key'],
            'oauth_token' => $this->config['access_token'],
            'oauth_signature_method' => $this->config['signature_method']
        ];

        if(!($this->action === Action::POST_TWITTER)){
            $signableParams = Util::buildHttpQuery(array_merge($signableParams, $postfields));
        }else{
            $signableParams = Util::buildHttpQuery($signableParams);
        }

        $parts = [
            strtoupper($this->method->value),
            $this->url,
            $signableParams
        ];

        $signatureBase = implode('&', Util::urlencodeRfc3986($parts));

        $parts = [$this->config['consumer_secret'], $this->config['access_token_secret'] ?? ''];
        $key = implode('&', $parts);

        $signature = Util::urlencodeRfc3986(base64_encode(hash_hmac('sha1', $signatureBase, $key, true)));

        return 'Authorization: OAuth oauth_consumer_key="'.$this->config['consumer_key'].'", oauth_nonce="'.$nonce
            .'", oauth_signature="'.$signature.'", oauth_signature_method="'.$this->config['signature_method']
            .'", oauth_timestamp="'.$timestamp.'", oauth_token="'.$this->config['access_token']
            .'", oauth_version="'.$this->config['version'].'"';
    }

    /**
     * Generate Nonce to add it to the authentication
     * @return string
     */
    protected function generateNonce(): string
    {
        return sha1(uniqid('', true) . $this->url);
    }
}
