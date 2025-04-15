# Changelog

All notable changes to `swisnl/geocoder-php-nationaal-georegister-provider` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [Unreleased]

- Nothing

## [1.7.0] - 2025-04-15

### Added

- Geocoder PHP 5 support

### Removed

- PHP <8.0 support

## [1.6.0] - 2023-04-07

### Changed

- Migrated to the new service URL. [#8](https://github.com/swisnl/geocoder-php-nationaal-georegister-provider/pull/8)

## [1.5.0] - 2022-08-16

### Changed

- Expect a PSR-18 client instead of a PHP-HTTP client. N.B. PHP-HTTP clients implement this interface, so it should not be a breaking change.

### Removed

- PHP <7.4 support

## [1.4.0] - 2021-12-13

### Added

- Allow setting options per query using query data.

### Fixed

- Added (protected) visibility keyword to constants. Although this can be considered a breaking change, we consider it a bugfix as they were never meant to be public.

### Removed

- PHP <7.3 support

## [1.3.0] - 2021-01-18

### Added

- PHP 8 support [#6](https://github.com/swisnl/geocoder-php-nationaal-georegister-provider/pull/6)

### Removed

- PHP <7.2 support [#6](https://github.com/swisnl/geocoder-php-nationaal-georegister-provider/pull/6)

## [1.2.2] - 2019-02-04

### Fixed

- Fixed use case where reverse geocoding failed for results with special characters (e.g. umlaut).

## [1.2.1] - 2019-01-07

### Fixed

- Fixed use case where "Undefined property: stdClass::$gemeentenaam" and "Undefined property: stdClass::$provincienaam" occured [#4](https://github.com/swisnl/geocoder-php-nationaal-georegister-provider/pull/4)

## [1.2.0] - 2018-12-11

### Added

- Add ext-json to composer.json

### Changed

- Use official reverse geocoder endpoint instead of workaround

## [1.1.0] - 2018-02-27

### Changed

- Implement [PDOK Locatieserver v3](https://github.com/PDOK/locatieserver/wiki/API-Locatieserver) instead of the old PDOK Geocoder (v1)

### Added

- Reverse geocoding feature

## [1.0.0] - 2018-02-07

### Added 

- Initial release
