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
        $this->request(['media' => base64_encode($file)]);
    }

    /*public function uploadImageFromUrl(string $url): void
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

        echo $size.PHP_EOL;
        echo $mimeType.PHP_EOL;

        $data = [
            'command' => 'INIT',
            'total_bytes' => $size,
            'media_type' => $mimeType
        ];

        $this->setAction(Action::UPLOAD);
        $this->setEndpoint('media/upload.json?'.http_build_query($data));

        $initUpload = $this->sendRequest();
        Log::info(json_encode($initUpload));

        $this->setEndpoint('media/upload.json');
        foreach ($this->splitFile($base64Image) as $index => $chunk){
            $data = [
                [
                    'name' => 'command',
                    'contents' => 'APPEND'
                ],
                [
                    'name' => 'media_id',
                    'contents' => $initUpload->media_id
                ],
                [
                    'name' => 'segment_index',
                    'contents' => $index
                ],
                [
                    'name' => 'media_data',
                    'contents' => $chunk
                ]
            ];

            echo strlen($chunk).PHP_EOL;

            // If something goes wrong, it will generate an Exception
            if($return = $this->setRemoveHeaders(true)->sendRequest($data, null, 'multipart') !== ''){
                dd($return);
                throw new \RuntimeException('Something goes wrong in APPEND command and it not caused any other error, please investigate id');
            }
        }

        //$this->setMethod(Methods::GET);
        sleep(20);
        $this->setRemoveHeaders(true)->setEndpoint("media/upload.json?command=FINALIZE&media_id={$initUpload->media_id}");
        dd($this->sendRequest());
    }*/

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
