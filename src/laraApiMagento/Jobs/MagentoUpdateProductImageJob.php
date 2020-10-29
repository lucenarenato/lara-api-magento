<?php

namespace App\Jobs;

use App\MarketPlace;
use App\Traits\MagentoClientTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class MagentoUpdateProductImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MagentoClientTrait;

    private $asset_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($asset_id)
    {
        $this->asset_id = $asset_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->loadProperties();

        $body =  json_encode([
            'product' => [
                'media_gallery_entries' => $this->getMediaGalleryEntries()
            ],
        ]);

        $this->httpClient->put($this->asset->_id, ['body' => $body]);
    }

    private function loadProperties(): void
    {
        $this->asset = MarketPlace::withoutGlobalScopes()->find($this->asset_id);
        $this->httpClient = $this->createMagentoClient('/rest/all/V1/products/');
    }

    private function getMediaGalleryEntries(): array
    {
        $pathFile = $this->asset->files['thumb']['path'];
        $contentFile = Storage::disk('s3')->get($pathFile);

        return [
            [
                'mediaType' => 'image',
                'label' => $this->asset->name,
                'position' => 1,
                'disabled' => false,
                'types' => ['image', 'small_image', 'thumbnail'],
                'content' => [
                    'base64EncodedData' => base64_encode($contentFile),
                    'type' => 'image/jpeg',
                    'name' => $this->asset->fileName
                ]
            ]
        ];
    }
}
