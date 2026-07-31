<?php

namespace Tests\Unit;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_value_caches_setting_and_clears_on_save()
    {
        // 1. Create setting
        $setting = SystemSetting::create([
            'key' => 'test_cache_key',
            'value' => 'original_value',
            'type' => 'string',
        ]);

        // 2. Fetch value to populate cache
        $this->assertEquals('original_value', SystemSetting::getValue('test_cache_key'));
        $this->assertTrue(Cache::has('system_setting_test_cache_key'));

        // 3. Update value directly via model
        $setting->update(['value' => 'updated_value']);

        // 4. Verify cache was cleared and getValue returns new value
        $this->assertFalse(Cache::has('system_setting_test_cache_key'));
        $this->assertEquals('updated_value', SystemSetting::getValue('test_cache_key'));
    }
}
