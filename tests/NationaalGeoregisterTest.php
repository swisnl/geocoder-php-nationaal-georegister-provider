<?php

declare(strict_types=1);

namespace Swis\Geocoder\NationaalGeoregister\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Swis\Geocoder\NationaalGeoregister\NationaalGeoregister;

class NationaalGeoregisterTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testExtraOptionsCanBeSet()
    {
        $provider = new NationaalGeoregister($this->getMockedHttpClient(), ['ident' => 'true']);
        $this->assertEquals(['ident' => 'true'], $provider->getOptions());
    }

    public function testBlacklistedOptionsCanNotBeSet()
    {
        $provider = new NationaalGeoregister($this->getMockedHttpClient(), ['fl' => '*']);
        $this->assertEquals([], $provider->getOptions());
    }

    public function testGetName()
    {
        $provider = new NationaalGeoregister($this->getMockedHttpClient());
        $this->assertEquals('nationaal_georegister', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The NationaalGeoregister provider does not support IP addresses.
     */
    public function testGeocodeWithIPAddress()
    {
        $provider = new NationaalGeoregister($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('8.8.8.8'));
    }

    public function testGeocodeWithShortPostalCode()
    {
        $provider = new NationaalGeoregister($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('2312'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals(52.16110378, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(4.48623323, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Apothekersdijk', $result->getStreetName());
        $this->assertEquals('2312DC', $result->getPostalCode());
        $this->assertEquals('Leiden', $result->getLocality());
        $this->assertFalse($result->getAdminLevels()->has(2));
        $this->assertFalse($result->getAdminLevels()->has(1));
        $this->assertEquals('Netherlands', $result->getCountry()->getName());
        $this->assertEquals('NL', $result->getCountry()->getCode());
        $this->assertEquals('Europe/Amsterdam', $result->getTimezone());
        $this->assertEquals('nationaal_georegister', $result->getProvidedBy());
    }

    public function testGeocodeWithPostalCode()
    {
        $provider = new NationaalGeoregister($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('2312 NR'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals(52.16416908, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(4.49098397, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('3e Binnenvestgracht', $result->getStreetName());
        $this->assertEquals('2312NR', $result->getPostalCode());
        $this->assertEquals('Leiden', $result->getLocality());
        $this->assertEquals('Leiden', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Zuid-Holland', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Netherlands', $result->getCountry()->getName());
        $this->assertEquals('NL', $result->getCountry()->getCode());
        $this->assertEquals('Europe/Amsterdam', $result->getTimezone());
        $this->assertEquals('nationaal_georegister', $result->getProvidedBy());
    }

    public function testGeocodeWithPostalCodeAndStreetNumber()
    {
        $provider = new NationaalGeoregister($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('2312 NR, 23'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals(52.16416908, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(4.49098397, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('23A', $result->getStreetNumber());
        $this->assertEquals('3e Binnenvestgracht', $result->getStreetName());
        $this->assertEquals('2312NR', $result->getPostalCode());
        $this->assertEquals('Leiden', $result->getLocality());
        $this->assertEquals('Leiden', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Zuid-Holland', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Netherlands', $result->getCountry()->getName());
        $this->assertEquals('NL', $result->getCountry()->getCode());
        $this->assertEquals('Europe/Amsterdam', $result->getTimezone());
        $this->assertEquals('nationaal_georegister', $result->getProvidedBy());
    }

    public function testGeocodeWithStreetNameAndStreetNumber()
    {
        $provider = new NationaalGeoregister($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('3e Binnenvestgracht 23'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals(52.16416908, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(4.49098397, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('23A', $result->getStreetNumber());
        $this->assertEquals('3e Binnenvestgracht', $result->getStreetName());
        $this->assertEquals('2312NR', $result->getPostalCode());
        $this->assertEquals('Leiden', $result->getLocality());
        $this->assertEquals('Leiden', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Zuid-Holland', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Netherlands', $result->getCountry()->getName());
        $this->assertEquals('NL', $result->getCountry()->getCode());
        $this->assertEquals('Europe/Amsterdam', $result->getTimezone());
        $this->assertEquals('nationaal_georegister', $result->getProvidedBy());
    }

    public function testGeocodeWithStreetNameStreetNumberAndCity()
    {
        $provider = new NationaalGeoregister($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('3e Binnenvestgracht 23, Leiden'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals(52.16416908, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(4.49098397, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('23A', $result->getStreetNumber());
        $this->assertEquals('3e Binnenvestgracht', $result->getStreetName());
        $this->assertEquals('2312NR', $result->getPostalCode());
        $this->assertEquals('Leiden', $result->getLocality());
        $this->assertEquals('Leiden', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Zuid-Holland', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Netherlands', $result->getCountry()->getName());
        $this->assertEquals('NL', $result->getCountry()->getCode());
        $this->assertEquals('Europe/Amsterdam', $result->getTimezone());
        $this->assertEquals('nationaal_georegister', $result->getProvidedBy());
    }

    public function testReverse()
    {
        $provider = new NationaalGeoregister($this->getHttpClient());
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(52.16416908, 4.49098397));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals(52.16416908, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(4.49098397, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('23A', $result->getStreetNumber());
        $this->assertEquals('3e Binnenvestgracht', $result->getStreetName());
        $this->assertEquals('2312NR', $result->getPostalCode());
        $this->assertEquals('Leiden', $result->getLocality());
        $this->assertEquals('Leiden', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Zuid-Holland', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Netherlands', $result->getCountry()->getName());
        $this->assertEquals('NL', $result->getCountry()->getCode());
        $this->assertEquals('Europe/Amsterdam', $result->getTimezone());
        $this->assertEquals('nationaal_georegister', $result->getProvidedBy());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testServerEmptyResponse()
    {
        $provider = new NationaalGeoregister($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('Lorem ipsum'));
    }
}
