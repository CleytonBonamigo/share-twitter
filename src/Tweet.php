<?php

namespace CleytonBonamigo\ShareTwitter;

use CleytonBonamigo\ShareTwitter\Enums\Action;
use CleytonBonamigo\ShareTwitter\Enums\Methods;

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
     * @return \stdClass
     * @throws \JsonException
     */
    public function create(array $params): \stdClass
    {
        $this->setEndpoint('tweets');
        $this->setAction(Action::POST_TWITTER);
        $this->setMethod(Methods::POST);
        return $this->request($params, true);
    }
}
