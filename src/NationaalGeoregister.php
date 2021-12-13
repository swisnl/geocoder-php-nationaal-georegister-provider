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
use Http\Client\HttpClient;

class NationaalGeoregister extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    protected const ENDPOINT_URL_FREE = 'https://geodata.nationaalgeoregister.nl/locatieserver/v3/free?%s';

    /**
     * @var string
     */
    protected const ENDPOINT_URL_REVERSE = 'https://geodata.nationaalgeoregister.nl/locatieserver/revgeo?%s';

    /**
     * @var string[]
     */
    protected const BLACKLISTED_OPTIONS = [
        'fl',
        'rows',
        'type',
        'wt',
    ];

    /**
     * @var array
     */
    protected const DEFAULT_OPTIONS = [
        'bq' => 'type:gemeente^0.5 type:woonplaats^0.5 type:weg^1.0 type:postcode^1.5 type:adres^1.5',
        'fl' => 'centroide_ll,huis_nlt,huisnummer,straatnaam,postcode,woonplaatsnaam,gemeentenaam,gemeentecode,provincienaam,provinciecode',
    ];

    /**
     * @var array
     */
    protected const REQUIRED_OPTIONS_GEOCODE = [];

    /**
     * @var array
     */
    protected const REQUIRED_OPTIONS_REVERSE = [
        'type' => 'adres',
    ];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param \Http\Client\HttpClient $client  An HTTP adapter
     * @param array                   $options Extra query parameters (optional)
     */
    public function __construct(HttpClient $client, array $options = [])
    {
        parent::__construct($client);

        $this->setOptions($options);
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = array_diff_key($options, array_fill_keys(self::BLACKLISTED_OPTIONS, true));
    }

    /**
     * @param \Geocoder\Query\GeocodeQuery $query
     *
     * @throws \Geocoder\Exception\InvalidServerResponse
     * @throws \Geocoder\Exception\UnsupportedOperation
     *
     * @return \Geocoder\Collection
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        // This API doesn't handle IPs.
        if (filter_var($query->getText(), FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The NationaalGeoregister provider does not support IP addresses.');
        }

        return $this->executeQuery(sprintf(self::ENDPOINT_URL_FREE, http_build_query($this->getGeocodeOptions($query))));
    }

    /**
     * @param \Geocoder\Query\GeocodeQuery $query
     *
     * @return array
     */
    protected function getGeocodeOptions(GeocodeQuery $query): array
    {
        return array_merge(
            static::DEFAULT_OPTIONS,
            $this->options,
            array_diff_key($query->getAllData(), array_fill_keys(self::BLACKLISTED_OPTIONS, true)),
            static::REQUIRED_OPTIONS_GEOCODE,
            [
                'rows' => $query->getLimit(),
                'q' => $query->getText(),
            ]
        );
    }

    /**
     * @param \Geocoder\Query\ReverseQuery $query
     *
     * @throws \Geocoder\Exception\InvalidServerResponse
     *
     * @return \Geocoder\Collection
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        return $this->executeQuery(sprintf(self::ENDPOINT_URL_REVERSE, http_build_query($this->getReverseOptions($query))));
    }

    /**
     * @param \Geocoder\Query\ReverseQuery $query
     *
     * @return array
     */
    protected function getReverseOptions(ReverseQuery $query): array
    {
        return array_merge(
            static::DEFAULT_OPTIONS,
            $this->options,
            array_diff_key($query->getAllData(), array_fill_keys(self::BLACKLISTED_OPTIONS, true)),
            static::REQUIRED_OPTIONS_REVERSE,
            [
                'rows' => $query->getLimit(),
                'lat' => $query->getCoordinates()->getLatitude(),
                'lon' => $query->getCoordinates()->getLongitude(),
            ]
        );
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
     *
     * @return \Geocoder\Model\AddressCollection
     */
    protected function executeQuery(string $query): AddressCollection
    {
        $results = $this->getResultsForQuery($query);

        $addresses = [];
        foreach ($results->response->docs as $doc) {
            $position = explode(' ', trim(str_replace(['POINT(', ')'], '', $doc->centroide_ll)));

            $builder = new AddressBuilder($this->getName());

            $builder->setCoordinates((float) $position[1], (float) $position[0]);
            $builder->setStreetNumber($doc->huis_nlt ?? $doc->huisnummer ?? null);
            $builder->setStreetName($doc->straatnaam ?? null);
            $builder->setPostalCode($doc->postcode ?? null);
            $builder->setLocality($doc->woonplaatsnaam ?? null);
            if (isset($doc->gemeentenaam)) {
                $builder->addAdminLevel(2, $doc->gemeentenaam, $doc->gemeentecode);
            }
            if (isset($doc->provincienaam)) {
                $builder->addAdminLevel(1, $doc->provincienaam, $doc->provinciecode);
            }
            $builder->setCountry('Netherlands');
            $builder->setCountryCode('NL');
            $builder->setTimezone('Europe/Amsterdam');

            $addresses[] = $builder->build();
        }

        return new AddressCollection($addresses);
    }

    /**
     * @param string $query
     *
     * @throws \Geocoder\Exception\InvalidServerResponse
     *
     * @return \stdClass
     */
    protected function getResultsForQuery(string $query): \stdClass
    {
        $content = $this->getUrlContents($query);

        $result = json_decode($content);

        if (json_last_error() === JSON_ERROR_UTF8) {
            $result = json_decode(utf8_encode($content));
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidServerResponse(sprintf('Could not execute query "%s": %s', $query, json_last_error_msg()));
        }

        return $result;
    }
}
