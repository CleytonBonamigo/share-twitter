<?php

namespace CleytonBonamigo\ShareTwitter;

use CleytonBonamigo\ShareTwitter\Enums\Action;
use CleytonBonamigo\ShareTwitter\Enums\Methods;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Media extends AbstractController
{
    /**
     * Constructor of Tweet class
     * @param array $settings
     * @throws \Exception
     */
    public function __construct(array $settings)
    {
        parent::__construct($settings);
    }

    public function uploadMediaFromUrl(string $url)
    {
        if (($file = file_get_contents($url)) === false) {
            throw new \InvalidArgumentException(
                'Please, add a readable file.',
            );
        }

        $this->setAction(Action::UPLOAD);
        $this->setMethod(Methods::POST);
        $this->setEndpoint('media/upload.json');
        return $this->request(['media' => base64_encode($file)]);
    }
}
