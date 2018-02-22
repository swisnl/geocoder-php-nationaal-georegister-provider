# Nationaal Georegister provider for Geocoder PHP

[![PHP from Packagist](https://img.shields.io/packagist/php-v/swisnl/geocoder-php-nationaal-georegister-provider.svg)](https://packagist.org/packages/swisnl/geocoder-php-nationaal-georegister-provider)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/swisnl/geocoder-php-nationaal-georegister-provider.svg)](https://packagist.org/packages/swisnl/geocoder-php-nationaal-georegister-provider)
[![Software License](https://img.shields.io/packagist/l/swisnl/geocoder-php-nationaal-georegister-provider.svg)](LICENSE) 
[![Run Status](https://api.shippable.com/projects/5a7add088abc8b06009fa8de/badge?branch=master)](https://app.shippable.com/github/swisnl/geocoder-php-nationaal-georegister-provider)
[![Coverage Badge](https://api.shippable.com/projects/5a7add088abc8b06009fa8de/coverageBadge?branch=master)](https://app.shippable.com/github/swisnl/geocoder-php-nationaal-georegister-provider)

This is the [Nationaal Georegister](https://geodata.nationaalgeoregister.nl/) provider for the [PHP Geocoder](https://github.com/geocoder-php/Geocoder), which uses the [PDOK Locatieserver v3 (Dutch)](https://www.pdok.nl/nl/producten/pdok-locatieserver).

Please note that this provider can only geocode addresses in The Netherlands!

## Install

```bash
composer require swisnl/geocoder-php-nationaal-georegister-provider
```

### HTTP Client

PHP Geocoder is decoupled from any HTTP messaging client with the help of [PHP-HTTP](http://php-http.org/).
This requires another package providing [php-http/client-implementation](https://packagist.org/providers/php-http/client-implementation).
To use Guzzle 6, for example, simply require `php-http/guzzle6-adapter`:

``` bash
composer require php-http/guzzle6-adapter
```

## Usage

``` php
// Create geocoder
$client = new \Http\Client\Curl\Client();
$geocoder = new \Swis\Geocoder\NationaalGeoregister\NationaalGeoregister($client);

// Geocode!
$query = \Geocoder\Query\GeocodeQuery::create(...);
$result = $geocoder->geocodeQuery($query);
```

Please see [PHP Geocoder documentation](http://geocoder-php.org/Geocoder/) for more information about using PHP Geocoder with this provider.

### Options

This provider accepts extra options as the second argument.
These options are directly passed to the Locatieserver, which accepts the following options:

| Option  | Description            | Default | Customizable in this provider |
| ------- | ---------------------- | ------- | ----------------------------- |
| bq      | Boost query            | "type:gemeente^0.5 type:woonplaats^0.5 type:weg^1.0 type:postcode^1.5 type:adres^1.5" | yes |
| df      | Default field          | - | yes |
| fl      | Field list             | All fields used by this provider | no |
| fq      | Filter query           | - | yes |
| lat&lon | Latitude and longitude | - | yes |
| q       | Search term            | Text from `\Geocoder\Query\GeocodeQuery` | no |
| rows    | Amount of rows         | Limit from `\Geocoder\Query\GeocodeQuery` | no |
| start   | Page (starting at 0)   | - | yes |
| sort    | Sorting                | See [Locatieserver documentation (Dutch)](https://github.com/PDOK/locatieserver/wiki/API-Locatieserver#52url-parameters) | yes |
| wt      | Format                 | JSON | no |

These options correspond with the options mentioned in the [Locatieserver documentation (Dutch)](https://github.com/PDOK/locatieserver/wiki/API-Locatieserver#52url-parameters), which describes them in more detail.

Example using extra options:

``` php
$client = new \Http\Client\Curl\Client();
$options = ['fq' => 'bron:BAG'];
$geocoder = new \Swis\Geocoder\NationaalGeoregister\NationaalGeoregister($client, $options);
```

### Response

The geocoder returns a `\Geocoder\Model\AddressCollection` which is a collection of `\Geocoder\Model\Address`.

Example response (first `\Geocoder\Model\Address` from collection) for query "3e Binnenvestgracht 23T1, Leiden" using this provider:

``` php
$address->getCoordinates() => \Geocoder\Model\Coordinates
$address->getLatitude() => 52.164203
$address->getLongitude() => 4.49202289
$address->getBounds() => null (unavailable)
$address->getStreetNumber() => '23T-1'
$address->getStreetName() => '3e Binnenvestgracht'
$address->getPostalCode() => '2312NR'
$address->getLocality() => 'Leiden'
$address->getSubLocality() => null (unavailable)
$address->getAdminLevels()->get(2)->getName() => 'Leiden'
$address->getAdminLevels()->get(2)->getCode() => '0546'
$address->getAdminLevels()->get(1)->getName() => 'Zuid-Holland'
$address->getAdminLevels()->get(1)->getCode() => 'PV28'
$address->getCountry() => 'Netherlands' (static)
$address->getCountryCode() => 'NL' (static)
$address->getTimezone() => 'Europe/Amsterdam' (static)
```

## License

`geocoder-php-nationaal-georegister-provider` is licensed under the MIT License - see the LICENSE file for details
