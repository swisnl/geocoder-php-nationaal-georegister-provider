# Nationaal Georegister provider for Geocoder PHP

[![PHP from Packagist](https://img.shields.io/packagist/php-v/swisnl/geocoder-php-nationaal-georegister-provider.svg?style=flat-square)](https://packagist.org/packages/swisnl/geocoder-php-nationaal-georegister-provider)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/swisnl/geocoder-php-nationaal-georegister-provider.svg?style=flat-square)](https://packagist.org/packages/swisnl/geocoder-php-nationaal-georegister-provider)
[![Software License](https://img.shields.io/packagist/l/swisnl/geocoder-php-nationaal-georegister-provider.svg?style=flat-square)](LICENSE.md) 
[![Buy us a tree](https://img.shields.io/badge/Treeware-%F0%9F%8C%B3-lightgreen.svg?style=flat-square)](https://plant.treeware.earth/swisnl/geocoder-php-nationaal-georegister-provider)
[![Build Status](https://img.shields.io/github/checks-status/swisnl/geocoder-php-nationaal-georegister-provider/master?label=tests)](https://github.com/swisnl/geocoder-php-nationaal-georegister-provider/actions/workflows/tests.yml)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/swisnl/geocoder-php-nationaal-georegister-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/swisnl/geocoder-php-nationaal-georegister-provider/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/swisnl/geocoder-php-nationaal-georegister-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/swisnl/geocoder-php-nationaal-georegister-provider)
[![Total Downloads](https://img.shields.io/packagist/dt/swisnl/geocoder-php-nationaal-georegister-provider.svg?style=flat-square)](https://packagist.org/packages/swisnl/geocoder-php-nationaal-georegister-provider)
[![Made by SWIS](https://img.shields.io/badge/%F0%9F%9A%80-made%20by%20SWIS-%230737A9.svg?style=flat-square)](https://www.swis.nl)

This is the [Nationaal Georegister](https://geodata.nationaalgeoregister.nl/) provider for the [PHP Geocoder](https://github.com/geocoder-php/Geocoder), which uses the [PDOK Locatieserver v3 (Dutch)](https://www.pdok.nl/diensten#PDOK%20Locatieserver).
It can geocode addresses (not IP addresses) and reverse geocode coordinates.

Please note that this provider can only (reverse) geocode addresses in The Netherlands!

## Install

Via Composer

``` bash
$ composer require swisnl/geocoder-php-nationaal-georegister-provider
```

### HTTP Client

PHP Geocoder is decoupled from any HTTP messaging client with the help of [PSR-18 HTTP Client](https://www.php-fig.org/psr/psr-18/).
This requires another package providing [psr/http-client-implementation](https://packagist.org/providers/psr/http-client-implementation).
To use Guzzle 7, for example, simply require `guzzlehttp/guzzle`:

``` bash
$ composer require guzzlehttp/guzzle
```

## Usage

``` php
// Create geocoder
$client = new \GuzzleHttp\Client();
$geocoder = new \Swis\Geocoder\NationaalGeoregister\NationaalGeoregister($client);

// Geocode!
$query = \Geocoder\Query\GeocodeQuery::create(...);
$result = $geocoder->geocodeQuery($query);
```

Please see [PHP Geocoder documentation](http://geocoder-php.org/Geocoder/) for more information about using PHP Geocoder with this provider.

### Options

This provider accepts extra options in the constructor, or as query data. These options are directly passed to the Locatieserver, which accepts the following options:

| Option  | Description            | Default | Customizable in this provider |
| ------- | ---------------------- | ------- | ----------------------------- |
| bq      | Boost query            | "type:gemeente^0.5 type:woonplaats^0.5 type:weg^1.0 type:postcode^1.5 type:adres^1.5" | yes |
| df      | Default field          | - | yes |
| fl      | Field list             | All fields used by this provider | no |
| fq      | Filter query           | - | yes |
| lat&lon | Latitude and longitude | Coordinates from `\Geocoder\Query\ReverseQuery` when reverse geocoding | only for geocoding |
| q       | Search term            | Text from `\Geocoder\Query\GeocodeQuery` when geocoding | no |
| rows    | Amount of rows         | Limit from query | no |
| start   | Page (starting at 0)   | - | yes |
| sort    | Sorting                | See [Locatieserver documentation (Dutch)](https://github.com/PDOK/locatieserver/wiki/API-Locatieserver#52url-parameters) | yes |
| wt      | Format                 | JSON | no |

These options correspond with the options mentioned in the [Locatieserver documentation (Dutch)](https://github.com/PDOK/locatieserver/wiki/API-Locatieserver#52url-parameters), which describes them in more detail.

Example using extra options:

``` php
// On the geocoder instance:
$client = new \GuzzleHttp\Client();
$options = ['fq' => 'bron:BAG'];
$geocoder = new \Swis\Geocoder\NationaalGeoregister\NationaalGeoregister($client, $options);

// Or just once on the query:
$query = \Geocoder\Query\GeocodeQuery::create(...)->withData('fq', 'bron:BAG');
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

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email security@swis.nl instead of using the issue tracker.

## Credits

- [Jasper Zonneveld](https://github.com/JaZo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

This package is [Treeware](https://treeware.earth). If you use it in production, then we ask that you [**buy the world a tree**](https://plant.treeware.earth/swisnl/geocoder-php-nationaal-georegister-provider) to thank us for our work. By contributing to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.

## SWIS :heart: Open Source

[SWIS](https://www.swis.nl) is a web agency from Leiden, the Netherlands. We love working with open source software. 
