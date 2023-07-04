<?php

namespace CleytonBonamigo\ShareTwitter;

class Tweet extends AbstractController
{
    /**
     * Constructor of Tweet class.
     * @param array<string> $settings
     * @throws \Exception
     */
    public function __construct(array $settings)
    {
        parent::__construct($settings);
    }

    /**
     * Create a Tweet
     * See https://developer.twitter.com/en/docs/twitter-api/tweets/manage-tweets/api-reference/post-tweets
     * @param array $params
     * @return array
     * @throws \JsonException
     */
    public function create(array $params): array
    {
        $this->setEndpoint('tweets');
        return $this->sendRequest($params);
    }
}
