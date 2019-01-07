# Changelog

All notable changes to `swisnl/geocoder-php-nationaal-georegister-provider` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [Unreleased]

- Nothing

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
