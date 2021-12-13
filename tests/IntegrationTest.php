<?php

declare(strict_types=1);

namespace Swis\Geocoder\NationaalGeoregister\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Http\Client\HttpClient;
use Swis\Geocoder\NationaalGeoregister\NationaalGeoregister;

class IntegrationTest extends ProviderIntegrationTest
{
    protected $skippedTests = [
        'testGeocodeQuery' => 'Geocoding an address in the UK is not supported by this provider',
    ];

    protected $testReverse = false;

    protected $testIpv4 = false;

    protected $testIpv6 = false;

    protected function createProvider(HttpClient $httpClient)
    {
        return new NationaalGeoregister($httpClient);
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey(): ?string
    {
        return null;
    }
}
