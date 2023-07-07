<?php

namespace CleytonBonamigo\ShareTwitter\Tests;

use Exception;

class TweetTest extends BaseTestCase
{
    /**
     * Post new Tweet.
     * @return void
     */
    public function testPostTweet(): void
    {
        try{
            $date = new \DateTime('NOW');
            $response = $this->client->tweet()->create([
                'text' => "Test Tweet {$date->format(\DateTimeInterface::ATOM)}"
            ]);

            $this->assertTrue(is_object($response) && property_exists($response, 'data'));
        }catch (Exception $e){
            $this->fail("Test failed: {$e->getMessage()}\n{$e->getTraceAsString()}");
        }
    }

    /**
     * Post a Tweet with Image
     * @return void
     */
    public function testPostTweetWithImage(): void
    {
        try{
            $newImage = $this->uploadImage();

            $date = new \DateTime('NOW');
            $response = $this->client->tweet()->create([
                'text' => "Test Tweet with image {$date->format(\DateTimeInterface::ATOM)}",
                'media' => [
                    'media_ids' => [$newImage->media_id_string]
                ]
            ]);

            $this->assertTrue(is_object($response) && property_exists($response, 'data'));
        }catch (Exception $e){
            $this->fail("Test failed: {$e->getMessage()}\n{$e->getTraceAsString()}");
        }
    }
}