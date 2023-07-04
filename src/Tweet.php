<?php

namespace CleytonBonamigo\ShareTwitter;

class Tweet extends AbstractController
{
    /**
     * Construct the Tweet class.
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
     * @return $this
     */
    public function create(array $params): Tweet
    {
        $this->setEndpoint('tweets');
        $this->sendRequest($params);
        return $this;
    }
}
