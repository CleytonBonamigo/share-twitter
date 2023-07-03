<?php

namespace CleytonBonamigo\AbstractController;

abstract class AbstractController
{
    /** @const API_BASE_URL */
    private const API_BASE_URL = 'https://api.twitter.com/api/2/';

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
     * Perform a request to Twitter API.
     * @param array<string> $data
     * @return mixed
     */
    public function request(array $data = []): mixed
    {

    }
}
