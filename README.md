# url-parser
Class that implements more control over an URL in PHP, like a Javascript URL class

## Instalation
```bash
composer require rlvendramini/url-parser
```

## Usage

Instantiate from a string
```php
$url = URLParser::fromString($string);
```

then you can get query string params
```php
$url = URLParser::fromString('https://foo.bar/home?param=value');

$fooParam = $url->getParam('param'); // value
```

and set new params or overwrite existing ones
```php
$url = URLParser::fromString('https://foo.bar/home?param=value');

$fooParam = $url->setParam('param', 'super value'); // super+value
$fooParam = $url->setParam(' #amazing param~', 'amazing value'); // amazing+value

$fooParam = $url->getParam('param'); // super+value
$fooParam = $url->getParam('amazing_param'); // amazing+value
```
