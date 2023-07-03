<?php

namespace CleytonBonamigo\ShareTwitter;

class Tweet
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
     * @return $this
     */
    public function create(): Tweet
    {
        $this->setEndpoint('tweets');
        return $this;
    }
}
