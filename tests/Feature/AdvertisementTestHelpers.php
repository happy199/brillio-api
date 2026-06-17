<?php

namespace Tests\Feature;

use App\Models\Advertisement;

/**
 * Shared helper methods for Advertisement feature tests.
 */
trait AdvertisementTestHelpers
{
    /**
     * Create a minimal Advertisement with sensible defaults.
     *
     * @param  array  $overrides  Attributes to override.
     */
    protected function makeAd(array $overrides = []): Advertisement
    {
        return Advertisement::create(array_merge([
            'title' => 'Test Ad',
            'image_path' => 'advertisements/test.webp',
            'status' => Advertisement::STATUS_PENDING,
        ], $overrides));
    }
}
