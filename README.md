# RDW Open Data

This is a simple library for getting vehicle data by license plate number from https://opendata.rdw.nl

## Installation

`composer config repositories.debrand vcs https://github.com/deBrandSoftware/rdw-opendata`

`composer require debrand/rdw-opendata`

## Example usage

Call the static `get` method on the `RDW` class. You can pass two parameters: `string license_plate` and `string[] endpoints`.
See the Endpoints class for a list of available endpoints.

```php
use deBrand\RDWOpenData\RDW;

$data = RDW::get('LXHH54');

echo $data['merk'];
```

```php
use deBrand\RDWOpenData\RDW;
use deBrand\RDWOpenData\Api\Endpoints;

$data = RDW::get('lx-hh-54', array(Endpoints::ALGEMEEN));

echo $data['brandstof_omschrijving'];
```
