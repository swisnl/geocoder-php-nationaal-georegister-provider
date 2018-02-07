<?php

declare(strict_types=1);

namespace Swis\Geocoder\NationaalGeoregister;

use Geocoder\Collection;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use proj4php\Point;
use proj4php\Proj;
use proj4php\Proj4php;

class NationaalGeoregister extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL_SSL = 'https://geodata.nationaalgeoregister.nl/geocoder/Geocoder?zoekterm=%s';

    /**
     * @var string
     */
    const RD_SRS_CODE = '+proj=sterea +lat_0=52.15616055555555 +lon_0=5.38763888888889 +k=0.999908 +x_0=155000 +y_0=463000 +ellps=bessel +units=m +towgs84=565.2369,50.0087,465.658,-0.406857330322398,0.350732676542563,-1.8703473836068,4.0812 +no_defs <>';

    /**
     * @param \Geocoder\Query\GeocodeQuery $query
     *
     * @throws \Geocoder\Exception\UnsupportedOperation
     *
     * @return \Geocoder\Collection
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        // This API doesn't handle IPs.
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The NationaalGeoregister provider does not support IP addresses.');
        }

        return $this->executeQuery(sprintf(self::GEOCODE_ENDPOINT_URL_SSL, rawurlencode($address)));
    }

    /**
     * @param \Geocoder\Query\ReverseQuery $query
     *
     * @throws \Geocoder\Exception\UnsupportedOperation
     *
     * @return \Geocoder\Collection
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The NationaalGeoregister provider is not able to do reverse geocoding.');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'nationaal_georegister';
    }

    /**
     * @param string $query
     *
     * @throws \Geocoder\Exception\InvalidServerResponse
     * @throws \Geocoder\Exception\CollectionIsEmpty
     *
     * @return \Geocoder\Model\AddressCollection
     */
    protected function executeQuery(string $query): AddressCollection
    {
        $xml = $this->getResultsAsXmlForQuery($query);

        $numberOfGeocodedAddresses = 0;
        $elements = $xml->xpath('//xls:GeocodeResponseList/@numberOfGeocodedAddresses');

        if (\is_array($elements) && \count($elements) > 0) {
            $numberOfGeocodedAddresses = (int)$elements[0]['numberOfGeocodedAddresses'];
        }

        $results = [];
        for ($i = 1; $i <= $numberOfGeocodedAddresses; ++$i) {
            if ($numberOfGeocodedAddresses === 1) {
                $addressXls = 'xls:GeocodedAddress';
            } else {
                $addressXls = 'xls:GeocodedAddress['.$i.']';
            }

            $positions = $xml->xpath('//'.$addressXls.'/gml:Point/gml:pos');
            $postalCode = $xml->xpath('//'.$addressXls.'/xls:Address/xls:PostalCode');
            $locality = $xml->xpath('//'.$addressXls.'/xls:Address/xls:Place[@type="Municipality"]');
            $subLocality = $xml->xpath('//'.$addressXls.'/xls:Address/xls:Place[@type="MunicipalitySubdivision"]');
            $streetNumber = $xml->xpath('//'.$addressXls.'/xls:Address/xls:StreetAddress/xls:Building');
            $cityDistrict = $xml->xpath('//'.$addressXls.'/xls:Address/xls:StreetAddress/xls:Street');

            $position = explode(' ', (string)$positions[0]);
            $point = $this->projectPoint((float)$position[0], (float)$position[1]);

            $builder = new AddressBuilder($this->getName());

            $builder->setCoordinates($point->y, $point->x);
            $builder->setStreetNumber($this->getStreetNumber($streetNumber));
            $builder->setStreetName($this->getItem($cityDistrict));
            $builder->setPostalCode($this->getItem($postalCode));
            $builder->setLocality($this->getItem($locality));
            $builder->setSubLocality($this->getItem($subLocality));
            $builder->setCountry('Netherlands');
            $builder->setCountryCode('NL');
            $builder->setTimezone('Europe/Amsterdam');

            $results[] = $builder->build();
        }

        return new AddressCollection($results);
    }

    /**
     * @param string $query
     *
     * @throws \Geocoder\Exception\InvalidServerResponse
     *
     * @return \SimpleXMLElement
     */
    protected function getResultsAsXmlForQuery(string $query): \SimpleXMLElement
    {
        $content = $this->getUrlContents($query);

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content)) {
            throw new InvalidServerResponse(sprintf('Could not execute query "%s"', $query));
        }

        $xml = new \SimpleXMLElement($content);
        if (isset($xml->ErrorList->Error) || null === $xml->GeocodeResponse) {
            throw new InvalidServerResponse(sprintf('Could not execute query "%s"', $query));
        }

        $xml->registerXPathNamespace('gml', 'http://www.opengis.net/gml');
        $xml->registerXPathNamespace('xls', 'http://www.opengis.net/xls');

        return $xml;
    }

    /**
     * Because Nationaal Georegister always uses EPSG:28922 projection we need to convert this result to EPSG:4326.
     *
     * @param float $x
     * @param float $y
     *
     * @return \proj4php\Point
     */
    protected function projectPoint(float $x, float $y): Point
    {
        $proj = new Proj4php();
        $rdProjection = new Proj(self::RD_SRS_CODE, $proj);
        $wgsProjection = new Proj('EPSG:4326', $proj);
        $rdPoint = new Point($x, $y, null, $rdProjection);

        return $proj->transform($wgsProjection, $rdPoint);
    }

    /**
     * @param array $items
     * @param int   $i
     *
     * @return string|null
     */
    protected function getItem(array $items, int $i = 0)
    {
        return isset($items[$i]) ? (string)$items[$i] : null;
    }

    /**
     * @param \SimpleXMLElement[] $items
     *
     * @return string|null
     */
    protected function getStreetNumber(array $items)
    {
        if (!isset($items[0])) {
            return null;
        }

        return trim(sprintf('%s %s', $items[0]['number'], $items[0]['subdivision']));
    }
}
