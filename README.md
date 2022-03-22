# Phone Number field for Craft CMS

This plugin adds a new fieldtype to Craft for entering phone numbers along with a Twig extension for extracting numbers from a string.

It's built on the excellent [libphonenumber-for-php](https://github.com/giggsey/libphonenumber-for-php) port of Google's [libphonenumber](https://github.com/google/libphonenumber) library. 

## Requirements

This plugin requires Craft CMS 3.6.12 or later.

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

The Phone Number field provides an easy way of parsing, formatting, storing and validating international phone numbers.

![Screenshot](/resources/screenshots/field.png)

### Templating

- `{{ entry.phone.region }}` - The raw region code as entered in the field
- `{{ entry.phone.number }}` - The raw number as entered in the field
- `{{ entry.phone.getCountryCode() }}` - The numerical country code
- `{{ entry.phone.getRegionCode() }}` - The alphabetical region code
- `{{ entry.phone.getLink() }} ` - Returns a phone number link
- `{{ entry.phone.format('e164') }}` - Formats a phone number
- `{{ entry.phone.getType() }}` - Returns the number type ([number types](#number-types))
- `{{ entry.phone.getDescription()` }} - Returns the country or geographical area

#### Number Formatting

Numbers can be formatted in the following formats:

| Format        | Example Output       |
| :------------ | :--------------------|
| e164          | +441174960123        |
| rfc3966       | tel:+44-117-496-0123 |
| national      | 0117 496 0123        |
| international | +44 117 496 0123     |

The rfc3966 format is also available via the `tel` alias.

#### Number Types

Number types are returned as integers.

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

The `tel` filter extracts phone numbers from a string and turns them into links, using the number as the link text.

```twig
{{ entry.text|tel }}
```

By default, only numbers entered in international format will be formatted. To format local number i.e. those without a region code such as +44, you can pass in a default country code to use when parsing.

```twig
{{ entry.text|tel('GB') }}
````

## Links

Both the `getLink()` method and `tel` filter support setting the generated links content and HTML attributes.

Attributes are set as per [`yii\helpers\BaseHtml::renderTagAttributes()`](https://www.yiiframework.com/doc/api/2.0/yii-helpers-basehtml#renderTagAttributes()-detail).

```twig
{{ entry.phone.getLink({
    class: 'my-class'
}) }}

{{ entry.textField|tel(null, {
    class: 'my-class'
}) }}
```

If `text` is included in the attributes argument, its value will be HTML-encoded and set as the text contents of the link.

```twig
{{ entry.phone.getLink({
    text: 'Content'
}) }}

{{ entry.textField|tel(null, {
    text: 'Content'
}) }}
```

If `html` is included in the attributes argument, its value will be set as the inner HTML of the link (without getting HTML-encoded).

```twig
{{ entry.phone.getLink({
    html: '<div>Content</div>'
}) }}

{{ entry.textField|tel(null, {
    html: '<div>Content</div>'
}) }}
```
