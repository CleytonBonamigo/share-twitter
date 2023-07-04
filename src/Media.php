<?php

namespace CleytonBonamigo\ShareTwitter;

use CleytonBonamigo\ShareTwitter\Enums\Action;
use GuzzleHttp\Client;

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

    public function uploadImageFromUrl(string $url): void
    {
        // Get the image content
        $imageContent = file_get_contents($url);

        // Get the size
        $size = strlen($imageContent);

        // Get the MIME type of the image
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageContent);

        // Convert the image to base64
        $base64Image = base64_encode($imageContent);

        // Format the base64 image for use in data URIs
        $base64Image = 'data:' . $mimeType . ';base64,' . $base64Image;

        $data = [
            'command' => 'INIT',
            'total_bytes' => $size,
            'media_type' => $mimeType
        ];

        $this->setAction(Action::UPLOAD);
        $this->setEndpoint('media/upload.json?'.http_build_query($data));

        //Send an empty array to send as null on the body
        $initUpload = $this->sendRequest([]);

        dd($initUpload);
    }
}
