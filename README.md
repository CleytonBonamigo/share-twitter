# Share Twitter (Post Tweet with Image)
Combination of API V1 and V2 for PHP with CURL instead of any other libraries, 
this is a pacjage that provides an easy and fast integration of Twitter.

For now, it's not possible to upload files with API V2, that's why it's a combination 
of them

## Installation
First you need to add the component to you composer.json
```
composer require cleytonbonamigo/twitter-share
```

## How to use
Firstly, you need to follow [this tutorial](https://developer.twitter.com/en/docs/tutorials/getting-started-with-r-and-v2-of-the-twitter-api).
- [Request of an approved account](https://developer.twitter.com/en/apply-for-access);
- Once you have an approved developer account, you will need to [create a Project](https://developer.twitter.com/en/docs/projects/overview);
- Enable read/write access for your Twitter app;
- Generate Consumer Keys and Authentication Tokens;
- Grab your Keys and Tokens from the twitter developer site.

### Prepare settings
Settings are expected as below:
```php
use CleytonBonamigo\ShareTwitter\Client;

$settings = [
    'access_token' => access_token,
    'access_token' => access_token,
    'access_token_secret' => access_token_secret,
    'consumer_key' => consumer_key,
    'consumer_secret' => consumer_secret  
];

$client = new Client($settings);
```

## Endpoints
### Media
```php
$url = ''; //An URL or path to local file
$return = $client->media()->uploadMediaFromUrl($url)
```

```php
$tweet = [
    'text' => 'Text of your tweet :)',
    'media' => [ //This param is optional
        'media_ids' => [
            'media_id_string' //Returned at uploadMediaFromUrl()
        ]
    ]
];
$client->tweet()->create(['text' => 'Test new tweet post with image complete flux', 'media' => ['media_ids' => [$media->media_id_string]]])
```

## Contributing
Fork/download the code and run

`composer install`

copy `test/config/.env.example` to `test/config/.env` and add your credentials for testing.

### To run tests

`./vendor/bin/phpunit`