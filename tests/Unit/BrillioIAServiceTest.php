<?php

namespace Tests\Unit;

use App\Services\BrillioIAService;
use Tests\TestCase;

class BrillioIAServiceTest extends TestCase
{
    public function test_is_api_key_configured_returns_boolean()
    {
        $service = new BrillioIAService;
        $this->assertIsBool($service->isApiKeyConfigured());
    }
}
