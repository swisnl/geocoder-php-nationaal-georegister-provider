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
    const GEOCODE_ENDPOINT_URL_SSL = 'https://geodata.nationaalgeoregister.nl/locatieserver/v3/free?rows=%d&%s&q=%s';

    /**
     * @var string[]
     */
    const BLACKLISTED_OPTIONS = [
        'fl',
        'q',
        'rows',
        'wt',
    ];

    /**
     * @var array
     */
    protected $defaultOptions = [
        'bq' => 'type:gemeente^0.5 type:woonplaats^0.5 type:weg^1.0 type:postcode^1.5 type:adres^1.5',
        'fl' => 'centroide_ll,huis_nlt,huisnummer,straatnaam,postcode,woonplaatsnaam,gemeentenaam,gemeentecode,provincienaam,provinciecode',
    ];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param \Http\Client\HttpClient $client An HTTP adapter
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
        $this->options = array_merge(
            $this->defaultOptions,
            array_diff_key($options, array_fill_keys(self::BLACKLISTED_OPTIONS, true))
        );
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
        $address = $query->getText();
        // This API doesn't handle IPs.
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The NationaalGeoregister provider does not support IP addresses.');
        }

        return $this->executeQuery(
            sprintf(
                self::GEOCODE_ENDPOINT_URL_SSL,
                $query->getLimit(),
                http_build_query($this->options),
                rawurlencode($address)
            )
        );
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

            $builder->setCoordinates((float)$position[1], (float)$position[0]);
            $builder->setStreetNumber($doc->huis_nlt ?? $doc->huisnummer ?? null);
            $builder->setStreetName($doc->straatnaam ?? null);
            $builder->setPostalCode($doc->postcode ?? null);
            $builder->setLocality($doc->woonplaatsnaam ?? null);
            if ($doc->gemeentenaam) {
                $builder->addAdminLevel(2, $doc->gemeentenaam, $doc->gemeentecode);
            }
            if ($doc->provincienaam) {
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

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidServerResponse(sprintf('Could not execute query "%s"', $query));
        }

        return $result;
    }
}
