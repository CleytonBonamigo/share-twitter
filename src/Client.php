<?php

namespace CleytonBonamigo\ShareTwitter;

class Client
{
    /**
     * Twitter API settings.
     * @var array<string>
     */
    protected array $settings = [];

    /**
     * Client constructor.
     * @param array<string> $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Access to Tweet class
     * @return Tweet
     * @throws \Exception
     */
    public function tweet(): Tweet
    {
        return new Tweet($this->settings);
    }

    /**
     * Access to Media class
     * @return Media
     * @throws \Exception
     */
    public function media(): Media
    {
        return new Media($this->settings);;
    }
}
