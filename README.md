# Phone Number field for Craft CMS

This plugin adds a new fieldtype to Craft for entering phone numbers along with a Twig extension for extracting numbers from a string.

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for "Phone Number". Then click on the "Install" button in its modal window.

### With Composer

Open your terminal and run the following commands:

```bash
# Go to the project directory
cd /path/to/project

# Tell Composer to load the plugin
composer require rynpsc/craft-phone-number

# Tell Craft to install the plugin
./craft install/plugin phone-number
```

## Field Type

The phone number field provides an easy way for authors to enter phone numbers.

![Screenshot](/resources/screenshots/field.png)

### Templating

- `{{ entry.phone.region }}` - The raw region code as entered in the field
- `{{ entry.phone.number }}` - The raw number as entered in the field
- `{{ entry.phone.getCountryCode() }}` - The alphabetical country code
- `{{ entry.phone.getRegionCode() }}` - The numerical region code
- `{{ entry.phone.getLink() }} ` - Returns a phone number link
- `{{ entry.phone.format('e164') }}` - Formats a phone number
- `{{ entry.phone.getType() }}` - Returns the number type ([number types](#number-types))
- `{{ entry.phone.getDescription()` }} - Returns the country or geographical area

#### Number Formatting

Numbers can be formatted in the following formats:

| Format        | Example              |
| :------------ | :--------------------|
| e164          | +441174960123        |
| rfc3966       | tel:+44-117-496-0123 |
| national      | 0117 496 0123        |
| international | +44 117 496 0123     |

#### Number Types

Number types are returned as integers, use the table below for reference.

| Value   | Type                 |
| :------ | :------------------- |
| 0       | Fixed line           |
| 1       | Mobile               |
| 2       | Fixed line or mobile |
| 3       | Toll free            |
| 4       | Premium rate         |
| 5       | Shared cost          |
| 6       | VOIP                 |
| 7       | Personal number      |
| 8       | Pager                |
| 9       | UAN                  |
| 10      | Unknown              |
| 27      | Emergency            |
| 28      | Voicemail            |
| 29      | Short code           |
| 30      | Standard rate        |

## Twig Filter

This plugin also adds a `tel` Twig filter for extracting phone numbers from a string and auto linking them using the rfc3966 format.

```twig
{{ entry.text|tel }}
```

By default only numbers entered in international format will be formatted. To format local number i.e. those without a region code such as +44, you can pass in a default country code to use when parsing.

```twig
{{ entry.text|tel('GB') }}
```
