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

#### Number Formatting

Numbers can be formated in the following formats:

| Format        | Example              |
| :------------ | :--------------------|
| e164          | +441174960123        |
| rfc3966       | tel:+44-117-496-0123 |
| national      | 0117 496 0123        |
| international | +44 117 496 0123     |

## Twig Filter

This plugin also adds a `tel` Twig filter for extracting phone numbers from a string and auto linking them using the rfc3966 format.

```twig
{{ entry.text|tel }}
```

By default only numbers entered in international format will be formatted. To format local number i.e. those without a region code such as +44, you can pass in a default country code to use when parsing.

```twig
{{ entry.text|tel('GB') }}
```
