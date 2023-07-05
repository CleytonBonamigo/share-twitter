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

        // Send an empty array to send as null on the body
        $initUpload = $this->sendRequest([]);

        $data = [
            [
                'name' => 'command',
                'contents' => 'APPEND'
            ],
            [
                'name' => 'media_id',
                'contents' => $initUpload->media_id
            ]
        ];

        $this->setEndpoint('media/upload.json');
        foreach ($this->splitFile($base64Image) as $index => $chunk){
            $data[] = [
                'name' => 'segment_index',
                'contents' => $index
            ];
            $data[] = [
                'name' => 'media_data',
                'contents' => $chunk
            ];

            // If something goes wrong, it will generate an Exception
            if($this->removeHeaders()->sendRequest($data, null, 'multipart') !== ''){
                throw new \RuntimeException('Something goes worn in APPEND command and it not caused any other error, please investigate id');
            }
        }
    }

    /**
     * Split file into chunks of 5MB
     * @param string $base64String
     * @param int $chunkSize
     * @return array
     */
    public function splitFile(string $base64String, int $chunkSize = 5 * 1024 * 1024): array
    {
        $chunkSizeBase64 = $chunkSize * 3 / 4;
        $chunkSizeBase64 -= $chunkSizeBase64 % 4;
        return str_split($base64String, $chunkSizeBase64);
    }
}
