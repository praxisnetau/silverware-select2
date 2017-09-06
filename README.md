# SilverWare Select2 Module

[![Latest Stable Version](https://poser.pugx.org/silverware/select2/v/stable)](https://packagist.org/packages/silverware/select2)
[![Latest Unstable Version](https://poser.pugx.org/silverware/select2/v/unstable)](https://packagist.org/packages/silverware/select2)
[![License](https://poser.pugx.org/silverware/select2/license)](https://packagist.org/packages/silverware/select2)

Provides Select2-powered dropdown and Ajax fields for [SilverStripe v4][silverstripe-framework]. Intended to be used
with [SilverWare][silverware], however this module can also be installed into a regular SilverStripe v4 project.

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Issues](#issues)
- [Contribution](#contribution)
- [Attribution](#attribution)
- [Maintainers](#maintainers)
- [License](#license)

## Requirements

- [SilverStripe Framework v4][silverstripe-framework]

## Installation

Installation is via [Composer][composer]:

```
$ composer require silverware/select2
```

## Configuration

As with all SilverStripe modules, configuration is via YAML. Extensions to `LeftAndMain` are applied via `config.yml`.

## Usage

### Select2Field

The `Select2Field` class is used exactly the same way as a regular old `DropdownField`:

```php
use SilverWare\Select2\Forms\Select2Field;

$field = Select2Field::create(
  'MySelect2Field',
  'Select2 Field',
  [
    1 => 'An',
    2 => 'Array',
    3 => 'of',
    4 => 'Options'
  ]
);
```

#### Select2 Configuration

You can define any of the Select2 configuration settings by using the `setConfig()` method:

```php
$field->setConfig('maximum-input-length', 20);
```

Alternatively, you can set the defaults for all `Select2Field` instances in your app by using YAML config:

```yaml
SilverWare\Select2\Forms\Select2Field:
  default_config:
    maximum-input-length: 20
```

**NOTE:** configuration setting names are defined using HTML attribute style, and not camel case, for example:

```php
$field->setConfig('maximum-input-length', 20);  // this will work
$field->setConfig('maximumInputLength', 20);    // this will NOT work
```

### Select2AjaxField

The `Select2AjaxField` is used to search a `DataList` based on a search term entered by the user. The results are
retrieved from the server via Ajax and rendered as dropdown options. This is very handy for cases where the number of
available options in a regular dropdown would be prodigious.

The field is created like any other field:

```php
use SilverWare\Select2\Forms\Select2AjaxField;

$field = Select2AjaxField::create(
  'MySelect2AjaxField',
  'Select2 Ajax Field'
);
```

But we do not pass an array of options to choose from to the constructor. Instead, we configure the field to search
a `DataList` on the server-side. Here are the default settings:

```php
$field->setDataClass(SiteTree::class);  // by default, the field searches for SiteTree records

$field->setIDField('ID');       // the name of the field which identifies the record
$field->setTextField('Title');  // the name of the field to use for the option text

$field->setSearchFields([
  'Title'  // an array of fields to search based on the entered term
]);

$field->setSortBy([
  'Title' => 'ASC'  // an array which defines the sort order of the results
]);

$field->setLimit(256);  // the maximum number of records to return
```

As mentioned, these are the default settings, and the field will work out-of-the-box for `SiteTree` searches.

#### Exclusions

You can also optionally define a series of exclusion filters, which use the same format for the `exclude` method of
`DataList`:

```php
$field->setExclude([
  'Title:ExactMatch' => 'Hide This Title'
]);
```

Any records matching the defined exclusion filters will be excluded from the results.

#### Formatting

By default, the field will render a regular series of dropdown options based on the `$ID` and `$Title` of the matching
records, however you can apply more advanced formatting for both results and the current selection. For example:

```php
$field->setFormatResult('<span>Found: <em>$Title</em></span>');
$field->setFormatSelection('<span>Selected: <strong>$Title</strong></span>');
```

HTML will be rendered as a jQuery object, so be sure to wrap it in an enclosing element such as a `<span>`.

#### Ajax Configuration

Any of the Select2 Ajax settings can be defined using the `setAjaxConfig()` method:

```php
$field->setAjaxConfig('cache', false);
$field->setAjaxConfig('delay', 500);
```

Alternatively, you can set the defaults for all `Select2AjaxField` instances in your app by using YAML config:

```yaml
SilverWare\Select2\Forms\Select2AjaxField:
  default_ajax_config:
    cache: false
    delay: 500
```

**NOTE:** configuration setting names are defined using HTML attribute style, and not camel case, for example:

```php
$field->setAjaxConfig('data-type', 'json');  // this will work
$field->setAjaxConfig('dataType', 'json');   // this will NOT work
```

## Issues

Please use the [GitHub issue tracker][issues] for bug reports and feature requests.

## Contribution

Your contributions are gladly welcomed to help make this project better.
Please see [contributing](CONTRIBUTING.md) for more information.

## Attribution

- Makes use of [Select2][select2] by [Kevin Brown](https://github.com/kevin-brown),
  [Igor Vaynberg](https://github.com/ivaynberg) and [others](https://github.com/select2/select2/graphs/contributors).

## Maintainers

[![Colin Tucker](https://avatars3.githubusercontent.com/u/1853705?s=144)](https://github.com/colintucker) | [![Praxis Interactive](https://avatars2.githubusercontent.com/u/1782612?s=144)](http://www.praxis.net.au)
---|---
[Colin Tucker](https://github.com/colintucker) | [Praxis Interactive](http://www.praxis.net.au)

## License

[BSD-3-Clause](LICENSE.md) &copy; Praxis Interactive

[silverware]: https://github.com/praxisnetau/silverware
[silverware-select2]: https://github.com/praxisnetau/silverware-select2
[composer]: https://getcomposer.org
[silverstripe-framework]: https://github.com/silverstripe/silverstripe-framework
[issues]: https://github.com/praxisnetau/silverware-select2/issues
[select2]: https://github.com/select2/select2
