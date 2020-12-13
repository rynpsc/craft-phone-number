# Changelog

## 1.4.1 - 2020-12-13

### Fixed

- Fix GraphQL error when `devMode` is set to `false`.

## 1.4.0 - 2020-12-05

### Added

- Added GraphQL content type ([#10](https://github.com/rynpsc/craft-phone-number/pull/10)).

## 1.3.0 - 2020-01-20

### Changed

- Updated styles for Craft 3.4.

## 1.2.5 - 2019-10-15

### Fixed

- Fix issue where `tel` filter returned incorrect `href` value in when running Craft 3.3..

## 1.2.4 - 2019-09-20

### Fixed

- Fix issue where `getLink()` returned incorrect `href` value in when running Craft 3.3..

## 1.2.3 - 2019-05-05

### Fixed

- Fix error when converting from a plain text field ([#1](https://github.com/rynpsc/craft-phone-number/issues/1)).

## 1.2.2 - 2019-02-10

### Fixed

- Fix incorrect string replacement with `tel` filter when dealing with multi-byte strings.

## 1.2.1 - 2019-01-04

### Fixed

- Fix issue with tel filter when string is null.

## 1.2.0 - 2018-10-19

### Added

- Added `getType()` to `PhoneNumberModel`.
- Added `getDescription()` to `PhoneNumberModel`.

## 1.1.1 - 2018-09-24

### Fixed

- Fix issue where classes were being incorrectly applied in IE11.

## 1.1.0 - 2018-09-10

### Added

- Added ability to pass in a country code to the `tel` filter to parse national numbers.

## 1.0.0 - 2018-09-05

- Initial release
