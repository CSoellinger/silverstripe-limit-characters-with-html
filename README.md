# Limit Characters With HTML Extension

This module adds an extension to DBHTMLVarchar and DBHTMLText which enables limit html text without loosing the html tags. For example if you want a teaser text written with a WYSIWYG.

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [License](#license)
4. [Configuration](#configuration)
   1. [`private static bool html_min`](#private-static-bool-html_min)
   2. [`private static array html_min_options`](#private-static-array-html_min_options)
5. [Documentation](#documentation)
   1. [public function **LimitCharactersWithHtml(** $limit = 20, $add = false, $exact = true **)**: string](#public-function-limitcharacterswithhtml-limit--20-add--false-exact--true--string)
   2. [public function **LimitCharactersWithHtmlToClosestWord(** int $limit = 20, $add = false **)**: string](#public-function-limitcharacterswithhtmltoclosestword-int-limit--20-add--false--string)
   3. [public function **LongerThan(** int $limit, $excludeHtml = true **)**: bool](#public-function-longerthan-int-limit-excludehtml--true--bool)
6. [Usage examples](#usage-examples)
7. [Maintainers](#maintainers)
8. [Bugtracker](#bugtracker)
9. [Development and contribution](#development-and-contribution)

## Requirements

* SilverStripe ^4

## Installation

Simply install the extension with composer

```bash
composer require csoellinger/silverstripe-limit-characters-with-html
```

The extension will be auto added to [SilverStripe\ORM\FieldType\DBHTMLVarchar](https://github.com/silverstripe/silverstripe-framework/blob/4/src/ORM/FieldType/DBHTMLVarchar.php) and [SilverStripe\ORM\FieldType\DBHTMLText](https://github.com/silverstripe/silverstripe-framework/blob/4/src/ORM/FieldType/DBHTMLText.php) by the included config file. Just run:

```bash
sake dev/build "flush=all"
```

## License

See [License](License.md)

## Configuration

### `private static bool html_min`

Enable/Disable html minify.

### `private static array html_min_options`

Set HTML minify options as associative array:

* `collapse_whitespace` => true|false (Default: true)
* `disable_comments` => true|false (Default: false)

## Documentation

After installing the module you have three new methods for your db html fields.

### public function **LimitCharactersWithHtml(** $limit = 20, $add = false, $exact = true **)**: string

```php
/**
 * Limit this field's content by a number of characters. It can consider
 * html and limit exact or at word ending.
 *
 * @param int           $limit        Number of characters to limit by.
 * @param string|false  $add          Ellipsis to add to the end of truncated string.
 * @param bool          $exact        Truncate exactly or at word ending.
 *
 * @return string HTML text with limited characters.
 */
```

### public function **LimitCharactersWithHtmlToClosestWord(** int $limit = 20, $add = false **)**: string

```php
/**
 * Limit this field's content by a number of characters and truncate the field
 * to the closest complete word.
 *
 * @param int          $limit        Number of characters to limit by.
 * @param string|false $add          Ellipsis to add to the end of truncated string.
 *
 * @return string HTML text value with limited characters truncated to the closest word.
 */
```

### public function **LongerThan(** int $limit, $excludeHtml = true **)**: bool

```php
/**
 * Check if a string is longer than a number of characters. It excludes html
 * by default.
 *
 * @param mixed $excludeHtml Default is true
 *
 * @return bool
 */
```

## Usage examples

```php
<?php

$htmlText = '<b>Hello World</b>';

// example 1
DBHTMLText::create('Test')
  ->setValue($htmlText)
  ->LimitCharactersWithHtml(9)
  ->Value() // Output: <b>Hello Wo…</b>

// example 2
DBHTMLText::create('Test')
  ->setValue($htmlText)
  ->LimitCharactersWithHtmlToClosestWord(9)
  ->Value() // Output: <b>Hello…</b>
```

```html
<p>{$TestField.LimitCharactersWithHtml(9)}</p>
<p>{$TestField.LimitCharactersWithHtmlToClosestWord(9)}</p>
```

## Maintainers

* Christopher Söllinger <christopher.soellinger@gmail.com>

## Bugtracker

Bugs are tracked in the issues section of this repository. Before submitting an issue please read over
existing issues to ensure yours is unique.

If the issue does look like a new bug:

* Create a new issue
* Describe the steps required to reproduce your issue, and the expected outcome. Unit tests, screenshots and screencasts can help here.
* Describe your environment as detailed as possible: SilverStripe version, Browser, PHP version, Operating System, any installed SilverStripe modules.

Please report security issues to the module maintainers directly. Please don't file security issues in the bug tracker.

## Development and contribution

If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.
