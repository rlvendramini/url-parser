# url-parser
 ![PHP Composer](https://github.com/rlvendramini/url-parser/workflows/PHP%20Composer/badge.svg) [![Version](https://poser.pugx.org/rlvendramini/url-parser/version)](//packagist.org/packages/rlvendramini/url-parser) [![Total Downloads](https://poser.pugx.org/rlvendramini/url-parser/downloads)](//packagist.org/packages/rlvendramini/url-parser) [![License](https://poser.pugx.org/rlvendramini/url-parser/license)](//packagist.org/packages/rlvendramini/url-parser) [![Open Source Love](https://badges.frapsoft.com/os/v1/open-source.svg?v=103)](https://github.com/ellerbrock/open-source-badges/)

Class that implements more control over an URL in PHP, like a Javascript URL class

## Instalation
```bash
composer require rlvendramini/url-parser
```

## Setup
If you already have a vendor library in your project, you might have the following line. If not, just add:
```php
require __DIR__ . '/vendor/autoload.php';
```

## Usage

Instantiate from a string
```php
$url = URLParser::fromString($string);
```

then you can get query string params
```php
$url = URLParser::fromString('https://foo.bar/home?param=value');

$url->getParam('param'); // value
```

and set new params or overwrite existing ones
```php
$url = URLParser::fromString('https://foo.bar/home?param=value');

$url->setParam('param', 'super value'); // super+value
$url->setParam(' #amazing param~', 'amazing value'); // amazing+value

$url->getParam('param'); // super+value
$url->getParam('amazing_param'); // amazing+value
```

and finally, you can get modified url as a string
```php
$url = URLParser::fromString('https://foo.bar/home?param=value');

$url->setParam('param', 'super value'); // super+value
$url->setParam(' #amazing param~', 'amazing value'); // amazing+value

$url->getParam('param'); // super+value
$url->getParam('amazing_param'); // amazing+value

$url->toString() // https://foo.bar/home?param=super+value&amazing_param=amazing+value
```
