<?php

declare(strict_types=1);

namespace Swis\Geocoder\NationaalGeoregister\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Psr\Http\Client\ClientInterface;
use Swis\Geocoder\NationaalGeoregister\NationaalGeoregister;

class IntegrationTest extends ProviderIntegrationTest
{
    protected array $skippedTests = [
        'testGeocodeQuery' => 'Geocoding an address in the UK is not supported by this provider',
        'testReverseQueryWithNoResults' => 'This provider will always return a result',
    ];

    protected bool $testIpv4 = false;

    protected bool $testIpv6 = false;

    protected function createProvider(ClientInterface $httpClient): NationaalGeoregister
    {
        return new NationaalGeoregister($httpClient);
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey(): string
    {
        return '';
    }
}
